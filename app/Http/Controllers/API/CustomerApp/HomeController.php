<?php

namespace App\Http\Controllers\API\CustomerApp;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Customers;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductReview;
use App\Models\StoreSetting;
use App\Models\Users;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    /**
     * Customer home: banners (sliders) and featured products.
     */
    public function dashboard(Request $request)
    {
        /** @var Users|null $user */
        $user = $request->user();

        if (!$user || (int) $user->role_type !== Users::CUSTOMER_APP_ROLE_TYPE) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized customer access',
            ], 403);
        }

        $hasBannerType = Schema::hasColumn('banners', 'banner_type');
        $visibility = $this->customerAppHomeVisibility();

        $bannersQuery = Banner::query()->visibleInDateRange();
        if (Schema::hasColumn('banners', 'status')) {
            $bannersQuery->where('status', 1);
        }
        $bannersQuery->orderByDesc('id');
        $bannerRows = $bannersQuery->get();
        $sliders = [];
        foreach ($bannerRows as $banner) {
            $section = $this->resolveBannerHomeSection($banner, $hasBannerType);
            if (!$this->customerHomeBannerSectionVisible($visibility, $section)) {
                continue;
            }

            $sliders[] = $this->mapBannerForHome($banner, $hasBannerType);
        }
        $featuredProducts = collect();
        if ($visibility['featured_products']) {
            $productsQuery = Product::query()->visibleToCustomerUser($user);
            if (Schema::hasColumn('products', 'featured')) {
                $productsQuery->where('featured', 1);
            }
            if (Schema::hasColumn('products', 'is_active_status')) {
                $productsQuery->where('is_active_status', 1);
            }
            if (Schema::hasColumn('products', 'status')) {
                $productsQuery->where('status', 1);
            }
            $featuredRows = $productsQuery
                ->orderByDesc('product_id')
                ->limit(12)
                ->get();

            $reviewCounts = [];
            if (Schema::hasTable('product_reviews') && $featuredRows->isNotEmpty()) {
                $reviewCounts = ProductReview::query()
                    ->where('status', 1)
                    ->whereIn('product_id', $featuredRows->pluck('product_id')->all())
                    ->selectRaw('product_id, COUNT(*) as total_reviews')
                    ->groupBy('product_id')
                    ->pluck('total_reviews', 'product_id')
                    ->map(fn ($count) => (int) $count)
                    ->all();
            }

            $featuredProducts = $featuredRows
                ->map(fn (Product $product) => $this->mapFeaturedProduct($product, $reviewCounts))
                ->values();
        }

        $categories = ProductCategory::where('status', 1)
            ->whereHas('subCategories', function ($q) use ($user) {
                $q->where('status', 1)
                    ->whereHas('products', function ($p) use ($user) {
                        $p->visibleToCustomerUser($user);
                        $p->where('status', 1);
                    });
            })
            ->orderByDesc('category_id')
            ->limit(10)
            ->get()
            ->map(fn (ProductCategory $category) => [
                'category_id'   => $category->category_id,
                'category_name' => $category->category_name,
                'category_image' => $category->category_image,
                'category_image_url' => !empty($category->category_image)
                    ? url('public/uploads/categories/' . $category->category_image)
                    : null,
            ])
            ->values();

        $popularPicks = $featuredProducts;
        $nearbyRestaurants = $this->nearbyRestaurantsForUser($user, $request);

        return response()->json([
            'status' => true,
            'message' => 'Customer home screen retrieved successfully',
            'data' => [
                'banners' => [
                    'sliders' => $sliders,
                ],
                'categories' => $categories,
                'featured_products' => $featuredProducts,
                'popular_picks' => $popularPicks,
                'nearby_restaurants' => $nearbyRestaurants,
            ],
        ], 200);
    }

    private function nearbyRestaurantsForUser(Users $user, Request $request): array
    {
        if (!Schema::hasTable('vendors')) {
            return [];
        }

        $customer = Customers::where('user_id', $user->user_id)->first();
        $latitude = $request->filled('latitude') ? (float) $request->input('latitude') : $customer?->latitude;
        $longitude = $request->filled('longitude') ? (float) $request->input('longitude') : $customer?->longitude;

        if ($latitude === null || $longitude === null) {
            return Vendor::query()
                ->when(Schema::hasColumn('vendors', 'status'), fn ($q) => $q->where('status', '1'))
                ->orderByDesc('vendor_id')
                ->limit(10)
                ->get()
                ->map(fn ($vendor) => $this->mapNearbyRestaurant($vendor, null))
                ->values()
                ->all();
        }

        $radiusKm = (float) $request->input('radius_km', config('customer-app.nearby_radius_km', 15));
        $distanceExpression = '(6371 * acos(cos(radians(?)) * cos(radians(v.latitude)) * cos(radians(v.longitude) - radians(?)) + sin(radians(?)) * sin(radians(v.latitude))))';

        return DB::table('vendors as v')
            ->select('v.*')
            ->selectRaw($distanceExpression . ' as distance_km', [$latitude, $longitude, $latitude])
            ->whereNotNull('v.latitude')
            ->whereNotNull('v.longitude')
            ->when(Schema::hasColumn('vendors', 'status'), fn ($q) => $q->where('v.status', '1'))
            ->havingRaw('distance_km <= ?', [$radiusKm > 0 ? $radiusKm : 15])
            ->orderBy('distance_km')
            ->limit(10)
            ->get()
            ->map(fn ($row) => $this->mapNearbyRestaurant($row, (float) $row->distance_km))
            ->values()
            ->all();
    }

    private function mapNearbyRestaurant($vendor, ?float $distanceKm): array
    {
        $image = is_object($vendor)
            ? ($vendor->business_banner ?? $vendor->shop_image ?? $vendor->business_logo)
            : null;

        return [
            'vendor_id' => $vendor->vendor_id,
            'restaurant_id' => $vendor->vendor_id,
            'restaurant_name' => $vendor->business_name,
            'name' => $vendor->business_name,
            'location' => $vendor->address,
            'distance_km' => $distanceKm !== null ? round($distanceKm, 1) : null,
            'distance' => $distanceKm !== null ? round($distanceKm, 1) . ' km' : null,
            'rating' => null,
            'offer_badge' => !empty($vendor->discount) ? ((string) $vendor->discount . '% OFF') : null,
            'image_url' => $image ? url('public/uploads/vendors/' . $image) : null,
        ];
    }

    /**
     * Mirrors admin Store Settings → Customer app home screen toggles.
     *
     * @return array{sliders: bool, offers: bool, promotions: bool, announcements: bool, featured_products: bool}
     */
    private function customerAppHomeVisibility(): array
    {
        $defaults = [
            'sliders' => true,
            'offers' => true,
            'promotions' => true,
            'announcements' => true,
            'featured_products' => true,
        ];

        if (!Schema::hasTable('store_settings')) {
            return $defaults;
        }

        $columnMap = [
            'sliders' => 'customer_home_sliders_enabled',
            'offers' => 'customer_home_offers_enabled',
            'promotions' => 'customer_home_promotions_enabled',
            'announcements' => 'customer_home_announcements_enabled',
            'featured_products' => 'customer_home_featured_products_enabled',
        ];

        $setting = StoreSetting::first();
        if (!$setting) {
            return $defaults;
        }

        $out = [];
        foreach ($columnMap as $key => $column) {
            if (!Schema::hasColumn('store_settings', $column)) {
                $out[$key] = true;
            } else {
                $out[$key] = (bool) $setting->{$column};
            }
        }

        return $out;
    }

    /**
     * @param array{sliders: bool, offers: bool, promotions: bool, announcements: bool, featured_products: bool} $visibility
     */
    private function customerHomeBannerSectionVisible(array $visibility, string $section): bool
    {
        return match ($section) {
            Banner::TYPE_OFFER => $visibility['offers'],
            Banner::TYPE_PROMOTION => $visibility['promotions'],
            Banner::TYPE_ANNOUNCEMENT => $visibility['announcements'],
            default => $visibility['sliders'],
        };
    }

    private function resolveBannerHomeSection(Banner $banner, bool $hasBannerTypeColumn): string
    {
        if (!$hasBannerTypeColumn) {
            return Banner::TYPE_SLIDER;
        }

        $t = strtolower(trim((string) ($banner->banner_type ?? '')));
        if ($t === '') {
            return Banner::TYPE_SLIDER;
        }

        if (in_array($t, Banner::homeBannerTypeOptions(), true)) {
            return $t;
        }

        return Banner::TYPE_SLIDER;
    }

    private function mapBannerForHome(Banner $banner, bool $hasBannerTypeColumn): array
    {
        $section = $this->resolveBannerHomeSection($banner, $hasBannerTypeColumn);

        return [
            'id' => $banner->id,
            'banner_type' => $section,
            'title' => $banner->title,
            'link' => $banner->button_link,
            'visible_from' => $banner->visible_from?->format('Y-m-d'),
            'visible_to' => $banner->visible_to?->format('Y-m-d'),
            'sort_order' => Schema::hasColumn('banners', 'sort_order') ? (int) $banner->sort_order : null,
            'banner_image' => $banner->banner_image,
            'banner_image_url' => !empty($banner->banner_image)
                ? url('public/uploads/banners/' . $banner->banner_image)
                : null,
        ];
    }

    private function mapFeaturedProduct(Product $product, array $reviewCounts = []): array
    {
        $row = [
            'product_id' => $product->product_id,
            'product_name' => $product->product_name,
            'target_user_type' => $product->target_user_type,
            'short_description' => $product->short_description,
            'price' => $product->price,
            'sale_price' => $product->sale_price,
            'discount' => $product->discount,
            'stock' => $product->stock,
            'product_image' => $product->product_image,
            'product_image_url' => !empty($product->product_image)
                ? url('public/uploads/products/' . $product->product_image)
                : null,
            'review_count' => (int) ($reviewCounts[$product->product_id] ?? 0),
        ];

        if (Schema::hasColumn('products', 'sale_status')) {
            $row['sale_status'] = $product->sale_status;
        }

        return $row;
    }
}
