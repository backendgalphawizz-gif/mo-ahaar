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
        $categories = ProductCategory::query()
            ->where('status', 1)

            ->whereHas('subCategories', function ($q) use ($user) {
                $q->where('status', 1)
                    ->whereHas('products', function ($p) use ($user) {
                        $p->visibleToCustomerUser($user);
                        $p->where('status', 1);
                    });
            })

            ->with(['subCategories' => function ($q) use ($user) {
                $q->where('status', 1)
                    ->whereHas('products', function ($p) use ($user) {
                        $p->visibleToCustomerUser($user);
                        $p->where('status', 1);
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
            ]),
        ]);
    }

    /**
    * Show category details by category id
    */
    public function categoryDetails(Request $request, $categoryId)
    {
        $user = $request->user();

        $category = ProductCategory::where('category_id', $categoryId)->where('status', 1)->first();
        if (!$category) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        $products = Product::query()
            ->visibleToCustomerUser($user)
            ->where('category_id', $categoryId)
            ->where('status', 1)
            ->whereIn('is_active_status', [1, '1'])
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
