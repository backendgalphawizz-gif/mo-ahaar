<?php

namespace App\Http\Controllers\API\DriverApp;

use App\Models\DeliveryAssignment;
use App\Models\OrderTracking;
use App\Models\Orders;
use App\Services\DriverWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class OrderController extends DriverAppController
{
    public function __construct(
        private readonly DriverWalletService $walletService
    ) {}
    public function index(Request $request)
    {
        $driver = $this->resolveDriver($request);
        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized driver access',
            ], 403);
        }

        $filterMap = DeliveryAssignment::myOrdersFilterMap();
        $allowedFilters = array_merge(array_keys($filterMap), ['all']);

        $validated = $request->validate([
            'status' => ['nullable', 'string', Rule::in($allowedFilters)],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $activeFilter = $validated['status'] ?? 'all';
        $perPage = max(1, min((int) ($validated['per_page'] ?? 10), 50));
        $driverId = (int) $driver->user_id;

        $query = $this->myOrdersBaseQuery($driverId)->orderByDesc('updated_at');

        if ($activeFilter !== 'all' && isset($filterMap[$activeFilter])) {
            $query->where('status', $filterMap[$activeFilter]);
        }

        if (!empty($validated['search'])) {
            $term = '%' . trim($validated['search']) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('store_name', 'like', $term)
                    ->orWhereHas('order', function ($orderQuery) use ($term) {
                        $orderQuery->where('order_number', 'like', $term)
                            ->orWhereHas('customer.user', function ($userQuery) use ($term) {
                                $userQuery->where('name', 'like', $term);
                            });
                    });
            });
        }

        $paginated = $query->paginate($perPage);

        $orders = collect($paginated->items())
            ->map(fn (DeliveryAssignment $assignment) => $this->formatMyOrderItem($assignment))
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Orders retrieved successfully',
            'data' => [
                'active_filter' => $activeFilter,
                'filters' => $this->myOrdersFilterCounts($driverId),
                'orders' => $orders,
                'pagination' => [
                    'current_page' => $paginated->currentPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                    'last_page' => $paginated->lastPage(),
                ],
            ],
        ], 200);
    }

    public function filters(Request $request)
    {
        $driver = $this->resolveDriver($request);
        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized driver access',
            ], 403);
        }

        return response()->json([
            'status' => true,
            'message' => 'Order filters retrieved successfully',
            'data' => [
                'filters' => $this->myOrdersFilterCounts((int) $driver->user_id),
            ],
        ], 200);
    }

    public function show(Request $request, int $assignmentId)
    {
        $driver = $this->resolveDriver($request);
        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized driver access',
            ], 403);
        }

        $assignment = $this->findDriverAssignment((int) $driver->user_id, $assignmentId);
        if (!$assignment) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        $order = $assignment->order;
        $detail = $this->formatMyOrderItem($assignment);

        $detail['items'] = $order?->orderItems?->map(function ($item) {
            return [
                'item_id' => $item->item_id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'quantity' => (int) $item->quantity,
                'line_total' => (float) $item->line_total,
            ];
        })->values() ?? [];

        $detail['payment'] = [
            'method' => $order?->payment_method,
            'status' => $order?->payment_status,
            'order_total' => $order ? (float) $order->total_amount : null,
        ];

        $detail['timeline'] = $order?->trackings?->map(function (OrderTracking $tracking) {
            return [
                'status' => $tracking->status,
                'description' => $tracking->description,
                'location' => $tracking->location,
                'tracked_at' => $tracking->tracked_at?->toIso8601String(),
                'tracked_at_formatted' => $tracking->tracked_at?->format('d M Y, g:i A'),
            ];
        })->values() ?? [];

        $detail['customer']['mobile'] = $order?->customer?->user?->mobile ?? null;

        return response()->json([
            'status' => true,
            'message' => 'Order retrieved successfully',
            'data' => [
                'order' => $detail,
            ],
        ], 200);
    }

    public function pickUp(Request $request, int $assignmentId)
    {
        return $this->transitionStatus(
            $request,
            $assignmentId,
            DeliveryAssignment::STATUS_ASSIGNED,
            DeliveryAssignment::STATUS_PICKED_UP,
            'Order marked as picked up',
            'Order cannot be marked as picked up',
            'picked_up'
        );
    }

    public function outForDelivery(Request $request, int $assignmentId)
    {
        return $this->transitionStatus(
            $request,
            $assignmentId,
            DeliveryAssignment::STATUS_PICKED_UP,
            DeliveryAssignment::STATUS_OUT_FOR_DELIVERY,
            'Order marked as out for delivery',
            'Order cannot be marked as out for delivery',
            'out_for_delivery'
        );
    }

    public function deliver(Request $request, int $assignmentId)
    {
        return $this->transitionStatus(
            $request,
            $assignmentId,
            DeliveryAssignment::STATUS_OUT_FOR_DELIVERY,
            DeliveryAssignment::STATUS_DELIVERED,
            'Order marked as delivered',
            'Order cannot be marked as delivered',
            'delivered',
            setCompletedAt: true
        );
    }

    private function transitionStatus(
        Request $request,
        int $assignmentId,
        string $requiredStatus,
        string $newStatus,
        string $successMessage,
        string $failureMessage,
        string $orderStatus,
        bool $setCompletedAt = false
    ) {
        $driver = $this->resolveDriver($request);
        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized driver access',
            ], 403);
        }

        $assignment = $this->findDriverAssignment((int) $driver->user_id, $assignmentId);
        if (!$assignment) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        if ($assignment->status !== $requiredStatus) {
            return response()->json([
                'status' => false,
                'message' => $failureMessage,
            ], 422);
        }

        DB::transaction(function () use ($assignment, $newStatus, $orderStatus, $setCompletedAt, $driver) {
            $assignment->status = $newStatus;
            if ($setCompletedAt) {
                $assignment->completed_at = now();
            }
            $assignment->save();

            $this->syncMainOrderStatus($assignment->order_id, $orderStatus);
            $this->recordOrderTracking($assignment->order_id, $orderStatus);

            if ($setCompletedAt) {
                $assignment->loadMissing(['order.customer.user']);
                $customerName = $assignment->order?->customer?->user?->name ?? 'customer';
                $this->walletService->creditForDelivery((int) $driver->user_id, $assignment);
                NotificationController::notify(
                    (int) $driver->user_id,
                    'Order Delivered',
                    'Delivered to ' . $customerName,
                    'order_delivered',
                    $assignment
                );
            }
        });

        $assignment->refresh()->load(['order.customer.user', 'order.orderItems']);

        return response()->json([
            'status' => true,
            'message' => $successMessage,
            'data' => [
                'order' => $this->formatMyOrderItem($assignment),
            ],
        ], 200);
    }

    private function syncMainOrderStatus(int $orderId, string $orderStatus): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Orders::where('order_id', $orderId)->update(['order_status' => $orderStatus]);
    }

    private function recordOrderTracking(int $orderId, string $status): void
    {
        if (!Schema::hasTable('order_trackings')) {
            return;
        }

        OrderTracking::create([
            'order_id' => $orderId,
            'status' => $status,
            'description' => 'Updated by delivery driver',
            'tracked_at' => now(),
        ]);
    }

}
