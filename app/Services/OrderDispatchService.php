<?php

namespace App\Services;

use App\Http\Controllers\API\DriverApp\DriverAppController;
use App\Http\Controllers\API\DriverApp\NotificationController as DriverNotificationController;
use App\Models\DeliveryAssignment;
use App\Models\Orders;
use App\Models\Users;
use App\Models\Vendor;
use App\Models\VendorNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class OrderDispatchService
{
    /**
     * After a customer/admin places an order: notify vendor, open driver pool, ping nearby drivers.
     */
    public function dispatchAfterOrderPlaced(Orders $order): ?DeliveryAssignment
    {
        if (!$this->shouldDispatch($order)) {
            return null;
        }

        $order->loadMissing(['vendor', 'customer.user']);

        $assignment = DriverAppController::syncAssignmentFromOrder($order);
        if (!$assignment || $assignment->driver_id !== null) {
            return $assignment;
        }

        $this->notifyVendor($order);
        $this->notifyNearbyDrivers($order, $assignment);

        return $assignment;
    }

    /**
     * Order statuses that appear in the driver "new deliveries" pool.
     *
     * @return list<string>
     */
    public static function poolOrderStatuses(): array
    {
        return [
            'pending',
            'payment_pending',
            'confirmed',
            'placed',
            'order_placed',
            'accepted',
            'processing',
            'shipped',
            'out_for_delivery',
        ];
    }

    /**
     * Approved active drivers eligible for pool notifications.
     */
    public function eligibleDriverIds(?float $pickupLat, ?float $pickupLng): Collection
    {
        $drivers = Users::query()
            ->where('role_type', Users::DRIVER_APP_ROLE_TYPE)
            ->where('approval_status', 'approved')
            ->where('status', (string) Users::STATUS_ACTIVE)
            ->get(['user_id']);

        if ($pickupLat === null || $pickupLng === null) {
            return $drivers->pluck('user_id');
        }

        $radiusKm = (float) config('driver.dispatch_radius_km', 15);

        return $drivers
            ->filter(function (Users $driver) use ($pickupLat, $pickupLng, $radiusKm) {
                $coords = $this->resolveDriverCoordinates($driver);
                if ($coords === null) {
                    return true;
                }

                return self::distanceKm($pickupLat, $pickupLng, $coords['lat'], $coords['lng']) <= $radiusKm;
            })
            ->pluck('user_id')
            ->values();
    }

    public static function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    public static function formatShippingAddress(Orders $order): string
    {
        $raw = $order->shipping_address;
        if ($raw === null || $raw === '') {
            return '';
        }

        if (is_array($raw)) {
            return self::formatAddressArray($raw);
        }

        $decoded = json_decode((string) $raw, true);
        if (is_array($decoded)) {
            return self::formatAddressArray($decoded);
        }

        return trim((string) $raw);
    }

    /**
     * @param  array<string, mixed>  $address
     */
    public static function formatAddressArray(array $address): string
    {
        if (!empty($address['formatted_address'])) {
            return trim((string) $address['formatted_address']);
        }

        $parts = array_filter([
            $address['address_line'] ?? $address['line1'] ?? $address['address'] ?? null,
            $address['landmark'] ?? null,
            $address['city'] ?? null,
            $address['state'] ?? null,
            $address['pincode'] ?? $address['zip'] ?? null,
        ]);

        return trim(implode(', ', $parts));
    }

    /**
     * @return array{lat: float, lng: float}|null
     */
    public static function shippingCoordinates(Orders $order): ?array
    {
        $raw = $order->shipping_address;
        if ($raw === null || $raw === '') {
            return null;
        }

        $decoded = is_array($raw) ? $raw : json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            return null;
        }

        $lat = $decoded['latitude'] ?? $decoded['lat'] ?? null;
        $lng = $decoded['longitude'] ?? $decoded['lng'] ?? $decoded['lon'] ?? null;

        if ($lat === null || $lng === null) {
            return null;
        }

        return ['lat' => (float) $lat, 'lng' => (float) $lng];
    }

    /**
     * @return array{lat: float, lng: float}|null
     */
    public static function vendorCoordinates(?Vendor $vendor): ?array
    {
        if (!$vendor || $vendor->latitude === null || $vendor->longitude === null) {
            return null;
        }

        return ['lat' => (float) $vendor->latitude, 'lng' => (float) $vendor->longitude];
    }

    public static function applyNearbyVendorFilter($query, ?float $driverLat, ?float $driverLng): void
    {
        if ($driverLat === null || $driverLng === null) {
            return;
        }

        $radiusKm = (float) config('driver.dispatch_radius_km', 15);
        $driverLat = (float) $driverLat;
        $driverLng = (float) $driverLng;

        $query->where(function ($outer) use ($driverLat, $driverLng, $radiusKm) {
            $outer->whereHas('order.vendor', function ($vendorQuery) use ($driverLat, $driverLng, $radiusKm) {
                $vendorQuery
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->whereRaw(
                        '(6371 * acos(least(1, greatest(-1,
                            cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?))
                            + sin(radians(?)) * sin(radians(latitude))
                        )))) <= ?',
                        [$driverLat, $driverLng, $driverLat, $radiusKm]
                    );
            })->orWhereHas('order.vendor', function ($vendorQuery) {
                $vendorQuery->where(function ($q) {
                    $q->whereNull('latitude')->orWhereNull('longitude');
                });
            });
        });
    }

    private function shouldDispatch(Orders $order): bool
    {
        if (!Schema::hasTable('delivery_assignments') || !Schema::hasTable('orders')) {
            return false;
        }

        if (in_array($order->order_status, ['cancelled', 'rejected', 'delivered', 'completed', 'success'], true)) {
            return false;
        }

        return true;
    }

    private function notifyVendor(Orders $order): void
    {
        if (!Schema::hasTable('vendor_notifications') || !$order->vendor_id) {
            return;
        }

        $orderNumber = $order->order_number ?? ('#' . $order->order_id);
        $customerName = $order->customer?->user?->name ?? 'Customer';
        $amount = number_format((float) $order->total_amount, 2);

        VendorNotification::firstOrCreate(
            [
                'vendor_id' => (int) $order->vendor_id,
                'order_id' => (int) $order->order_id,
                'type' => VendorNotification::TYPE_NEW_ORDER,
            ],
            [
                'title' => 'New Order Received',
                'message' => sprintf(
                    '%s placed order %s (₹%s).',
                    $customerName,
                    $orderNumber,
                    $amount
                ),
                'is_read' => false,
            ]
        );
    }

    private function notifyNearbyDrivers(Orders $order, DeliveryAssignment $assignment): void
    {
        if (!Schema::hasTable('driver_notifications')) {
            return;
        }

        $vendor = $order->vendor;
        $pickup = self::vendorCoordinates($vendor) ?? self::shippingCoordinates($order);
        $pickupLat = $pickup['lat'] ?? null;
        $pickupLng = $pickup['lng'] ?? null;

        $driverIds = $this->eligibleDriverIds($pickupLat, $pickupLng);
        $orderNumber = $order->order_number ?? ('#' . $order->order_id);
        $storeName = $vendor?->business_name ?? $vendor?->owner_name ?? 'Store';

        foreach ($driverIds as $driverId) {
            DriverNotificationController::notify(
                (int) $driverId,
                'New Delivery Available',
                'New order ' . $orderNumber . ' from ' . $storeName . ' is available near you.',
                'new_delivery_available',
                $assignment
            );
        }
    }

    /**
     * @return array{lat: float, lng: float}|null
     */
    private function resolveDriverCoordinates(Users $driver): ?array
    {
        $profile = $driver->driverProfile;
        if ($profile && isset($profile->latitude, $profile->longitude)
            && $profile->latitude !== null && $profile->longitude !== null) {
            return ['lat' => (float) $profile->latitude, 'lng' => (float) $profile->longitude];
        }

        if (isset($driver->latitude, $driver->longitude)
            && $driver->latitude !== null && $driver->longitude !== null) {
            return ['lat' => (float) $driver->latitude, 'lng' => (float) $driver->longitude];
        }

        return null;
    }
}
