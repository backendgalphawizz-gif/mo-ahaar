<?php

namespace App\Http\Controllers\API\CustomerApp;

use App\Http\Controllers\Controller;
use App\Models\Customers;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductReview;
use App\Models\ProductSubCategory;
use App\Models\Users;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VendorBrowsingController extends Controller
{
    /**
     * GET /api/customer-app/vendors/nearby
     */
    public function nearby(Request $request)
    {
        $user = $request->user();
        if (!$this->isAuthorizedCustomer($user)) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        $customer = Customers::where('user_id', $user->user_id)->first();
        [$latitude, $longitude, $error] = $this->resolveCoordinates($request, $customer);
        if ($error) {
            return response()->json(['status' => false, 'message' => $error], 422);
        }

        $radiusKm = (float) $request->input('radius_km', config('customer-app.nearby_radius_km', 15));
        if ($radiusKm <= 0) {
            $radiusKm = 15;
        }

        $limit = min((int) $request->input('limit', 20), 50);
        if ($limit <= 0) {
            $limit = 20;
        }

        $distanceExpression = '(6371 * acos(cos(radians(?)) * cos(radians(v.latitude)) * cos(radians(v.longitude) - radians(?)) + sin(radians(?)) * sin(radians(v.latitude))))';

        $vendors = DB::table('vendors as v')
            ->select(
                'v.vendor_id',
                'v.business_name',
                'v.business_description',
                'v.address',
                'v.business_logo',
                'v.business_banner',
                'v.shop_image',
                'v.latitude',
                'v.longitude'
            )
            ->selectRaw($distanceExpression . ' as distance_km', [$latitude, $longitude, $latitude])
            ->whereNotNull('v.latitude')
            ->whereNotNull('v.longitude')
            ->when(Schema::hasColumn('vendors', 'status'), fn ($q) => $q->where('v.status', '1'))
            ->when(Schema::hasColumn('vendors', 'approval_status'), fn ($q) => $q->where('v.approval_status', 'approved'))
            ->havingRaw('distance_km <= ?', [$radiusKm])
            ->orderBy('distance_km')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => $this->mapVendorCard($row))
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Nearby restaurants retrieved successfully',
            'data' => [
                'search_center' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'radius_km' => $radiusKm,
                ],
                'restaurants' => $vendors,
                'nearby_restaurants' => $vendors,
            ],
        ]);
    }

    /**
     * GET /api/customer-app/vendors/{vendorId}
     */
    public function show(Request $request, int $vendorId)
    {
        $user = $request->user();
        if (!$this->isAuthorizedCustomer($user)) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        $vendor = Vendor::query()
            ->when(Schema::hasColumn('vendors', 'status'), fn ($q) => $q->where('status', '1'))
            ->where('vendor_id', $vendorId)
            ->first();

        if (!$vendor) {
            return response()->json(['status' => false, 'message' => 'Restaurant not found'], 404);
        }

        $customer = Customers::where('user_id', $user->user_id)->first();
        $distanceKm = null;
        if ($customer && $customer->latitude && $customer->longitude && $vendor->latitude && $vendor->longitude) {
            $distanceKm = $this->haversineKm(
                (float) $customer->latitude,
                (float) $customer->longitude,
                (float) $vendor->latitude,
                (float) $vendor->longitude
            );
        }

        return response()->json([
            'status' => true,
            'message' => 'Restaurant details retrieved successfully',
            'data' => [
                'restaurant' => array_merge($this->mapVendorModel($vendor), [
                    'distance_km' => $distanceKm !== null ? round($distanceKm, 1) : null,
                    'distance' => $distanceKm !== null ? round($distanceKm, 1) . ' km' : null,
                ]),
            ],
        ]);
    }

    /**
     * GET /api/customer-app/vendors/{vendorId}/menu
     */
    public function menu(Request $request, int $vendorId)
    {
        $user = $request->user();
        if (!$this->isAuthorizedCustomer($user)) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        $vendor = Vendor::query()
            ->when(Schema::hasColumn('vendors', 'status'), fn ($q) => $q->where('status', '1'))
            ->where('vendor_id', $vendorId)
            ->first();

        if (!$vendor) {
            return response()->json(['status' => false, 'message' => 'Restaurant not found'], 404);
        }

        $categoryId = $request->query('category_id');
        $subCategoryId = $request->query('sub_category_id');
        $isVeg = $request->has('is_veg') ? filter_var($request->query('is_veg'), FILTER_VALIDATE_BOOLEAN) : null;

        $categories = ProductCategory::query()
            ->where('status', 1)
            ->whereHas('products', function ($q) use ($user, $vendorId) {
                $q
                    ->where('vendor_id', $vendorId)
                    ->where('status', 1);
            })
            ->orderBy('category_name')
            ->get()
            ->map(fn (ProductCategory $category) => [
                'category_id' => $category->category_id,
                'category_name' => $category->category_name,
                'category_image_url' => !empty($category->category_image)
                    ? url('public/uploads/categories/' . $category->category_image)
                    : null,
            ])
            ->values();

        $productsQuery = Product::query()
            ->where('vendor_id', $vendorId)
            ->where('status', 1)
            ->whereIn('is_active_status', [1, '1']);

        if ($categoryId) {
            $productsQuery->where('category_id', $categoryId);
        }
        if ($subCategoryId) {
            $productsQuery->where('sub_category_id', $subCategoryId);
        }

        $products = $productsQuery
            ->orderBy('product_name')
            ->get()
            ->map(fn (Product $product) => $this->mapMenuProduct($product, $user))
            ->values();

        if ($isVeg !== null) {
            $products = $products->filter(fn ($row) => (bool) ($row['is_vegetarian'] ?? false) === $isVeg)->values();
        }

        return response()->json([
            'status' => true,
            'message' => 'Restaurant menu retrieved successfully',
            'data' => [
                'restaurant' => $this->mapVendorModel($vendor),
                'categories' => $categories,
                'menu_items' => $products,
                'filters' => [
                    'category_id' => $categoryId ? (int) $categoryId : null,
                    'sub_category_id' => $subCategoryId ? (int) $subCategoryId : null,
                    'is_veg' => $isVeg,
                ],
            ],
        ]);
    }

    private function mapVendorCard(object $row): array
    {
        $image = $row->business_banner ?: ($row->shop_image ?: $row->business_logo);

        return [
            'vendor_id' => $row->vendor_id,
            'restaurant_id' => $row->vendor_id,
            'restaurant_name' => $row->business_name,
            'name' => $row->business_name,
            'description' => $row->business_description,
            'location' => $row->address,
            'distance_km' => round((float) $row->distance_km, 1),
            'distance' => round((float) $row->distance_km, 1) . ' km',
            'rating' => $this->vendorAverageRating((int) $row->vendor_id),
            'image_url' => $image ? url('public/uploads/vendors/' . $image) : null,
            'logo_url' => !empty($row->business_logo) ? url('public/uploads/vendors/' . $row->business_logo) : null,
        ];
    }

    private function mapVendorModel(Vendor $vendor): array
    {
        $banner = $vendor->business_banner ?: $vendor->shop_image;

        return [
            'vendor_id' => $vendor->vendor_id,
            'restaurant_id' => $vendor->vendor_id,
            'restaurant_name' => $vendor->business_name,
            'name' => $vendor->business_name,
            'description' => $vendor->business_description,
            'location' => $vendor->address,
            'rating' => $this->vendorAverageRating((int) $vendor->vendor_id),
            'banner_image_url' => $banner ? url('public/uploads/vendors/' . $banner) : null,
            'logo_url' => !empty($vendor->business_logo) ? url('public/uploads/vendors/' . $vendor->business_logo) : null,
            'mobile' => $vendor->mobile ?? $vendor->business_phone,
        ];
    }

    private function mapMenuProduct(Product $product, Users $user): array
    {
        $reviewQuery = ProductReview::where('product_id', $product->product_id)->where('status', 1);
        $averageRating = (clone $reviewQuery)->count() > 0
            ? round((float) (clone $reviewQuery)->avg('rating'), 1)
            : null;

        $effectivePrice = (float) ($product->sale_price ?: $product->price);
        $originalPrice = $product->sale_price ? (float) $product->price : null;

        return [
            'product_id' => $product->product_id,
            'item_id' => $product->product_id,
            'name' => $product->product_name,
            'product_name' => $product->product_name,
            'description' => $product->short_description,
            'price' => number_format($effectivePrice, 2, '.', ''),
            'original_price' => $originalPrice !== null ? number_format($originalPrice, 2, '.', '') : null,
            'currency' => 'INR',
            'rating' => $averageRating,
            'image_url' => !empty($product->product_image)
                ? url('public/uploads/products/' . $product->product_image)
                : null,
            'is_vegetarian' => $this->productIsVegetarian($product),
            'is_available' => (int) $product->is_active_status === 1 && (int) $product->status === 1,
            'category_id' => $product->category_id,
            'sub_category_id' => $product->sub_category_id,
        ];
    }

    private function productIsVegetarian(Product $product): bool
    {
        if (Schema::hasColumn('products', 'is_vegetarian')) {
            return (bool) $product->is_vegetarian;
        }

        $tags = strtolower((string) ($product->tags ?? ''));

        return str_contains($tags, 'veg') && !str_contains($tags, 'non');
    }

    private function vendorAverageRating(int $vendorId): ?float
    {
        $avg = ProductReview::query()
            ->join('products', 'products.product_id', '=', 'product_reviews.product_id')
            ->where('products.vendor_id', $vendorId)
            ->where('product_reviews.status', 1)
            ->avg('product_reviews.rating');

        return $avg !== null ? round((float) $avg, 1) : null;
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    private function resolveCoordinates(Request $request, ?Customers $customer): array
    {
        $latitude = $request->filled('latitude') ? (float) $request->input('latitude') : null;
        $longitude = $request->filled('longitude') ? (float) $request->input('longitude') : null;

        if ($latitude === null || $longitude === null) {
            $latitude = $customer?->latitude;
            $longitude = $customer?->longitude;
        }

        if ($latitude === null || $longitude === null) {
            return [null, null, 'Location is not enabled. Pass latitude/longitude or call location/enable first.'];
        }

        return [(float) $latitude, (float) $longitude, null];
    }

    private function isAuthorizedCustomer(?Users $user): bool
    {
        return $user && (int) $user->role_type === Users::CUSTOMER_APP_ROLE_TYPE;
    }
}
