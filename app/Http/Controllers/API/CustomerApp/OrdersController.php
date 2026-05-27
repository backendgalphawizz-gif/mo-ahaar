<?php

namespace App\Http\Controllers\API\CustomerApp;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\CustomerApp\NotificationController;
use App\Models\Customers;
use App\Models\Orders;
use App\Models\OrderItem;
use App\Models\OrderTracking;
use App\Models\ProductReview;
use App\Models\StoreSetting;
use App\Models\Users;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{
    private const CUSTOMER_ROLE_TYPE = Users::CUSTOMER_APP_ROLE_TYPE;
    private const STATUS_ORDER_PLACED = 'order_placed';
    private const STATUS_ORDER_CONFIRMED = 'confirmed';
    private const STATUS_PICKED_UP = 'picked_up';
    private const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    private const STATUS_DELIVERED = 'delivered';
    private const STATUS_CANCELLED = 'cancelled';

    public function history(Request $request)
    {
        return $this->index($request);
    }

    // -----------------------------------------------------------------------
    // GET /api/customer-app/orders
    // List all orders for the authenticated customer (paginated)
    // Query: status (optional), page (optional, default 1), per_page (optional, default 15)
    // -----------------------------------------------------------------------
    public function index(Request $request)
    {
        /** @var Users $user */
        $user = $request->user();
        if (!$user || (int) $user->role_type !== self::CUSTOMER_ROLE_TYPE) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        $customer = Customers::where('user_id', $user->user_id)->first();
        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Customer profile not found'], 404);
        }

        $perPage = min((int) $request->input('per_page', 15), 50);
        if ($perPage <= 0) {
            $perPage = 15;
        }

        $query = Orders::with('orderItems.product')
            ->where('customer_id', $customer->customer_id);

        if ($request->filled('status')) {
            $statusFilter = $this->normalizeStatus((string) $request->input('status'));
            $query->where('order_status', $statusFilter);
        }

        $orders = $query->orderByDesc('order_id')->paginate($perPage);

        $items = collect($orders->items())->map(fn (Orders $order) => $this->formatOrderSummary($order));

        return response()->json([
            'status'  => true,
            'message' => 'Orders retrieved successfully',
            'data'    => [
                'orders'       => $items,
                'current_page' => $orders->currentPage(),
                'per_page'     => $orders->perPage(),
                'total'        => $orders->total(),
                'last_page'    => $orders->lastPage(),
            ],
        ], 200);
    }

    // -----------------------------------------------------------------------
    // GET /api/customer-app/orders/{orderId}
    // Full details of a single order including all items
    // -----------------------------------------------------------------------
    public function show(Request $request, int $orderId)
    {
        /** @var Users $user */
        $user = $request->user();
        if (!$user || (int) $user->role_type !== self::CUSTOMER_ROLE_TYPE) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        $customer = Customers::where('user_id', $user->user_id)->first();
        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Customer profile not found'], 404);
        }

        $order = Orders::with(['orderItems', 'vendor'])
            ->where('order_id', $orderId)
            ->where('customer_id', $customer->customer_id)
            ->first();

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        // Load existing reviews by this customer for products in this order (one per product)
        $productIds = $order->orderItems->pluck('product_id')->filter()->unique()->values();
        $existingReviews = ProductReview::where('customer_id', $customer->customer_id)
            ->whereIn('product_id', $productIds)
            ->get()
            ->keyBy('product_id');

        $isDelivered = in_array((string) $order->order_status, ['delivered', 'completed'], true);

        $items = $order->orderItems->map(function (OrderItem $item) use ($existingReviews, $isDelivered) {
            $review = $existingReviews->get($item->product_id);
            return [
                'item_id'           => $item->item_id,
                'product_id'        => $item->product_id,
                'product_name'      => $item->product_name,
                'sku'               => $item->sku,
                'quantity'          => $item->quantity,
                'unit_price'        => number_format((float) $item->unit_price, 2, '.', ''),
                'discount_amount'   => number_format((float) $item->discount_amount, 2, '.', ''),
                'tax_amount'        => number_format((float) $item->tax_amount, 2, '.', ''),
                'line_total'        => number_format((float) $item->line_total, 2, '.', ''),
                'item_status'       => $item->item_status,
                'product_image_url' => $item->product && !empty($item->product->product_image)
                    ? url('public/uploads/products/' . $item->product->product_image)
                    : null,
                'can_review'        => $isDelivered && $review === null,
                'has_reviewed'      => $review !== null,
                'my_review'         => $review ? [
                    'review_id'    => $review->review_id,
                    'rating'       => (int) $review->rating,
                    'review'       => $review->review,
                    'submitted_at' => $review->updated_at ? $review->updated_at->toDateTimeString() : null,
                ] : null,
            ];
        })->values();

        $vendor = $order->vendor;

        return response()->json([
            'status'  => true,
            'message' => 'Order details retrieved successfully',
            'data'    => [
                'order'  => [
                    'order_id'         => $order->order_id,
                    'order_number'     => $order->order_number,
                    'order_status'     => $order->order_status,
                    'payment_method'   => $order->payment_method,
                    'payment_status'   => $order->payment_status,
                    'subtotal'         => number_format((float) $order->subtotal, 2, '.', ''),
                    'tax_amount'       => number_format((float) $order->tax_amount, 2, '.', ''),
                    'shipping_amount'  => number_format((float) $order->shipping_amount, 2, '.', ''),
                    'total_amount'     => number_format((float) $order->total_amount, 2, '.', ''),
                    'shipping_address' => $order->shipping_address,
                    'notes'            => $order->notes,
                    'can_cancel'       => $this->canCancelOrder($order),
                    'placed_at'        => $order->created_at ? $order->created_at->toDateTimeString() : null,
                    'updated_at'       => $order->updated_at ? $order->updated_at->toDateTimeString() : null,
                ],
                'vendor' => $vendor ? [
                    'vendor_id'     => $vendor->vendor_id,
                    'business_name' => $vendor->business_name,
                    'mobile'        => $vendor->mobile,
                ] : null,
                'items'  => $items,
            ],
        ], 200);
    }

    // -----------------------------------------------------------------------
    // GET /api/customer-app/orders/{orderId}/tracking
    // Live tracking timeline for a specific order
    // -----------------------------------------------------------------------

    public function tracking(Request $request, int $orderId)
    {
        /** @var Users $user */
        $user = $request->user();
        if (!$user || (int) $user->role_type !== self::CUSTOMER_ROLE_TYPE) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        $customer = Customers::where('user_id', $user->user_id)->first();
        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Customer profile not found'], 404);
        }

        $order = Orders::with('orderItems')
            ->where('order_id', $orderId)
            ->where('customer_id', $customer->customer_id)
            ->first();

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        $trackings = OrderTracking::where('order_id', $orderId)
            ->orderByDesc('tracked_at')
            ->get()
            ->map(function (OrderTracking $tracking) {
                return [
                    'tracking_id' => $tracking->tracking_id,
                    // 'status'      => $tracking->status,
                    'status'      => Orders::statusLabel($tracking->status),
                    'raw_status' => $tracking->status,
                    'location'    => $tracking->location,
                    'description' => $tracking->description,
                    'tracked_at'  => $tracking->tracked_at
                        ? $tracking->tracked_at->toDateTimeString()
                        : null,
                ];
            })
            ->values();

        // Load existing reviews by this customer for products in this order
        $productIds = $order->orderItems->pluck('product_id')->filter()->unique()->values();
        $existingReviews = ProductReview::where('customer_id', $customer->customer_id)
            ->whereIn('product_id', $productIds)
            ->get()
            ->keyBy('product_id');

        $isDelivered = in_array((string) $order->order_status, ['delivered', 'completed'], true);

        $items = $order->orderItems->map(function (OrderItem $item) use ($existingReviews, $isDelivered) {
            $review = $existingReviews->get($item->product_id);
            return [
                'item_id'       => $item->item_id,
                'product_id'    => $item->product_id,
                'product_name'  => $item->product_name,
                'sku'           => $item->sku,
                'quantity'      => $item->quantity,
                'unit_price'    => number_format((float) $item->unit_price, 2, '.', ''),
                'line_total'    => number_format((float) $item->line_total, 2, '.', ''),
                'gst_amount'    => number_format((float) $item->gst_amount, 2, '.', ''),
                'gst_percentage'    => number_format((float) $item->gst_percentage, 2, '.', ''),
                'gst_calculation_type'    => $item->gst_calculation_type,
                'item_status'   => $item->item_status,
                'thumbnail_url' => $item->product && !empty($item->product->product_image)
                    ? url('public/uploads/products/' . $item->product->product_image)
                    : null,
                'can_review'    => $isDelivered && $review === null,
                'has_reviewed'  => $review !== null,
                'my_review'     => $review ? [
                    'review_id'    => $review->review_id,
                    'rating'       => (int) $review->rating,
                    'review'       => $review->review,
                    'submitted_at' => $review->updated_at ? $review->updated_at->toDateTimeString() : null,
                ] : null,
            ];
        })->values();

        return response()->json([
            'status'  => true,
            'message' => 'Order tracking retrieved successfully',
            'data'    => [
                'order_id'        => $order->order_id,
                'order_number'    => $order->order_number,
                // 'order_status'    => $order->order_status,
                // 'order_status_label'    => $this->humanizeStatus($order->order_status),
                'order_status' => Orders::statusLabel($order->order_status),
                'order_status_label' => Orders::statusLabel($order->order_status),
                'raw_order_status' => $order->order_status,
                'razorpay_order_id'    => $order->razorpay_order_id,
                'placed_at'       => $order->created_at ? $order->created_at->toDateTimeString() : null,
                'payment_status'  => $order->payment_status,
                'total_amount'    => number_format((float) $order->total_amount, 2, '.', ''),
                'items_count'     => $order->orderItems->count(),
                'shipping_details' => [
                    'name'            => $user->name,
                    'mobile'          => $user->mobile,
                    'shipping_address' => $order->shipping_address,
                ],
                'items'           => $items,
                'current_stage'   => $this->resolveCurrentStage($order->order_status),
                'status_flow'     => $this->buildStatusFlow($order),
                'tracking'        => $trackings,
            ],
        ], 200);
    }

    // -----------------------------------------------------------------------
    // POST /api/customer-app/orders/{orderId}/cancel
    // Cancel a pending order (only allowed before it is confirmed/shipped)
    // -----------------------------------------------------------------------
    public function cancel(Request $request, int $orderId)
    {
        /** @var Users $user */
        $user = $request->user();
        if (!$user || (int) $user->role_type !== self::CUSTOMER_ROLE_TYPE) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        $customer = Customers::where('user_id', $user->user_id)->first();
        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Customer profile not found'], 404);
        }

        $order = Orders::where('order_id', $orderId)
            ->where('customer_id', $customer->customer_id)
            ->first();

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        $cancellableStatuses = ['pending', 'payment_pending'];
        if (!in_array($order->order_status, $cancellableStatuses, true)) {
            return response()->json([
                'status'  => false,
                'message' => 'This order cannot be cancelled. Only pending orders can be cancelled.',
            ], 422);
        }

        $refundAmount = 0.0;
        DB::transaction(function () use ($order, &$refundAmount) {
            $order->order_status = 'cancelled';

            if ($order->payment_status === 'paid') {
                $refundAmount = (float) $order->total_amount;
                $order->payment_status = 'refunded';
            } else {
                $order->payment_status = 'cancelled';
            }

            $order->save();
            OrderItem::where('order_id', $order->order_id)->update(['item_status' => 'cancelled']);
        });

        NotificationController::pushOrderUpdate(
            (int) $customer->customer_id,
            (int) $order->order_id,
            'Order Cancelled',
            'Your order ' . $order->order_number . ' has been cancelled.',
            [
                'order_number' => $order->order_number,
                'order_status' => $order->order_status,
                'payment_status' => $order->payment_status,
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => 'Order cancelled successfully',
            'data'    => [
                'order_id'     => $order->order_id,
                'order_number' => $order->order_number,
                'order_status' => $order->order_status,
                'payment_status' => $order->payment_status,
                'can_cancel' => $this->canCancelOrder($order),
                'refund_amount' => number_format($refundAmount, 2, '.', ''),
            ],
        ], 200);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------
    private function formatOrderSummary(Orders $order): array
    {
        $itemCount = $order->orderItems->count();
        $firstItem = $order->orderItems->first();
        $normalizedStatus = $this->normalizeStatus((string) $order->order_status);
        $statusLabel = Orders::statusLabel($normalizedStatus);

        $firstItemImageUrl = null;
        if ($firstItem && $firstItem->product && !empty($firstItem->product->product_image)) {
            $firstItemImageUrl = url('public/uploads/products/' . $firstItem->product->product_image);
        }

        $productImages = $order->orderItems->map(function (OrderItem $item) {
            return [
                'product_id'        => $item->product_id,
                'product_name'      => $item->product_name,
                'product_image_url' => $item->product && !empty($item->product->product_image)
                    ? url('public/uploads/products/' . $item->product->product_image)
                    : null,
            ];
        })->values();

        return [
            'order_id'              => $order->order_id,
            'order_number'          => $order->order_number,
            // 'order_status'          => $normalizedStatus,
            // 'order_status_label'    => $this->humanizeStatus($normalizedStatus),
            'order_status'       => $statusLabel,
            'order_status_label' => $statusLabel,
            'payment_method'        => $order->payment_method,
            'payment_status'        => $order->payment_status,
            'can_cancel'            => $this->canCancelOrder($order),
            'total_amount'          => number_format((float) $order->total_amount, 2, '.', ''),
            'items_count'           => $itemCount,
            'first_item_name'       => $firstItem?->product_name,
            'first_item_image_url'  => $firstItemImageUrl,
            'products'              => $productImages,
            'placed_at'             => $order->created_at ? $order->created_at->toDateTimeString() : null,
        ];
    }

    private function canCancelOrder(Orders $order): bool
    {
        return in_array((string) $order->order_status, ['pending', 'payment_pending'], true);
    }

    private function buildStatusFlow(Orders $order): array
    {
        $currentStatus = $this->normalizeStatus((string) $order->order_status);

        $statusOrder = [
            self::STATUS_ORDER_PLACED,
            self::STATUS_ORDER_CONFIRMED,
            self::STATUS_PICKED_UP,
            self::STATUS_OUT_FOR_DELIVERY,
            self::STATUS_DELIVERED,
        ];

        $currentIndex = array_search($currentStatus, $statusOrder, true);
        if ($currentIndex === false) {
            $currentIndex = 0;
        }

        $history = OrderTracking::where('order_id', $order->order_id)
            ->orderBy('tracked_at')
            ->get();

        $historyByStatus = [];
        foreach ($history as $event) {
            $normalized = $this->normalizeStatus((string) $event->status);
            if (!isset($historyByStatus[$normalized])) {
                $historyByStatus[$normalized] = $event;
            }
        }

        $flow = [];
        foreach ($statusOrder as $index => $status) {
            $event = $historyByStatus[$status] ?? null;
            $flow[] = [
                'key' => $status,
                'label' => $this->humanizeStatus($status),
                'is_completed' => $currentStatus !== self::STATUS_CANCELLED && $index <= $currentIndex,
                'is_current' => $currentStatus !== self::STATUS_CANCELLED && $status === $this->resolveCurrentStage($currentStatus),
                'tracked_at' => $event?->tracked_at ? $event->tracked_at->toDateTimeString() : null,
                'description' => $event?->description,
                'location' => $event?->location,
            ];
        }

        $cancelEvent = $historyByStatus[self::STATUS_CANCELLED] ?? null;
        $flow[] = [
            'key' => self::STATUS_CANCELLED,
            'label' => $this->humanizeStatus(self::STATUS_CANCELLED),
            'is_completed' => $currentStatus === self::STATUS_CANCELLED,
            'is_current' => $currentStatus === self::STATUS_CANCELLED,
            'tracked_at' => $cancelEvent?->tracked_at ? $cancelEvent->tracked_at->toDateTimeString() : null,
            'description' => $cancelEvent?->description,
            'location' => $cancelEvent?->location,
        ];

        return $flow;
    }

    private function resolveCurrentStage(string $status): string
    {
        $status = $this->normalizeStatus($status);

        return match ($status) {
            self::STATUS_PICKED_UP => self::STATUS_PICKED_UP,
            self::STATUS_OUT_FOR_DELIVERY => self::STATUS_OUT_FOR_DELIVERY,
            self::STATUS_DELIVERED => self::STATUS_DELIVERED,
            self::STATUS_CANCELLED => self::STATUS_CANCELLED,
            self::STATUS_ORDER_PLACED => self::STATUS_ORDER_PLACED,
            default => self::STATUS_ORDER_CONFIRMED,
        };
    }

    private function normalizeStatus(string $status): string
    {
        $normalized = strtolower(trim($status));
        $normalized = str_replace(['-', ' '], '_', $normalized);

        return match ($normalized) {
            'pending', 'payment_pending' => self::STATUS_ORDER_PLACED,
            'confirmed', 'placed', 'order_placed' => self::STATUS_ORDER_CONFIRMED,
            'picked', 'pickup', 'picked_up' => self::STATUS_PICKED_UP,
            'out_for_delivery', 'out_for_delivery_', 'outfordelivery', 'shipped', 'in_transit' => self::STATUS_OUT_FOR_DELIVERY,
            'delivered', 'completed' => self::STATUS_DELIVERED,
            'cancelled', 'canceled' => self::STATUS_CANCELLED,
            default => $normalized,
        };
    }

    private function humanizeStatus(string $status): string
    {
        return match ($status) {
            self::STATUS_ORDER_PLACED => 'Order Placed',
            self::STATUS_ORDER_CONFIRMED => 'Confirmed',
            // self::STATUS_PICKED_UP => 'Picked Up',
            self::STATUS_PICKED_UP => 'Ready to Dispatch',
            self::STATUS_OUT_FOR_DELIVERY => 'Out for Delivery',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucwords(str_replace('_', ' ', $status)),
        };
    }

    public function downloadOrderInvoicePdf($id)
    {
        $order = Orders::with(['customer.user', 'orderItems'])->find($id);
        $storeSetting = StoreSetting::query()->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found.'
            ], 404);
        }

        $pdf = Pdf::loadView('admin.orders.orderInvoicePdf', [
            'order' => $order,
            'storeSetting' => $storeSetting,
        ])->setPaper('a4', 'portrait');

        $fileName = 'invoice-' . $order->order_number . '.pdf';

        return $pdf->download($fileName);
    }
}
