<?php

namespace App\Http\Controllers\API\DriverApp;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAssignment;
use App\Models\Orders;
use App\Models\Users;
use App\Models\Vendor;
use App\Services\OrderDispatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class DriverAppController extends Controller
{
    protected const DRIVER_ROLE_TYPE = 4;

    protected function resolveDriver(Request $request): ?Users
    {
        $user = $request->user();

        if (!$user instanceof Users || !$user->isDriverAppUser()) {
            return null;
        }

        return $user;
    }

    protected function driverProfilePayload(Users $user): array
    {
        $profileImageUrl = null;
        if (!empty($user->profile_image)) {
            $profileImageUrl = url('public/uploads/drivers/' . $user->profile_image);
        }

        return [
            'user_id' => $user->user_id,
            'name' => $user->name,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'profile_photo_url' => $profileImageUrl,
        ];
    }

    protected function maskMobile(?string $mobile): string
    {
        $digits = preg_replace('/\D/', '', (string) $mobile);
        if (strlen($digits) < 4) {
            return '+91 ****** ****';
        }

        $lastFour = substr($digits, -4);

        return '+91 ****** ' . $lastFour;
    }

    protected function formatAssignmentItem(DeliveryAssignment $assignment, ?Orders $order = null): array
    {
        $order = $order ?: $assignment->order;
        $customerName = $order?->customer?->user?->name ?? 'Customer';
        $orderNumber = $order?->order_number ?? ('#' . str_pad((string) $assignment->order_id, 6, '0', STR_PAD_LEFT));

        $storeImageUrl = null;
        if (!empty($assignment->store_image)) {
            $storeImageUrl = str_starts_with($assignment->store_image, 'http')
                ? $assignment->store_image
                : url('public/uploads/vendors/' . $assignment->store_image);
        }

        $createdAt = $assignment->created_at;

        return [
            'assignment_id' => $assignment->assignment_id,
            'order_id' => $assignment->order_id,
            'order_number' => $orderNumber,
            'status' => $assignment->status,
            'status_label' => DeliveryAssignment::statusLabel($assignment->status),
            'created_at' => $createdAt?->toIso8601String(),
            'created_at_formatted' => $createdAt?->format('d M Y, g:i A'),
            'store' => [
                'name' => $assignment->store_name ?? 'Store',
                'image_url' => $storeImageUrl,
                'location_summary' => $assignment->store_location_summary,
            ],
            'customer' => [
                'name' => $customerName,
                'id_label' => $orderNumber,
            ],
            'payout_amount' => (float) $assignment->payout_amount,
            'payout_formatted' => '₹' . number_format((float) $assignment->payout_amount, 0),
            'pickup' => [
                'label' => 'Pickup Location',
                'address' => $assignment->pickup_address ?? '',
            ],
            'delivery' => [
                'label' => 'Delivery Location',
                'address' => $assignment->delivery_address ?? ($order?->shipping_address ?? ''),
            ],
            'can_accept' => $assignment->status === DeliveryAssignment::STATUS_NEW && $assignment->driver_id === null,
            'can_reject' => in_array($assignment->status, [DeliveryAssignment::STATUS_NEW, DeliveryAssignment::STATUS_ASSIGNED], true),
        ];
    }

    /**
     * Build assignment row from an order (admin creates orders; driver pool is synced here).
     */
    public static function syncAssignmentFromOrder(Orders $order, float $payoutAmount = 0): ?DeliveryAssignment
    {
        if (!Schema::hasTable('delivery_assignments')) {
            return null;
        }

        $existing = DeliveryAssignment::where('order_id', $order->order_id)->first();
        if ($existing) {
            $formattedDelivery = OrderDispatchService::formatShippingAddress($order);
            if ($formattedDelivery !== '' && $existing->delivery_address !== $formattedDelivery) {
                $existing->delivery_address = $formattedDelivery;
                $existing->save();
            }

            return $existing;
        }

        $vendor = $order->vendor_id ? Vendor::find($order->vendor_id) : null;
        $pickupAddress = self::vendorAddressLine($vendor);
        $deliveryAddress = OrderDispatchService::formatShippingAddress($order);

        if ($payoutAmount <= 0) {
            $payoutAmount = max(50, round((float) $order->shipping_amount, 2));
            if ($payoutAmount <= 0) {
                $payoutAmount = 100;
            }
        }

        return DeliveryAssignment::create([
            'order_id' => $order->order_id,
            'status' => DeliveryAssignment::STATUS_NEW,
            'payout_amount' => $payoutAmount,
            'pickup_address' => $pickupAddress,
            'delivery_address' => $deliveryAddress,
            'store_name' => $vendor?->business_name ?? $vendor?->owner_name ?? 'Store',
            'store_image' => $vendor?->profile_image ?? null,
            'store_location_summary' => self::vendorLocationSummary($vendor),
        ]);
    }

    protected static function vendorAddressLine(?Vendor $vendor): string
    {
        if (!$vendor) {
            return '';
        }

        $parts = array_filter([
            $vendor->address ?? null,
            $vendor->city ?? null,
            $vendor->state ?? null,
            $vendor->pincode ?? null,
        ]);

        return implode(', ', $parts);
    }

    protected static function vendorLocationSummary(?Vendor $vendor): ?string
    {
        if (!$vendor) {
            return null;
        }

        return $vendor->city ?? $vendor->area ?? $vendor->address ?? null;
    }

    protected function eligibleNewDeliveriesQuery(int $driverId, ?float $driverLat = null, ?float $driverLng = null)
    {
        $rejectedIds = DB::table('delivery_assignment_rejections')
            ->where('driver_id', $driverId)
            ->pluck('assignment_id');

        $query = DeliveryAssignment::query()
            ->with(['order.customer.user', 'order.vendor'])
            ->where('status', DeliveryAssignment::STATUS_NEW)
            ->whereNull('driver_id')
            ->when($rejectedIds->isNotEmpty(), fn ($q) => $q->whereNotIn('assignment_id', $rejectedIds));

        OrderDispatchService::applyNearbyVendorFilter($query, $driverLat, $driverLng);

        return $query->orderByDesc('assignment_id');
    }

    protected function myOrdersBaseQuery(int $driverId)
    {
        return DeliveryAssignment::query()
            ->with(['order.customer.user', 'order.orderItems'])
            ->where('driver_id', $driverId)
            ->whereNotIn('status', [
                DeliveryAssignment::STATUS_NEW,
                DeliveryAssignment::STATUS_REJECTED_BY_DRIVER,
            ]);
    }

    /**
     * @return list<array{key: string, label: string, count: int}>
     */
    protected function myOrdersFilterCounts(int $driverId): array
    {
        $labels = [
            'accepted' => 'Accepted',
            'picked_up' => 'Picked Up',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
        ];

        $filters = [];
        foreach (DeliveryAssignment::myOrdersFilterMap() as $key => $dbStatus) {
            $count = DeliveryAssignment::query()
                ->where('driver_id', $driverId)
                ->where('status', $dbStatus)
                ->count();

            $filters[] = [
                'key' => $key,
                'label' => $labels[$key] ?? ucwords(str_replace('_', ' ', $key)),
                'count' => $count,
            ];
        }

        return $filters;
    }

    protected function formatMyOrderItem(DeliveryAssignment $assignment): array
    {
        $order = $assignment->order;
        $base = $this->formatAssignmentItem($assignment, $order);
        $status = $assignment->status;

        $orderNumber = $base['order_number'];
        $totalAmount = $order ? (float) $order->total_amount : (float) $assignment->payout_amount;

        return array_merge($base, [
            'display_id' => $orderNumber,
            'status_label' => DeliveryAssignment::statusLabel($status, true),
            'status_badge' => $this->statusBadgeFor($status),
            'total_amount' => $totalAmount,
            'currency' => 'INR',
            'amount_formatted' => '₹' . number_format((float) $assignment->payout_amount, 0),
            'store' => array_merge($base['store'], [
                'branch' => $assignment->store_location_summary,
            ]),
            'can_mark_picked_up' => $status === DeliveryAssignment::STATUS_ASSIGNED,
            'can_mark_out_for_delivery' => $status === DeliveryAssignment::STATUS_PICKED_UP,
            'can_mark_delivered' => $status === DeliveryAssignment::STATUS_OUT_FOR_DELIVERY,
            'can_accept' => false,
            'can_reject' => in_array($status, [
                DeliveryAssignment::STATUS_ASSIGNED,
                DeliveryAssignment::STATUS_PICKED_UP,
                DeliveryAssignment::STATUS_OUT_FOR_DELIVERY,
            ], true),
        ]);
    }

    /**
     * @return array{text: string, color: string}
     */
    protected function statusBadgeFor(string $status): array
    {
        $color = match ($status) {
            DeliveryAssignment::STATUS_ASSIGNED => 'blue',
            DeliveryAssignment::STATUS_PICKED_UP => 'purple',
            DeliveryAssignment::STATUS_OUT_FOR_DELIVERY => 'orange',
            DeliveryAssignment::STATUS_DELIVERED => 'green',
            DeliveryAssignment::STATUS_CANCELLED => 'red',
            default => 'gray',
        };

        return [
            'text' => DeliveryAssignment::statusLabel($status, true),
            'color' => $color,
        ];
    }

    protected function findDriverAssignment(int $driverId, int $assignmentId): ?DeliveryAssignment
    {
        return DeliveryAssignment::with(['order.customer.user', 'order.orderItems', 'order.trackings'])
            ->where('assignment_id', $assignmentId)
            ->where('driver_id', $driverId)
            ->first();
    }
}
