<?php

namespace App\Http\Controllers\API\CustomerApp;

use App\Http\Controllers\Controller;
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
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
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
            ->visibleToCustomerUser($user)
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
     * Show products according to user type (retailer/wholesaler)
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
        
        $query = Product::visibleToCustomerUser($user)->where('status', 1);
        
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
     * Show product details by product id
     */
    public function productDetail(Request $request, $productId)
    {
        $user = $request->user();
        $product = Product::with('details')
            ->visibleToCustomerUser($user)
            ->where('product_id', $productId)
            ->where('status', 1)
            ->first();

        // First check: does the product exist at all (ignoring user-type visibility)?
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

        // Second check: apply user-type visibility
        $product = Product::with('details')
            ->visibleToCustomerUser($user)
            ->where('product_id', $productId)
            ->where('status', 1)
            ->first();

        if (!$product) {
            $productSegment = $baseProduct->target_user_type ?? 'All';
            $userSegment    = $user ? ($user->user_type ?? 'Unknown') : 'Unauthenticated';
            return response()->json([
                'success'         => false,
                'message'         => 'This product is not available for your account type.',
                'product_segment' => $productSegment,
                'your_segment'    => $userSegment,
            ], 403);
        }

        // Rating summary
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

        // Recent reviews (read more — first page, 5 per page)
        $perPage  = (int) $request->query('reviews_per_page', 5);
        $page     = (int) $request->query('reviews_page', 1);
        $reviews  = (clone $reviewQuery)
            ->with(['customer:customer_id,user_id', 'customer.user:user_id,name', 'user:user_id,name'])
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

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
                    ->visibleToCustomerUser($user)
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
            'data'    => array_merge($product->toArray(), [
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
                'restaurant' => $restaurant,
                'restaurant_products' => $restaurantProducts,
            ]),
        ]);
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
            ->visibleToCustomerUser($user)
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
        $raw = $request->query('restaurant_id', $request->query('vendor_id'));
        if ($raw === null || $raw === '') {
            return null;
        }

        return (int) $raw;
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
        $query->visibleToCustomerUser($user)
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
