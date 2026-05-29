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
use Illuminate\Support\Facades\Schema;  

class ProductBrowsingController extends Controller
{
    private const CUSTOMER_ROLE_TYPE = 2;

    // public function categories(Request $request)
    // {
    //     $categories = ProductCategory::where('status', 1)
    //         ->with(['subCategories' => function($q) {
    //             $q->where('status', 1);
    //         }])
    //         ->get();

    //     return response()->json([
    //         'success' => true,
    //         'data' => $categories
    //     ]);
    // }

    public function search(Request $request)
    {
        $user = $request->user();
        if (!$user || (int) $user->role_type !== self::CUSTOMER_ROLE_TYPE) {
            return response()->json(['status' => false, 'message' => 'Please log in to browse products'], 403);
        }

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'query' => ['nullable', 'string', 'max:120'],
        ]);

        $term = trim((string) ($validated['q'] ?? $validated['query'] ?? ''));
        if ($term === '') {
            return response()->json([
                'status' => true,
                'message' => 'Search query is required',
                'data' => ['products' => [], 'restaurants' => []],
            ], 200);
        }

        $like = '%' . $term . '%';

        $products = Product::query()
            ->where('status', 1)
            ->whereIn('is_active_status', [1, '1'])
            ->where(function ($q) use ($like) {
                $q->where('product_name', 'like', $like)
                    ->orWhere('short_description', 'like', $like)
                    ->orWhere('tags', 'like', $like);
            })
            ->limit(30)
            ->get()
            ->map(fn (Product $product) => $this->mapProductListItem($product))
            ->values();

        $restaurants = collect();
        if (Schema::hasTable('vendors')) {
            $restaurants = Vendor::query()
                ->when(Schema::hasColumn('vendors', 'status'), fn ($q) => $q->where('status', '1'))
                ->where(function ($q) use ($like) {
                    $q->where('business_name', 'like', $like)
                        ->orWhere('business_description', 'like', $like)
                        ->orWhere('address', 'like', $like);
                })
                ->limit(20)
                ->get()
                ->map(fn (Vendor $vendor) => [
                    'vendor_id' => $vendor->vendor_id,
                    'restaurant_id' => $vendor->vendor_id,
                    'restaurant_name' => $vendor->business_name,
                    'location' => $vendor->address,
                    'image_url' => !empty($vendor->business_banner)
                        ? url('public/uploads/vendors/' . $vendor->business_banner)
                        : (!empty($vendor->business_logo) ? url('public/uploads/vendors/' . $vendor->business_logo) : null),
                ])
                ->values();
        }

        return response()->json([
            'status' => true,
            'message' => 'Search results retrieved successfully',
            'data' => [
                'query' => $term,
                'products' => $products,
                'restaurants' => $restaurants,
            ],
        ], 200);
    }

    public function categories(Request $request)
    {
        $user = $request->user();
        $restaurantId = $this->resolveRestaurantId($request);

        if ($error = $this->restaurantNotFoundResponse($restaurantId)) {
            return $error;
        }

        $categories = ProductCategory::query()
            ->where('status', 1)
            ->whereHas('subCategories', function ($q) use ($user, $restaurantId) {
                $q->where('status', 1)
                    ->whereHas('products', function ($p) use ($user, $restaurantId) {
                        $this->applyCategoryProductScope($p, $user, $restaurantId);
                    });
            })
            ->with(['subCategories' => function ($q) use ($user, $restaurantId) {
                $q->where('status', 1)
                    ->whereHas('products', function ($p) use ($user, $restaurantId) {
                        $this->applyCategoryProductScope($p, $user, $restaurantId);
                    });
            }])
            ->get();

        $mapped = $categories->map(function (ProductCategory $category) {
            return [
                'category_id' => $category->category_id,
                'category_name' => $category->category_name,
                'name' => $category->category_name,
                'category_image' => $category->category_image,
                'category_image_url' => !empty($category->category_image)
                    ? url('public/uploads/categories/' . $category->category_image)
                    : null,
                'sub_categories' => $category->subCategories,
            ];
        })->values();

        return response()->json([
            'status' => true,
            'success' => true,
            'message' => 'Categories retrieved successfully',
            'data' => $mapped,
        ], 200);
    }


    /**
     * List active products (legacy route name: by-user-type).
     * Optional query parameters: category, subcategory
     * 
     * Examples:
     *   /customer-app/products/by-user-type
     *   /customer-app/products/by-user-type?category=1
     *   /customer-app/products/by-user-type?subcategory=2
     *   /customer-app/products/by-user-type?category=1&subcategory=2
     */
    public function productsByUserType(Request $request)
    {
        $user = $request->user();
        $categoryId = $request->query('category');
        $subCategoryId = $request->query('subcategory');
        
        $query = Product::query()->where('status', 1);
        
        // Filter by category if provided
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        
        // Filter by subcategory if provided
        if ($subCategoryId) {
            $query->where('sub_category_id', $subCategoryId);
        }
        
        $products = $query->get();
        
        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

        /**
     * Product detail by product id, or restaurant detail when restaurant_id / type=restaurant is passed.
     *
     * Product:  GET /products/detail/{productId}
     * Restaurant: GET /products/detail?restaurant_id={restaurantId}
     *             GET /products/detail?restaurant_id={restaurantId}&category_id={categoryId}
     *             GET /products/detail/{restaurantId}?type=restaurant
     */
    public function productDetail(Request $request, $productId = null)
    {
        $user = $request->user();
        $restaurantId = $this->resolveRestaurantDetailModeId($request, $productId);

        if ($restaurantId !== null) {
            return $this->restaurantDetailResponse($request, $user, $restaurantId);
        }

        if ($productId === null || $productId === '') {
            return response()->json([
                'success' => false,
                'message' => 'Product id or restaurant_id is required.',
            ], 422);
        }

        $baseProduct = Product::where('product_id', $productId)->first();

        if (!$baseProduct) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        if ((int) $baseProduct->status !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'This product is currently inactive.',
            ], 404);
        }

        $product = Product::with('details')
            ->where('product_id', $productId)
            ->where('status', 1)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'This product is not available.',
            ], 404);
        }

        $restaurant = null;
        $restaurantProducts = [];

        if (
            Schema::hasTable('vendors')
            && Schema::hasColumn('products', 'vendor_id')
            && !empty($product->vendor_id)
        ) {
            $vendor = Vendor::find($product->vendor_id);
            if ($vendor) {
                $restaurant = $this->mapRestaurant($vendor);

                $restaurantProductRows = Product::query()
                    ->where('vendor_id', $product->vendor_id)
                    ->where('status', 1)
                    ->when(Schema::hasColumn('products', 'is_active_status'), fn ($q) => $q->where('is_active_status', 1))
                    ->orderByDesc('featured')
                    ->orderByDesc('product_id')
                    ->get();

                $restaurantReviewCounts = [];
                if (Schema::hasTable('product_reviews') && $restaurantProductRows->isNotEmpty()) {
                    $restaurantReviewCounts = ProductReview::query()
                        ->where('status', 1)
                        ->whereIn('product_id', $restaurantProductRows->pluck('product_id')->all())
                        ->selectRaw('product_id, COUNT(*) as total_reviews')
                        ->groupBy('product_id')
                        ->pluck('total_reviews', 'product_id')
                        ->map(fn ($count) => (int) $count)
                        ->all();
                }

                $restaurantProducts = $restaurantProductRows
                    ->map(fn (Product $row) => $this->mapFeaturedProduct($row, $restaurantReviewCounts, $vendor))
                    ->values()
                    ->all();
            }
        }

        return response()->json([
            'success' => true,
            'data'    => array_merge($this->buildFullProductDetailData($product, $request), [
                'restaurant' => $restaurant,
                'restaurant_products' => $restaurantProducts,
            ]),
        ]);
    }

    private function restaurantDetailResponse(Request $request, $user, int $restaurantId)
    {
        if ($error = $this->restaurantNotFoundResponse($restaurantId)) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant not found.',
            ], 404);
        }

        $filterCategoryId = $this->resolveCategoryId($request);
        if ($error = $this->categoryNotFoundResponse($filterCategoryId)) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        $vendor = Vendor::where('vendor_id', $restaurantId)->first();
        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant not found.',
            ], 404);
        }

        $productsQuery = Product::with('details')
            ->where('vendor_id', $restaurantId)
            ->where('status', 1)
            ->when(Schema::hasColumn('products', 'is_active_status'), fn ($q) => $q->whereIn('is_active_status', [1, '1']));

        if ($filterCategoryId !== null) {
            $productsQuery->where('category_id', $filterCategoryId);
        }

        $products = $productsQuery
            ->orderByDesc('featured')
            ->orderByDesc('product_id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'restaurant' => $this->mapCompleteRestaurant($vendor, $request, $user),
                'products' => $products
                    ->map(fn (Product $product) => $this->buildFullProductDetailData($product, $request))
                    ->values()
                    ->all(),
            ],
        ]);
    }

    /**
     * Full product payload (same fields as single product detail API).
     */
    private function buildFullProductDetailData(Product $product, Request $request): array
    {
        $productId = (int) $product->product_id;
        $reviewQuery = ProductReview::where('product_id', $productId)->where('status', 1);
        $totalReviews = (clone $reviewQuery)->count();
        $averageRating = $totalReviews > 0
            ? round((clone $reviewQuery)->avg('rating'), 1)
            : null;

        $ratingSummary = [];
        for ($star = 5; $star >= 1; $star--) {
            $count = (clone $reviewQuery)->where('rating', $star)->count();
            $ratingSummary[] = [
                'star'       => $star,
                'count'      => $count,
                'percentage' => $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0,
            ];
        }

        $perPage = (int) $request->query('reviews_per_page', 5);
        $page = (int) $request->query('reviews_page', 1);
        $reviews = (clone $reviewQuery)
            ->with(['customer:customer_id,user_id', 'customer.user:user_id,name', 'user:user_id,name'])
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        return array_merge($product->toArray(), [
            'average_rating' => $averageRating,
            'total_reviews'  => $totalReviews,
            'rating_summary' => $ratingSummary,
            'reviews'        => [
                'data'          => $reviews->items(),
                'current_page'  => $reviews->currentPage(),
                'per_page'      => $reviews->perPage(),
                'total'         => $reviews->total(),
                'last_page'     => $reviews->lastPage(),
                'has_more'      => $reviews->hasMorePages(),
            ],
        ]);
    }

    private function resolveRestaurantDetailModeId(Request $request, $pathId): ?int
    {
        if (
            $request->filled('restaurant_id')
            || $request->filled('restraunt_id')
            || $request->filled('vendor_id')
        ) {
            return $this->resolveRestaurantId($request);
        }

        $type = strtolower((string) $request->query('type', ''));
        if (in_array($type, ['restaurant', 'vendor'], true) && $pathId !== null && $pathId !== '') {
            return (int) $pathId;
        }

        return null;
    }

    private function mapCompleteRestaurant(Vendor $vendor, Request $request, $user): array
    {
        $banner = $vendor->business_banner ?: $vendor->shop_image;
        $image = $banner ?: $vendor->business_logo;
        $distanceKm = null;

        if ($user && Schema::hasTable('customers')) {
            $customer = Customers::where('user_id', $user->user_id)->first();
            if (
                $customer
                && $customer->latitude
                && $customer->longitude
                && $vendor->latitude
                && $vendor->longitude
            ) {
                $distanceKm = $this->haversineKm(
                    (float) $customer->latitude,
                    (float) $customer->longitude,
                    (float) $vendor->latitude,
                    (float) $vendor->longitude
                );
            }
        }

        if ($distanceKm === null && $request->filled('latitude') && $request->filled('longitude') && $vendor->latitude && $vendor->longitude) {
            $distanceKm = $this->haversineKm(
                (float) $request->input('latitude'),
                (float) $request->input('longitude'),
                (float) $vendor->latitude,
                (float) $vendor->longitude
            );
        }

        return [
            'vendor_id' => $vendor->vendor_id,
            'restaurant_id' => $vendor->vendor_id,
            'vendor_code' => $vendor->vendor_code ?? null,
            'restaurant_name' => $vendor->business_name,
            'name' => $vendor->business_name,
            'owner_name' => $vendor->owner_name,
            'business_name' => $vendor->business_name,
            'business_type' => $vendor->business_type,
            'business_description' => $vendor->business_description,
            'description' => $vendor->business_description,
            'location' => $vendor->address,
            'address' => $vendor->address,
            'latitude' => $vendor->latitude,
            'longitude' => $vendor->longitude,
            'mobile' => $vendor->mobile ?? $vendor->business_phone,
            'business_phone' => $vendor->business_phone,
            'business_email' => $vendor->business_email,
            'rating' => $this->vendorAverageRating((int) $vendor->vendor_id),
            'banner_image_url' => $banner ? url('public/uploads/vendors/' . $banner) : null,
            'image_url' => $image ? url('public/uploads/vendors/' . $image) : null,
            'logo_url' => !empty($vendor->business_logo)
                ? url('public/uploads/vendors/' . $vendor->business_logo)
                : null,
            'approval_status' => $vendor->approval_status ?? null,
            'status' => $vendor->status ?? null,
            'distance_km' => $distanceKm !== null ? round($distanceKm, 1) : null,
            'distance' => $distanceKm !== null ? round($distanceKm, 1) . ' km' : null,
            'created_at' => $vendor->created_at,
            'updated_at' => $vendor->updated_at,
        ];
    }

    private function vendorAverageRating(int $vendorId): ?float
    {
        if (!Schema::hasTable('product_reviews') || !Schema::hasColumn('products', 'vendor_id')) {
            return null;
        }

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

    /**
    * Show category details by category id
    */
    public function categoryDetails(Request $request, $categoryId)
    {
        $user = $request->user();
        $restaurantId = $this->resolveRestaurantId($request);

        if ($error = $this->restaurantNotFoundResponse($restaurantId)) {
            return $error;
        }

        $category = ProductCategory::where('category_id', $categoryId)->where('status', 1)->first();
        if (!$category) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        $productsQuery = Product::query()
            ->where('category_id', $categoryId)
            ->where('status', 1)
            ->whereIn('is_active_status', [1, '1']);

        if ($restaurantId !== null && Schema::hasColumn('products', 'vendor_id')) {
            $productsQuery->where('vendor_id', $restaurantId);
        }

        $products = $productsQuery
            ->orderByDesc('featured')
            ->orderBy('product_name')
            ->get()
            ->map(fn (Product $product) => $this->mapProductListItem($product))
            ->values();

        return response()->json([
            'status' => true,
            'success' => true,
            'message' => 'Category details retrieved successfully',
            'data' => [
                'category' => [
                    'category_id' => $category->category_id,
                    'category_name' => $category->category_name,
                    'name' => $category->category_name,
                    'category_image_url' => !empty($category->category_image)
                        ? url('public/uploads/categories/' . $category->category_image)
                        : null,
                ],
                'products' => $products,
            ],
        ]);
    }

    private function mapProductListItem(Product $product): array
    {
        $effectivePrice = (float) ($product->sale_price ?: $product->price);

        return [
            'product_id' => $product->product_id,
            'name' => $product->product_name,
            'product_name' => $product->product_name,
            'description' => $product->short_description,
            'price' => number_format($effectivePrice, 2, '.', ''),
            'original_price' => $product->sale_price ? number_format((float) $product->price, 2, '.', '') : null,
            'currency' => 'INR',
            'rating' => null,
            'image_url' => !empty($product->product_image)
                ? url('public/uploads/products/' . $product->product_image)
                : null,
            'is_vegetarian' => true,
        ];
    }

    /**
     * Same shape as home/dashboard featured_products items.
     *
     * @param  array<int,int>  $reviewCounts
     */
    private function mapFeaturedProduct(Product $product, array $reviewCounts = [], ?Vendor $vendor = null): array
    {
        $restaurantName = $vendor?->business_name;
        if ($restaurantName === null && Schema::hasColumn('products', 'store_name') && !empty($product->store_name)) {
            $restaurantName = $product->store_name;
        }

        $row = [
            'product_id' => $product->product_id,
            'product_name' => $product->product_name,
            'short_description' => $product->short_description,
            'price' => $product->price,
            'sale_price' => $product->sale_price,
            'discount' => $product->discount,
            'product_image' => $product->product_image,
            'product_image_url' => !empty($product->product_image)
                ? url('public/uploads/products/' . $product->product_image)
                : null,
            'review_count' => (int) ($reviewCounts[$product->product_id] ?? 0),
            'restaurant_name' => $restaurantName,
            'location' => $vendor?->address,
        ];

        if (Schema::hasColumn('products', 'sale_status')) {
            $row['sale_status'] = $product->sale_status;
        }

        return $row;
    }

    private function mapRestaurant(Vendor $vendor): array
    {
        $image = $vendor->business_banner ?: ($vendor->shop_image ?: $vendor->business_logo);

        return [
            'vendor_id' => $vendor->vendor_id,
            'restaurant_id' => $vendor->vendor_id,
            'restaurant_name' => $vendor->business_name,
            'name' => $vendor->business_name,
            'location' => $vendor->address,
            'description' => $vendor->business_description,
            'image_url' => $image ? url('public/uploads/vendors/' . $image) : null,
            'logo_url' => !empty($vendor->business_logo)
                ? url('public/uploads/vendors/' . $vendor->business_logo)
                : null,
        ];
    }

    private function resolveRestaurantId(Request $request): ?int
    {
        $raw = $request->query(
            'restaurant_id',
            $request->query('restraunt_id', $request->query('vendor_id'))
        );
        if ($raw === null || $raw === '') {
            return null;
        }

        return (int) $raw;
    }

    private function resolveCategoryId(Request $request): ?int
    {
        $raw = $request->query('category_id', $request->query('category'));
        if ($raw === null || $raw === '') {
            return null;
        }

        return (int) $raw;
    }

    private function categoryNotFoundResponse(?int $categoryId): ?\Illuminate\Http\JsonResponse
    {
        if ($categoryId === null) {
            return null;
        }

        $exists = ProductCategory::where('category_id', $categoryId)->where('status', 1)->exists();
        if ($exists) {
            return null;
        }

        return response()->json([
            'status' => false,
            'success' => false,
            'message' => 'Category not found.',
        ], 404);
    }

    private function restaurantNotFoundResponse(?int $restaurantId): ?\Illuminate\Http\JsonResponse
    {
        if ($restaurantId === null || !Schema::hasTable('vendors')) {
            return null;
        }

        $exists = Vendor::where('vendor_id', $restaurantId)->exists();
        if ($exists) {
            return null;
        }

        return response()->json([
            'status' => false,
            'success' => false,
            'message' => 'Restaurant not found.',
        ], 404);
    }

    /**
     * Product constraints used when building category/sub-category trees.
     */
    private function applyCategoryProductScope($query, $user, ?int $restaurantId = null): void
    {
        $query
            ->where('status', 1);

        if ($restaurantId !== null && Schema::hasColumn('products', 'vendor_id')) {
            $query->where('vendor_id', $restaurantId);
        }
    }

    /**
     * Show sub-category details by sub-category id
     */
    public function subCategoryDetails(Request $request, $subCategoryId)
    {
        $subCategory = ProductSubCategory::where('sub_category_id', $subCategoryId)->where('status', 1)->first();
        if (!$subCategory) {
            return response()->json([
                'success' => false,
                'message' => 'Sub-category not found.'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $subCategory
        ]);
    }

}
