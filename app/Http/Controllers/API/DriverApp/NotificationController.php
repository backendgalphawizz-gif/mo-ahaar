<?php

namespace App\Http\Controllers\API\DriverApp;

use App\Models\DriverNotification;
use Illuminate\Http\Request;
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
                    'notifications' => [],
                    'unread_count' => 0,
                ],
            ], 200);
        }

        $perPage = min(max((int) $request->input('per_page', 20), 1), 100);

        $query = DriverNotification::query()
            ->where('driver_id', $driver->user_id)
            ->orderByDesc('notification_id');

        if ($request->boolean('unread_only')) {
            $query->where('is_read', false);
        }

        $paginated = $query->paginate($perPage);

        $items = collect($paginated->items())->map(function (DriverNotification $notification) {
            return [
                'notification_id' => $notification->notification_id,
                'title' => $notification->title,
                'message' => $notification->message,
                'type' => $notification->type,
                'assignment_id' => $notification->assignment_id,
                'order_id' => $notification->order_id,
                'is_read' => (bool) $notification->is_read,
                'read_at' => $notification->read_at?->toDateTimeString(),
                'created_at' => $notification->created_at?->toDateTimeString(),
            ];
        })->values();

        $unreadCount = DriverNotification::where('driver_id', $driver->user_id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'status' => true,
            'message' => 'Notifications retrieved successfully',
            'data' => [
                'notifications' => $items,
                'unread_count' => $unreadCount,
                'pagination' => [
                    'current_page' => $paginated->currentPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                    'last_page' => $paginated->lastPage(),
                ],
            ],
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

        $notification = DriverNotification::where('notification_id', $notificationId)
            ->where('driver_id', $driver->user_id)
            ->first();

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
            'message' => 'Notification marked as read',
        ], 200);
    }
}
