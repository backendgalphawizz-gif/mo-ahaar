<?php

namespace App\Services;

use App\Http\Controllers\API\DriverApp\DriverAppController;
use App\Http\Controllers\API\DriverApp\NotificationController;
use App\Models\DeliveryAssignment;
use App\Models\DeliveryAssignmentInvite;
use App\Models\DeliveryAssignmentRejection;
use App\Models\DriverProfile;
use App\Models\Orders;
use App\Models\OrderTracking;
use App\Models\Users;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DriverAssignmentService
{
    public const MODE_MANUAL = 'manual';

    public const MODE_BROADCAST = 'broadcast';

    public function ensureAssignment(Orders $order): DeliveryAssignment
    {
        $order->loadMissing(['vendor', 'customer.user']);

        $existing = DeliveryAssignment::where('order_id', $order->order_id)->first();
        if ($existing) {
            return $existing;
        }

        $assignment = DriverAppController::syncAssignmentFromOrder($order);

        if (!$assignment) {
            throw new \RuntimeException('Delivery assignments are not available.');
        }

        return $assignment;
    }

    /**
     * Notify nearby online drivers; first accept wins.
     */
    public function broadcastToNearbyDrivers(Orders $order): array
    {
        $assignment = $this->ensureAssignment($order);

        if ($assignment->driver_id !== null) {
            throw new \InvalidArgumentException('This order already has a delivery partner assigned.');
        }

        if (!in_array($assignment->status, [DeliveryAssignment::STATUS_NEW], true)) {
            throw new \InvalidArgumentException('This delivery is no longer open for broadcast.');
        }

        $driverIds = $this->findNearbyDriverIds($order);

        if ($driverIds->isEmpty()) {
            throw new \InvalidArgumentException('No nearby online drivers found. Ask drivers to go online and update location in the app.');
        }

        $notified = 0;

        DB::transaction(function () use ($assignment, $order, $driverIds, &$notified) {
            $assignment = DeliveryAssignment::where('assignment_id', $assignment->assignment_id)
                ->lockForUpdate()
                ->first();

            if ($assignment->driver_id !== null) {
                throw new \InvalidArgumentException('This order was just assigned to another driver.');
            }

            $assignment->assignment_mode = self::MODE_BROADCAST;
            $assignment->broadcast_at = now();
            $assignment->save();

            foreach ($driverIds as $driverId) {
                $invite = DeliveryAssignmentInvite::firstOrCreate(
                    [
                        'assignment_id' => $assignment->assignment_id,
                        'driver_id' => $driverId,
                    ],
                    [
                        'status' => DeliveryAssignmentInvite::STATUS_PENDING,
                        'notified_at' => now(),
                    ]
                );

                if ($invite->status !== DeliveryAssignmentInvite::STATUS_PENDING) {
                    continue;
                }

                if (!$invite->wasRecentlyCreated && $invite->notified_at) {
                    continue;
                }

                $invite->status = DeliveryAssignmentInvite::STATUS_PENDING;
                $invite->notified_at = now();
                $invite->save();

                NotificationController::notify(
                    (int) $driverId,
                    'New delivery request',
                    'Order ' . ($order->order_number ?? $order->order_id) . ' is available nearby. Accept before others do.',
                    'delivery_request_broadcast',
                    $assignment
                );

                $notified++;
            }
        });

        OrderTracking::create([
            'order_id' => $order->order_id,
            'status' => $order->order_status,
            'description' => 'Delivery request broadcast to ' . $notified . ' nearby driver(s).',
            'location' => 'Admin Panel',
            'tracked_at' => now(),
        ]);

        return [
            'assignment' => $assignment->fresh(),
            'drivers_notified' => $notified,
        ];
    }

    /**
     * Admin assigns a specific driver (closes broadcast pool).
     */
    public function assignManually(Orders $order, Users $driver): DeliveryAssignment
    {
        $assignment = $this->ensureAssignment($order);

        DB::transaction(function () use ($assignment, $order, $driver) {
            $assignment = DeliveryAssignment::where('assignment_id', $assignment->assignment_id)
                ->lockForUpdate()
                ->first();

            $assignment->driver_id = $driver->user_id;
            $assignment->status = DeliveryAssignment::STATUS_ASSIGNED;
            $assignment->assignment_mode = self::MODE_MANUAL;
            $assignment->assigned_at = now();
            $assignment->save();

            $this->expirePendingInvites($assignment, (int) $driver->user_id);

            NotificationController::notify(
                (int) $driver->user_id,
                'New Delivery Assigned',
                'You were assigned order ' . ($order->order_number ?? $order->order_id) . ' by admin.',
                'new_delivery_assigned',
                $assignment
            );
        });

        if (in_array($order->order_status, ['pending', 'accepted', 'confirmed', 'processing'], true)) {
            $order->update(['order_status' => 'processing']);
        }

        OrderTracking::create([
            'order_id' => $order->order_id,
            'status' => $order->order_status,
            'description' => 'Delivery partner ' . $driver->name . ' assigned by admin.',
            'location' => 'Admin Panel',
            'tracked_at' => now(),
        ]);

        return $assignment->fresh();
    }

    /**
     * Driver accepts — only one succeeds (row lock).
     */
    public function acceptByDriver(DeliveryAssignment $assignment, Users $driver): DeliveryAssignment
    {
        $assignment->loadMissing(['order']);

        if ($assignment->assignment_mode === self::MODE_BROADCAST) {
            $hasInvite = DeliveryAssignmentInvite::where('assignment_id', $assignment->assignment_id)
                ->where('driver_id', $driver->user_id)
                ->where('status', DeliveryAssignmentInvite::STATUS_PENDING)
                ->exists();

            if (!$hasInvite) {
                throw new \InvalidArgumentException('You were not invited to accept this delivery.');
            }
        }

        $otherNotifiedDriverIds = [];

        DB::transaction(function () use ($assignment, $driver, &$otherNotifiedDriverIds) {
            $assignment = DeliveryAssignment::where('assignment_id', $assignment->assignment_id)
                ->lockForUpdate()
                ->first();

            if ($assignment->status !== DeliveryAssignment::STATUS_NEW || $assignment->driver_id !== null) {
                throw new \InvalidArgumentException('This delivery is no longer available to accept.');
            }

            if ($assignment->assignment_mode === self::MODE_BROADCAST) {
                $invite = DeliveryAssignmentInvite::where('assignment_id', $assignment->assignment_id)
                    ->where('driver_id', $driver->user_id)
                    ->lockForUpdate()
                    ->first();

                if (!$invite || $invite->status !== DeliveryAssignmentInvite::STATUS_PENDING) {
                    throw new \InvalidArgumentException('This delivery is no longer available for you to accept.');
                }

                $invite->status = DeliveryAssignmentInvite::STATUS_ACCEPTED;
                $invite->save();

                $otherNotifiedDriverIds = DeliveryAssignmentInvite::where('assignment_id', $assignment->assignment_id)
                    ->where('driver_id', '!=', $driver->user_id)
                    ->where('status', DeliveryAssignmentInvite::STATUS_PENDING)
                    ->pluck('driver_id')
                    ->all();
            }

            $alreadyRejected = DeliveryAssignmentRejection::where('assignment_id', $assignment->assignment_id)
                ->where('driver_id', $driver->user_id)
                ->exists();

            if ($alreadyRejected) {
                throw new \InvalidArgumentException('You have already rejected this delivery.');
            }

            $assignment->driver_id = $driver->user_id;
            $assignment->status = DeliveryAssignment::STATUS_ASSIGNED;
            $assignment->assigned_at = now();
            $assignment->save();

            $this->expirePendingInvites($assignment, (int) $driver->user_id);

            NotificationController::notify(
                (int) $driver->user_id,
                'Delivery accepted',
                'You accepted order ' . ($assignment->order?->order_number ?? $assignment->order_id) . '.',
                'new_delivery_assigned',
                $assignment
            );
        });

        $orderNumber = $assignment->order?->order_number ?? $assignment->order_id;
        foreach ($otherNotifiedDriverIds as $otherDriverId) {
            NotificationController::notify(
                (int) $otherDriverId,
                'Delivery no longer available',
                'Order ' . $orderNumber . ' was accepted by another driver.',
                'delivery_request_taken',
                $assignment
            );
        }

        return $assignment->fresh(['order.customer.user']);
    }

    public function driverCanSeeOpenAssignment(DeliveryAssignment $assignment, int $driverId): bool
    {
        if ($assignment->status !== DeliveryAssignment::STATUS_NEW || $assignment->driver_id !== null) {
            return false;
        }

        if ($assignment->assignment_mode === self::MODE_BROADCAST) {
            return DeliveryAssignmentInvite::where('assignment_id', $assignment->assignment_id)
                ->where('driver_id', $driverId)
                ->where('status', DeliveryAssignmentInvite::STATUS_PENDING)
                ->exists();
        }

        return true;
    }

    /**
     * @return Collection<int, int>
     */
    public function findNearbyDriverIds(Orders $order): Collection
    {
        $order->loadMissing('vendor');
        $vendor = $order->vendor;
        $maxDrivers = max(1, (int) config('driver-app.broadcast_max_drivers', 25));
        $radiusKm = max(1.0, (float) config('driver-app.nearby_radius_km', 15));

        $baseQuery = Users::query()
            ->where('role_type', Users::DRIVER_APP_ROLE_TYPE)
            ->where('approval_status', 'approved')
            ->where('status', '1');

        if (
            Schema::hasTable('driver_profiles')
            && Schema::hasColumn('driver_profiles', 'is_online')
        ) {
            $baseQuery->whereHas('driverProfile', fn ($q) => $q->where('is_online', true));
        }

        $lat = $vendor?->latitude;
        $lng = $vendor?->longitude;

        if (
            $lat !== null && $lng !== null
            && Schema::hasColumn('driver_profiles', 'latitude')
            && Schema::hasColumn('driver_profiles', 'longitude')
        ) {
            $distanceExpression = '(6371 * acos(LEAST(1, GREATEST(-1, cos(radians(?)) * cos(radians(driver_profiles.latitude)) * cos(radians(driver_profiles.longitude) - radians(?)) + sin(radians(?)) * sin(radians(driver_profiles.latitude))))))';

            return $baseQuery
                ->join('driver_profiles', 'driver_profiles.driver_id', '=', 'users.user_id')
                ->whereNotNull('driver_profiles.latitude')
                ->whereNotNull('driver_profiles.longitude')
                ->select('users.user_id')
                ->selectRaw($distanceExpression . ' as distance_km', [$lat, $lng, $lat])
                ->havingRaw('distance_km <= ?', [$radiusKm])
                ->orderBy('distance_km')
                ->limit($maxDrivers)
                ->pluck('user_id');
        }

        if ($vendor?->city && Schema::hasColumn('driver_profiles', 'city')) {
            $cityMatches = (clone $baseQuery)
                ->whereHas('driverProfile', fn ($q) => $q->where('city', $vendor->city))
                ->orderBy('name')
                ->limit($maxDrivers)
                ->pluck('user_id');

            if ($cityMatches->isNotEmpty()) {
                return $cityMatches;
            }
        }

        return $baseQuery->orderBy('name')->limit($maxDrivers)->pluck('user_id');
    }

    private function expirePendingInvites(DeliveryAssignment $assignment, ?int $exceptDriverId = null): void
    {
        if (!Schema::hasTable('delivery_assignment_invites')) {
            return;
        }

        $query = DeliveryAssignmentInvite::where('assignment_id', $assignment->assignment_id)
            ->where('status', DeliveryAssignmentInvite::STATUS_PENDING);

        if ($exceptDriverId) {
            $query->where('driver_id', '!=', $exceptDriverId);
        }

        $query->update(['status' => DeliveryAssignmentInvite::STATUS_EXPIRED]);
    }
}
