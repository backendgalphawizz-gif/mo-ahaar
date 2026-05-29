<?php

namespace App\Http\Controllers\API\DriverApp;

use App\Models\DeliveryAssignment;
use App\Models\DriverNotification;
use App\Models\Orders;
use App\Models\Users;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class NotificationController extends DriverAppController
{
    public function index(Request $request)
    {
        $driver = $this->resolveDriver($request);
        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized driver access',
            ], 403);
        }

        if (!Schema::hasTable('driver_notifications')) {
            return response()->json([
                'status' => true,
                'message' => 'Notifications retrieved successfully',
                'data' => [
                    'unread_count' => 0,
                    'groups' => [],
                    'notifications' => [],
                    'pagination' => $this->emptyPagination(),
                ],
            ], 200);
        }

        $validated = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'unread_only' => ['sometimes', 'boolean'],
            'grouped' => ['sometimes', 'boolean'],
        ]);

        $perPage = min(max((int) ($validated['per_page'] ?? 20), 1), 100);
        $grouped = $request->boolean('grouped', true);

        $query = $this->driverNotificationsQuery($driver)
            ->orderByDesc('notification_id');

        if ($request->boolean('unread_only')) {
            $query->where('is_read', false);
        }

        $paginated = $query->paginate($perPage);
        $driverId = (int) $driver->user_id;

        $unreadCount = $this->driverNotificationsQuery($driver)
            ->where('is_read', false)
            ->count();

        $items = collect($paginated->items())
            ->map(fn (DriverNotification $n) => $this->formatNotification($n));

        $data = [
            'unread_count' => $unreadCount,
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
            ],
        ];

        if ($grouped) {
            $data['groups'] = $this->groupNotifications($items);
            $data['notifications'] = $items->values();
        } else {
            $data['notifications'] = $items->values();
            $data['groups'] = [];
        }

        return response()->json([
            'status' => true,
            'message' => 'Notifications retrieved successfully',
            'data' => $data,
        ], 200);
    }

    public function markRead(Request $request, int $notificationId)
    {
        $driver = $this->resolveDriver($request);
        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized driver access',
            ], 403);
        }

        $notification = $this->findNotification($driver, $notificationId);
        if (!$notification) {
            return response()->json([
                'status' => false,
                'message' => 'Notification not found.',
            ], 404);
        }

        $notification->is_read = true;
        $notification->read_at = now();
        $notification->save();

        return response()->json([
            'status' => true,
            'message' => 'Notification marked as read successfully',
        ], 200);
    }

    public function markAllRead(Request $request)
    {
        $driver = $this->resolveDriver($request);
        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized driver access',
            ], 403);
        }

        if (!Schema::hasTable('driver_notifications')) {
            return response()->json([
                'status' => true,
                'message' => 'All notifications marked as read',
                'data' => ['updated_count' => 0],
            ], 200);
        }

        $updated = $this->driverNotificationsQuery($driver)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'status' => true,
            'message' => 'All notifications marked as read',
            'data' => [
                'updated_count' => $updated,
            ],
        ], 200);
    }

    public function destroy(Request $request, int $notificationId)
    {
        $driver = $this->resolveDriver($request);
        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized driver access',
            ], 403);
        }

        $notification = $this->findNotification($driver, $notificationId);
        if (!$notification) {
            return response()->json([
                'status' => false,
                'message' => 'Notification not found.',
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'status' => true,
            'message' => 'Notification deleted successfully',
        ], 200);
    }

    public function destroyAll(Request $request)
    {
        $driver = $this->resolveDriver($request);
        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized driver access',
            ], 403);
        }

        $validated = $request->validate([
            'only_read' => ['sometimes', 'boolean'],
        ]);

        $query = $this->driverNotificationsQuery($driver);

        if ($request->boolean('only_read')) {
            $query->where('is_read', true);
        }

        $deleted = $query->delete();

        return response()->json([
            'status' => true,
            'message' => 'All selected notifications have been deleted successfully',
            'data' => [
                'deleted_count' => $deleted,
            ],
        ], 200);
    }

    private function findNotification(Users $driver, int $notificationId): ?DriverNotification
    {
        return $this->driverNotificationsQuery($driver)
            ->where('notification_id', $notificationId)
            ->first();
    }

    private function driverNotificationsQuery(Users $driver)
    {
        $query = DriverNotification::query()
            ->where('driver_id', $driver->user_id);

        if ($driver->created_at) {
            $query->where('created_at', '>=', $driver->created_at);
        }

        return $query;
    }

    private function formatNotification(DriverNotification $notification): array
    {
        $orderDisplayId = null;
        $customerName = null;

        if ($notification->order_id) {
            $order = Orders::with('customer.user')->find($notification->order_id);
            $orderDisplayId = $order?->order_number ?? ('#ORD-' . $notification->order_id);
            $customerName = $order?->customer?->user?->name;
        }

        $createdAt = $notification->created_at;

        return [
            'notification_id' => $notification->notification_id,
            'title' => $notification->title,
            'description' => $notification->message,
            'message' => $notification->message,
            'type' => $notification->type,
            'icon' => $this->iconForType($notification->type),
            'order_id' => $notification->order_id,
            'assignment_id' => $notification->assignment_id,
            'order_display_id' => $orderDisplayId,
            'customer_name' => $customerName,
            'is_read' => (bool) $notification->is_read,
            'read_at' => $notification->read_at?->toDateTimeString(),
            'created_at' => $createdAt?->toIso8601String(),
            'time_ago' => $createdAt?->diffForHumans(),
            'created_at_formatted' => $createdAt?->format('d M Y, g:i A'),
        ];
    }

    /**
     * @return array{variant: string, name: string}
     */
    private function iconForType(string $type): array
    {
        return match ($type) {
            'order_delivered' => ['variant' => 'success', 'name' => 'check'],
            'order_cancelled' => ['variant' => 'danger', 'name' => 'close'],
            'new_delivery_assigned' => ['variant' => 'warning', 'name' => 'bag'],
            default => ['variant' => 'warning', 'name' => 'bag'],
        };
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @return list<array{label: string, notifications: array<int, array<string, mixed>>}>
     */
    private function groupNotifications(Collection $items): array
    {
        $grouped = [];

        foreach ($items as $item) {
            $createdAt = isset($item['created_at'])
                ? Carbon::parse($item['created_at'])
                : now();

            $label = $this->groupLabelForDate($createdAt);
            $grouped[$label][] = $item;
        }

        $result = [];
        foreach ($grouped as $label => $notifications) {
            $result[] = [
                'label' => $label,
                'notifications' => array_values($notifications),
            ];
        }

        return $result;
    }

    private function groupLabelForDate(Carbon $date): string
    {
        if ($date->isToday()) {
            return 'NEWEST';
        }

        if ($date->isYesterday()) {
            return 'YESTERDAY';
        }

        return strtoupper($date->format('d F Y'));
    }

    /**
     * @return array<string, int>
     */
    private function emptyPagination(): array
    {
        return [
            'current_page' => 1,
            'per_page' => 20,
            'total' => 0,
            'last_page' => 1,
        ];
    }

    public static function notify(
        int $driverId,
        string $title,
        string $message,
        string $type,
        ?DeliveryAssignment $assignment = null
    ): void {
        if (!Schema::hasTable('driver_notifications')) {
            return;
        }

        DriverNotification::create([
            'driver_id' => $driverId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'assignment_id' => $assignment?->assignment_id,
            'order_id' => $assignment?->order_id,
        ]);
    }
}
