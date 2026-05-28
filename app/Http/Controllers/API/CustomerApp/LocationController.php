<?php

namespace App\Http\Controllers\API\CustomerApp;

use App\Http\Controllers\Controller;
use App\Models\Customers;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LocationController extends Controller
{
    private const CUSTOMER_ROLE_TYPE = Users::CUSTOMER_APP_ROLE_TYPE;

    public function enable(Request $request)
    {
        /** @var Users|null $user */
        $user = $request->user();

        if (!$user || (int) $user->role_type !== self::CUSTOMER_ROLE_TYPE) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized customer access',
            ], 403);
        }

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $customer = Customers::firstOrNew(['user_id' => $user->user_id]);
        $customer->latitude = (float) $validated['latitude'];
        $customer->longitude = (float) $validated['longitude'];
        $customer->location_enabled = 1;
        $customer->location_updated_at = now();
        $customer->save();

        return response()->json([
            'status' => true,
            'message' => 'Location enabled successfully',
            'data' => [
                'latitude' => $customer->latitude,
                'longitude' => $customer->longitude,
                'location_enabled' => (bool) $customer->location_enabled,
                'location_updated_at' => $customer->location_updated_at,
            ],
        ], 200);
    }

    public function nearbyProducts(Request $request)
    {
        [$user, $customer] = $this->resolveCustomer($request);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized customer access',
            ], 403);
        }

        [$latitude, $longitude, $error] = $this->resolveCoordinates($request, $customer);
        if ($error) {
            return response()->json([
                'status' => false,
                'message' => $error,
            ], 422);
        }

        $radiusKm = (float) $request->input('radius_km', 10);
        if ($radiusKm <= 0) {
            $radiusKm = 10;
        }

        $distanceExpression = '(6371 * acos(cos(radians(?)) * cos(radians(v.latitude)) * cos(radians(v.longitude) - radians(?)) + sin(radians(?)) * sin(radians(v.latitude))))';

        $products = DB::table('products as p')
            ->join('vendors as v', 'v.vendor_id', '=', 'p.vendor_id')
            ->select(
                'p.product_id',
                'p.product_name',
                'p.short_description',
                'p.price',
                'p.sale_price',
                'p.discount',
                'p.product_image',
                'p.vendor_id',
                'v.business_name as vendor_name',
                'v.latitude as vendor_latitude',
                'v.longitude as vendor_longitude'
            )
            ->selectRaw($distanceExpression . ' as distance_km', [$latitude, $longitude, $latitude])
            ->whereNotNull('v.latitude')
            ->whereNotNull('v.longitude')
            ->when(Schema::hasColumn('vendors', 'status'), function ($query) {
                $query->where('v.status', '1');
            })
            ->when(Schema::hasColumn('products', 'is_active_status'), function ($query) {
                $query->whereIn('p.is_active_status', [1, '1']);
            })
            ->when(Schema::hasColumn('products', 'status'), function ($query) {
                $query->whereIn('p.status', [1, '1']);
            })
            ->havingRaw('distance_km <= ?', [$radiusKm])
            ->orderBy('distance_km')
            ->limit(150)
            ->get()
            ->map(function ($product) {
                return [
                    'product_id' => $product->product_id,
                    'product_name' => $product->product_name,
                    'short_description' => $product->short_description,
                    'price' => $product->price,
                    'sale_price' => $product->sale_price,
                    'discount' => $product->discount,
                    'vendor_id' => $product->vendor_id,
                    'vendor_name' => $product->vendor_name,
                    'distance_km' => round((float) $product->distance_km, 2),
                    'product_image_url' => !empty($product->product_image) ? url('public/uploads/products/' . $product->product_image) : null,
                ];
            })
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Nearby products retrieved successfully',
            'data' => [
                'search_center' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'radius_km' => $radiusKm,
                ],
                'products' => $products,
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

        $customer = Customers::firstOrCreate(['user_id' => $user->user_id]);

        return [$user, $customer];
    }

    private function resolveCoordinates(Request $request, ?Customers $customer): array
    {
        $latitude = $request->filled('latitude') ? (float) $request->input('latitude') : null;
        $longitude = $request->filled('longitude') ? (float) $request->input('longitude') : null;

        if ($latitude === null || $longitude === null) {
            $latitude = $customer?->latitude;
            $longitude = $customer?->longitude;
        }

        if ($latitude === null || $longitude === null) {
            return [null, null, 'Location is not enabled. Please call enable location API first or pass latitude/longitude.'];
        }

        return [(float) $latitude, (float) $longitude, null];
    }
}
