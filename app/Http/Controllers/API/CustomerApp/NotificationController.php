<?php

namespace App\Http\Controllers\API\CustomerApp;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\CustomerNotification;
use App\Models\Customers;
use App\Models\OrderTracking;
use App\Models\Users;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private const CUSTOMER_ROLE_TYPE = Users::CUSTOMER_APP_ROLE_TYPE;

    /**
     * GET /api/customer-app/notifications
     * Customer notifications list (order updates + promotional offers)
     */
    public function index(Request $request)
    {
        [$user, $customer] = $this->resolveCustomer($request);
        if (!$user || !$customer) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized customer access',
            ], 403);
        }

        $this->syncPromotionalNotifications($customer);
        $this->syncOrderTrackingNotifications($customer);

        $perPage = (int) $request->input('per_page', 20);
        if ($perPage <= 0) {
            $perPage = 20;
        }
        $perPage = min($perPage, 100);

        $query = $this->customerNotificationsQuery($customer)
            ->orderByDesc('notification_id');

        if ($request->filled('type')) {
            $type = trim((string) $request->input('type'));
            if (in_array($type, ['order_update', 'promotional_offer'], true)) {
                $query->where('source_type', $type);
            }
        }

        if ($request->filled('is_read')) {
            $isRead = filter_var($request->input('is_read'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isRead !== null) {
                $query->where('is_read', $isRead);
            }
        }

        $paginated = $query->paginate($perPage);

        $items = collect($paginated->items())->map(function (CustomerNotification $notification) {
            return [
                'notification_id' => $notification->notification_id,
                'type' => $notification->source_type,
                'order_id' => $notification->order_id,
                'title' => $notification->title,
                'message' => $notification->message,
                'meta' => $notification->meta,
                'is_read' => (bool) $notification->is_read,
                'read_at' => $notification->read_at ? $notification->read_at->toDateTimeString() : null,
                'created_at' => $notification->created_at ? $notification->created_at->toDateTimeString() : null,
            ];
        })->values();

        return response()->json([
            'status' => true,
            'message' => 'Notifications retrieved successfully',
            'data' => [
                'notifications' => $items,
                'unread_count' => $this->customerNotificationsQuery($customer)->where('is_read', false)->count(),
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
     * GET /api/customer-app/notifications/unread-count
     */
    public function unreadCount(Request $request)
    {
        [, $customer] = $this->resolveCustomer($request);
        if (!$customer) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized customer access',
            ], 403);
        }

        $this->syncPromotionalNotifications($customer);
        $this->syncOrderTrackingNotifications($customer);

        return response()->json([
            'status' => true,
            'message' => 'Unread count retrieved successfully',
            'data' => [
                'unread_count' => $this->customerNotificationsQuery($customer)
                    ->where('is_read', false)
                    ->count(),
            ],
        ], 200);
    }

    /**
     * POST /api/customer-app/notifications/{notificationId}/read
     */
    public function markRead(Request $request, int $notificationId)
    {
        [, $customer] = $this->resolveCustomer($request);
        if (!$customer) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized customer access',
            ], 403);
        }

        $notification = $this->customerNotificationsQuery($customer)
            ->where('notification_id', $notificationId)
            ->first();

        if (!$notification) {
            return response()->json([
                'status' => false,
                'message' => 'Notification not found',
            ], 404);
        }

        if (!$notification->is_read) {
            $notification->is_read = true;
            $notification->read_at = now();
            $notification->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'Notification marked as read',
        ], 200);
    }

    /**
     * POST /api/customer-app/notifications/read-all
     */
    public function markAllRead(Request $request)
    {
        [, $customer] = $this->resolveCustomer($request);
        if (!$customer) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized customer access',
            ], 403);
        }

        $query = $this->customerNotificationsQuery($customer)
            ->where('is_read', false);

        if ($request->filled('type')) {
            $type = trim((string) $request->input('type'));
            if (in_array($type, ['order_update', 'promotional_offer'], true)) {
                $query->where('source_type', $type);
            }
        }

        $updated = $query->update([
            'is_read' => true,
            'read_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Notifications marked as read',
            'data' => [
                'updated_count' => $updated,
            ],
        ], 200);
    }

    private function resolveCustomer(Request $request): array
    {
        /** @var Users|null $user */
        $user = $request->user();

        if (!$user || (int) $user->role_type !== self::CUSTOMER_ROLE_TYPE) {
            return [null, null];
        }

        $customer = Customers::with('user')->where('user_id', $user->user_id)->first();

        return [$user, $customer];
    }

    private function customerNotificationsQuery(Customers $customer)
    {
        $query = CustomerNotification::query()
            ->where('customer_id', $customer->customer_id);

        $registeredAt = $customer->registeredAt();
        if ($registeredAt) {
            $query->where('created_at', '>=', $registeredAt);
        }

        return $query;
    }

    private function purgePreRegistrationNotifications(Customers $customer): void
    {
        $registeredAt = $customer->registeredAt();
        if (!$registeredAt) {
            return;
        }

        CustomerNotification::query()
            ->where('customer_id', $customer->customer_id)
            ->where('created_at', '<', $registeredAt)
            ->delete();
    }

    private function syncPromotionalNotifications(Customers $customer): void
    {
        $this->purgePreRegistrationNotifications($customer);

        $userId = $customer->user_id;
        $registeredAt = $customer->registeredAt();

        $adminNotifications = AdminNotification::query()
            ->where('target_type', 'users')
            ->when($registeredAt, fn ($query) => $query->where('created_at', '>=', $registeredAt))
            ->where(function ($query) use ($userId) {
                $query->where('recipient_scope', 'all')
                    ->orWhere(function ($subQuery) use ($userId) {
                        $subQuery->where('recipient_scope', 'specific')
                            ->where('recipient_id', $userId);
                    });
            })
            ->orderByDesc('id')
            ->get(['id', 'title', 'message', 'recipient_scope', 'recipient_id', 'created_at']);

        foreach ($adminNotifications as $adminNotification) {
            CustomerNotification::firstOrCreate(
                [
                    'customer_id' => $customer->customer_id,
                    'source_type' => 'promotional_offer',
                    'source_id' => $adminNotification->id,
                ],
                [
                    'title' => $adminNotification->title,
                    'message' => $adminNotification->message,
                    'meta' => [
                        'recipient_scope' => $adminNotification->recipient_scope,
                        'recipient_id' => $adminNotification->recipient_id,
                    ],
                    'created_at' => $adminNotification->created_at,
                ]
            );
        }
    }

    private function syncOrderTrackingNotifications(Customers $customer): void
    {
        $registeredAt = $customer->registeredAt();

        $trackings = OrderTracking::query()
            ->join('orders as o', 'o.order_id', '=', 'order_trackings.order_id')
            ->where('o.customer_id', $customer->customer_id)
            ->when($registeredAt, function ($query) use ($registeredAt) {
                $query->where(function ($subQuery) use ($registeredAt) {
                    $subQuery->where('order_trackings.tracked_at', '>=', $registeredAt)
                        ->orWhere(function ($fallback) use ($registeredAt) {
                            $fallback->whereNull('order_trackings.tracked_at')
                                ->where('o.created_at', '>=', $registeredAt);
                        });
                });
            })
            ->whereIn('order_trackings.status', ['picked_up', 'out_for_delivery', 'delivered', 'cancelled'])
            ->select(
                'order_trackings.tracking_id',
                'order_trackings.order_id',
                'order_trackings.status',
                'order_trackings.location',
                'order_trackings.description',
                'order_trackings.tracked_at',
                'o.order_number'
            )
            ->orderByDesc('order_trackings.tracking_id')
            ->get();

        foreach ($trackings as $tracking) {
            [$title, $message] = $this->buildOrderUpdateMessage(
                (string) $tracking->status,
                (string) $tracking->order_number
            );

            CustomerNotification::firstOrCreate(
                [
                    'customer_id' => $customer->customer_id,
                    'source_type' => 'order_update',
                    'source_id' => $tracking->tracking_id,
                ],
                [
                    'order_id' => $tracking->order_id,
                    'title' => $title,
                    'message' => $message,
                    'meta' => [
                        'status' => $tracking->status,
                        'location' => $tracking->location,
                        'description' => $tracking->description,
                        'tracked_at' => $tracking->tracked_at,
                    ],
                    'created_at' => $tracking->tracked_at ?? now(),
                ]
            );
        }
    }

    private function buildOrderUpdateMessage(string $status, string $orderNumber): array
    {
        return match ($status) {
            'picked_up' => [
                'Order Picked Up',
                'Your order ' . $orderNumber . ' has been picked up.',
            ],
            'out_for_delivery' => [
                'Out for Delivery',
                'Your order ' . $orderNumber . ' is out for delivery.',
            ],
            'delivered' => [
                'Order Delivered',
                'Your order ' . $orderNumber . ' has been delivered.',
            ],
            'cancelled' => [
                'Order Cancelled',
                'Your order ' . $orderNumber . ' has been cancelled.',
            ],
            default => [
                'Order Update',
                'Your order ' . $orderNumber . ' has a new update.',
            ],
        };
    }

    public static function pushOrderUpdate(
        int $customerId,
        int $orderId,
        string $title,
        string $message,
        array $meta = []
    ): void {
        CustomerNotification::create([
            'customer_id' => $customerId,
            'source_type' => 'order_update',
            'source_id' => null,
            'order_id' => $orderId,
            'title' => $title,
            'message' => $message,
            'meta' => $meta,
            'is_read' => false,
        ]);
    }
}
