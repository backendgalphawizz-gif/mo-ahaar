<?php

namespace App\Http\Controllers\API\DriverApp;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAssignment;
use App\Models\Orders;
use App\Models\Users;
use App\Models\Vendor;
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
            return $existing;
        }

        $vendor = $order->vendor_id ? Vendor::find($order->vendor_id) : null;
        $pickupAddress = self::vendorAddressLine($vendor);
        $deliveryAddress = (string) ($order->shipping_address ?? '');

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

    protected function eligibleNewDeliveriesQuery(int $driverId)
    {
        $rejectedIds = DB::table('delivery_assignment_rejections')
            ->where('driver_id', $driverId)
            ->pluck('assignment_id');

        return DeliveryAssignment::query()
            ->with(['order.customer.user'])
            ->where('status', DeliveryAssignment::STATUS_NEW)
            ->whereNull('driver_id')
            ->when($rejectedIds->isNotEmpty(), fn ($q) => $q->whereNotIn('assignment_id', $rejectedIds))
            ->orderByDesc('assignment_id');
    }
}
