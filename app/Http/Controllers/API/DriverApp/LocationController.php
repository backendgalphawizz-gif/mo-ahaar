<?php

namespace App\Http\Controllers\API\DriverApp;

use App\Models\DriverProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class LocationController extends DriverAppController
{
    public function update(Request $request)
    {
        $driver = $this->resolveDriver($request);
        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized driver access',
            ], 403);
        }

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'is_online' => ['sometimes', 'boolean'],
        ]);

        if (!Schema::hasTable('driver_profiles')) {
            return response()->json([
                'status' => false,
                'message' => 'Driver profile is not available.',
            ], 422);
        }

        $profile = DriverProfile::firstOrCreate(['driver_id' => $driver->user_id]);

        $profile->latitude = (float) $validated['latitude'];
        $profile->longitude = (float) $validated['longitude'];
        $profile->last_location_at = now();

        if ($request->has('is_online') && Schema::hasColumn('driver_profiles', 'is_online')) {
            $profile->is_online = $request->boolean('is_online');
        }

        $profile->save();

        return response()->json([
            'status' => true,
            'message' => 'Location updated successfully',
            'data' => [
                'latitude' => $profile->latitude,
                'longitude' => $profile->longitude,
                'is_online' => (bool) $profile->is_online,
                'last_location_at' => $profile->last_location_at?->toIso8601String(),
            ],
        ], 200);
    }

    public function setOnlineStatus(Request $request)
    {
        $driver = $this->resolveDriver($request);
        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized driver access',
            ], 403);
        }

        $validated = $request->validate([
            'is_online' => ['required', 'boolean'],
        ]);

        if (!Schema::hasTable('driver_profiles') || !Schema::hasColumn('driver_profiles', 'is_online')) {
            return response()->json([
                'status' => false,
                'message' => 'Online status is not supported.',
            ], 422);
        }

        $profile = DriverProfile::firstOrCreate(['driver_id' => $driver->user_id]);
        $profile->is_online = (bool) $validated['is_online'];
        $profile->save();

        return response()->json([
            'status' => true,
            'message' => $validated['is_online'] ? 'You are now online for deliveries' : 'You are now offline',
            'data' => [
                'is_online' => (bool) $profile->is_online,
            ],
        ], 200);
    }
}
