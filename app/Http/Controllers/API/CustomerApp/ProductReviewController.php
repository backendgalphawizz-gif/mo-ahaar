<?php

namespace App\Http\Controllers\API\CustomerApp;

use App\Http\Controllers\Controller;
use App\Models\Customers;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Users;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    private const CUSTOMER_ROLE_TYPE = 2;

    /**
     * POST /api/customer-app/reviews/products/{productId}
     * Rate and review a purchased product.
     */
    public function store(Request $request, int $productId)
    {
        /** @var Users|null $user */
        $user = $request->user();

        if (!$user || (int) $user->role_type !== self::CUSTOMER_ROLE_TYPE) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized customer access',
            ], 403);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['nullable', 'string', 'max:2000'],
        ]);

        $customer = Customers::where('user_id', $user->user_id)->first();
        if (!$customer) {
            return response()->json([
                'status' => false,
                'message' => 'Customer profile not found',
            ], 404);
        }

        $product = Product::query()
            ->visibleToCustomerUser($user)
            ->where('product_id', $productId)
            ->first();
        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $deliveredStatuses = ['delivered', 'completed'];

        $deliveredOrderItem = OrderItem::query()
            ->join('orders as o', 'o.order_id', '=', 'order_items.order_id')
            ->where('o.customer_id', $customer->customer_id)
            ->where('order_items.product_id', $productId)
            ->whereIn('o.order_status', $deliveredStatuses)
            ->select('order_items.item_id', 'order_items.order_id')
            ->orderByDesc('order_items.item_id')
            ->first();

        if (!$deliveredOrderItem) {
            return response()->json([
                'status' => false,
                'message' => 'You can review only purchased and delivered products',
            ], 422);
        }

        $review = ProductReview::updateOrCreate(
            [
                'product_id' => $productId,
                'customer_id' => $customer->customer_id,
            ],
            [
                'user_id' => $user->user_id,
                'order_id' => $deliveredOrderItem->order_id,
                'order_item_id' => $deliveredOrderItem->item_id,
                'rating' => (int) $validated['rating'],
                'review' => $validated['review'] ?? null,
                'status' => 1,
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Review submitted successfully',
            'data' => [
                'review_id' => $review->review_id,
                'product_id' => $review->product_id,
                'rating' => (int) $review->rating,
                'review' => $review->review,
                'submitted_at' => $review->updated_at ? $review->updated_at->toDateTimeString() : null,
            ],
        ], 200);
    }

    /**
     * GET /api/customer-app/reviews/products/{productId}
     * View reviews submitted by users for a product.
     */
    public function index(Request $request, int $productId)
    {
        /** @var Users|null $user */
        $user = $request->user();

        if (!$user || (int) $user->role_type !== self::CUSTOMER_ROLE_TYPE) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized customer access',
            ], 403);
        }

        $product = Product::query()
            ->visibleToCustomerUser($user)
            ->where('product_id', $productId)
            ->first();
        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $perPage = (int) $request->input('per_page', 20);
        if ($perPage <= 0) {
            $perPage = 20;
        }
        $perPage = min($perPage, 100);

        $reviewsQuery = ProductReview::query()
            ->with('user:user_id,name,profile_image')
            ->where('product_id', $productId)
            ->where('status', 1)
            ->orderByDesc('review_id');

        $stats = ProductReview::query()
            ->where('product_id', $productId)
            ->where('status', 1)
            ->selectRaw('COUNT(*) as total_reviews, COALESCE(AVG(rating), 0) as average_rating')
            ->first();

        $ratingBuckets = ProductReview::query()
            ->where('product_id', $productId)
            ->where('status', 1)
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $paginated = $reviewsQuery->paginate($perPage);

        $reviews = collect($paginated->items())->map(function (ProductReview $review) use ($user) {
            return [
                'review_id' => $review->review_id,
                'rating' => (int) $review->rating,
                'review' => $review->review,
                'is_mine' => (int) $review->user_id === (int) $user->user_id,
                'reviewer' => [
                    'user_id' => $review->user?->user_id,
                    'name' => $review->user?->name,
                    'profile_image_url' => !empty($review->user?->profile_image)
                        ? url('public/uploads/customers/' . $review->user->profile_image)
                        : null,
                ],
                'created_at' => $review->created_at ? $review->created_at->toDateTimeString() : null,
            ];
        })->values();

        return response()->json([
            'status' => true,
            'message' => 'Product reviews retrieved successfully',
            'data' => [
                'product' => [
                    'product_id' => $product->product_id,
                    'product_name' => $product->product_name,
                ],
                'summary' => [
                    'average_rating' => round((float) ($stats->average_rating ?? 0), 2),
                    'total_reviews' => (int) ($stats->total_reviews ?? 0),
                    'ratings_breakdown' => [
                        '5' => (int) ($ratingBuckets[5] ?? 0),
                        '4' => (int) ($ratingBuckets[4] ?? 0),
                        '3' => (int) ($ratingBuckets[3] ?? 0),
                        '2' => (int) ($ratingBuckets[2] ?? 0),
                        '1' => (int) ($ratingBuckets[1] ?? 0),
                    ],
                ],
                'reviews' => $reviews,
                'pagination' => [
                    'current_page' => $paginated->currentPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                    'last_page' => $paginated->lastPage(),
                ],
            ],
        ], 200);
    }

    /**
     * GET /api/customer-app/reviews/my
     * View current customer's submitted reviews.
     */
    public function myReviews(Request $request)
    {
        /** @var Users|null $user */
        $user = $request->user();

        if (!$user || (int) $user->role_type !== self::CUSTOMER_ROLE_TYPE) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized customer access',
            ], 403);
        }

        $customer = Customers::where('user_id', $user->user_id)->first();
        if (!$customer) {
            return response()->json([
                'status' => false,
                'message' => 'Customer profile not found',
            ], 404);
        }

        $perPage = (int) $request->input('per_page', 20);
        if ($perPage <= 0) {
            $perPage = 20;
        }
        $perPage = min($perPage, 100);

        $paginated = ProductReview::query()
            ->with('product:product_id,product_name,product_image')
            ->where('customer_id', $customer->customer_id)
            ->orderByDesc('review_id')
            ->paginate($perPage);

        $reviews = collect($paginated->items())->map(function (ProductReview $review) {
            return [
                'review_id' => $review->review_id,
                'product_id' => $review->product_id,
                'product_name' => $review->product?->product_name,
                'product_image_url' => !empty($review->product?->product_image)
                    ? url('public/uploads/products/' . $review->product->product_image)
                    : null,
                'rating' => (int) $review->rating,
                'review' => $review->review,
                'status' => (int) $review->status,
                'updated_at' => $review->updated_at ? $review->updated_at->toDateTimeString() : null,
            ];
        })->values();

        return response()->json([
            'status' => true,
            'message' => 'My reviews retrieved successfully',
            'data' => [
                'reviews' => $reviews,
                'pagination' => [
                    'current_page' => $paginated->currentPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                    'last_page' => $paginated->lastPage(),
                ],
            ],
        ], 200);
    }
}
