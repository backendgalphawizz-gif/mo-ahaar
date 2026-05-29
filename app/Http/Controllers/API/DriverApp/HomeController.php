<?php

namespace App\Http\Controllers\API\DriverApp;

use App\Models\DeliveryAssignment;
use App\Models\DriverNotification;
use App\Models\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class HomeController extends DriverAppController
{
    public function dashboard(Request $request)
    {
        $driver = $this->resolveDriver($request);
        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized driver access',
            ], 403);
        }

        $this->syncReadyOrdersToAssignments();

        $driverId = (int) $driver->user_id;

        $assignedCount = DeliveryAssignment::where('driver_id', $driverId)
            ->whereIn('status', [
                DeliveryAssignment::STATUS_ASSIGNED,
                DeliveryAssignment::STATUS_PICKED_UP,
            ])
            ->count();

        $pendingCount = DeliveryAssignment::where('driver_id', $driverId)
            ->where('status', DeliveryAssignment::STATUS_ASSIGNED)
            ->count();

        $completedCount = DeliveryAssignment::where('driver_id', $driverId)
            ->where('status', DeliveryAssignment::STATUS_DELIVERED)
            ->count();

        $cancelledCount = DeliveryAssignment::where('driver_id', $driverId)
            ->whereIn('status', [
                DeliveryAssignment::STATUS_CANCELLED,
                DeliveryAssignment::STATUS_REJECTED_BY_DRIVER,
            ])
            ->count();

        $totalEarnings = (float) DeliveryAssignment::where('driver_id', $driverId)
            ->where('status', DeliveryAssignment::STATUS_DELIVERED)
            ->sum('payout_amount');

        $unreadNotifications = 0;
        if (Schema::hasTable('driver_notifications')) {
            $unreadNotifications = DriverNotification::where('driver_id', $driverId)
                ->where('is_read', false)
                ->count();
        }

        return response()->json([
            'status' => true,
            'message' => 'Dashboard loaded successfully',
            'data' => [
                'total_earnings' => $totalEarnings,
                'currency' => 'INR',
                'stats' => [
                    'assigned_deliveries' => $assignedCount,
                    'pending_deliveries' => $pendingCount,
                    'completed_deliveries' => $completedCount,
                    'cancelled_deliveries' => $cancelledCount,
                ],
                'unread_notifications_count' => $unreadNotifications,
            ],
        ], 200);
    }

    public function newDeliveries(Request $request)
    {
        $driver = $this->resolveDriver($request);
        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized driver access',
            ], 403);
        }

        $this->syncReadyOrdersToAssignments();

        $validated = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));

        $paginated = $this->eligibleNewDeliveriesQuery((int) $driver->user_id)
            ->paginate($perPage);

        $items = collect($paginated->items())
            ->map(fn (DeliveryAssignment $assignment) => $this->formatAssignmentItem($assignment))
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Available deliveries retrieved successfully',
            'data' => [
                'deliveries' => $items,
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
     * Expose orders in dispatch-ready states as open delivery assignments.
     */
    private function syncReadyOrdersToAssignments(): void
    {
        if (!Schema::hasTable('delivery_assignments') || !Schema::hasTable('orders')) {
            return;
        }

        $statuses = ['processing', 'shipped', 'out_for_delivery'];

        Orders::query()
            ->with(['vendor', 'customer.user'])
            ->whereIn('order_status', $statuses)
            ->orderByDesc('order_id')
            ->limit(50)
            ->get()
            ->each(fn (Orders $order) => self::syncAssignmentFromOrder($order));
    }
}
