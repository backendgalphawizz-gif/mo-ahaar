<?php

namespace App\Http\Controllers\API\CustomerApp;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\Customers;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    private const CUSTOMER_ROLE_TYPE = Users::CUSTOMER_APP_ROLE_TYPE;

    public function index(Request $request)
    {
        [$user, $customer, $errorResponse] = $this->resolveCustomer($request);

        if ($errorResponse !== null) {
            return $errorResponse;
        }

        return response()->json([
            'status' => true,
            'message' => 'Shipping addresses retrieved successfully',
            'data' => [
                'addresses' => $customer->addresses->map(fn (CustomerAddress $address) => $this->transformAddress($address))->values(),
                'default_address' => $customer->defaultAddress
                    ? $this->transformAddress($customer->defaultAddress)
                    : null,
            ],
        ]);
    }

    public function store(Request $request)
    {
        [$user, $customer, $errorResponse] = $this->resolveCustomer($request);

        if ($errorResponse !== null) {
            return $errorResponse;
        }

        $validated = $this->validateAddress($request);

        $address = DB::transaction(function () use ($customer, $user, $validated, $request) {
            $makeDefault = $request->has('is_default')
                ? $request->boolean('is_default')
                : !$customer->addresses()->exists();

            if ($makeDefault) {
                $customer->addresses()->update(['is_default' => false]);
            }

            $address = $customer->addresses()->create([
                'contact_name' => $validated['contact_name'] ?? $user->name,
                'mobile' => $validated['mobile'] ?? $user->mobile,
                'address_line' => $validated['address_line'],
                'landmark' => $validated['landmark'] ?? null,
                'city' => $validated['city'] ?? null,
                'state' => $validated['state'] ?? null,
                'country' => $validated['country'] ?? null,
                'pincode' => $validated['pincode'] ?? null,
                'address_type' => $validated['address_type'] ?? 'other',
                'is_default' => $makeDefault,
            ]);

            $customer->syncLegacyAddress($makeDefault ? $address : ($customer->defaultAddress ?: $address));

            return $address->fresh();
        });

        return response()->json([
            'status' => true,
            'message' => 'Shipping address created successfully',
            'data' => $this->transformAddress($address),
        ], 201);
    }

    public function show(Request $request, int $addressId)
    {
        [$user, $customer, $errorResponse] = $this->resolveCustomer($request);

        if ($errorResponse !== null) {
            return $errorResponse;
        }

        $address = $customer->addresses()->where('customer_address_id', $addressId)->first();
        if (!$address) {
            return response()->json([
                'status' => false,
                'message' => 'Shipping address not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Shipping address retrieved successfully',
            'data' => $this->transformAddress($address),
        ]);
    }

    public function update(Request $request, int $addressId)
    {
        [$user, $customer, $errorResponse] = $this->resolveCustomer($request);

        if ($errorResponse !== null) {
            return $errorResponse;
        }

        $address = $customer->addresses()->where('customer_address_id', $addressId)->first();
        if (!$address) {
            return response()->json([
                'status' => false,
                'message' => 'Shipping address not found',
            ], 404);
        }

        $validated = $this->validateAddress($request);

        $address = DB::transaction(function () use ($customer, $user, $validated, $address, $request) {
            $makeDefault = $request->has('is_default')
                ? $request->boolean('is_default')
                : (bool) $address->is_default;

            if ($makeDefault) {
                $customer->addresses()
                    ->where('customer_address_id', '!=', $address->customer_address_id)
                    ->update(['is_default' => false]);
            }

            $address->fill([
                'contact_name' => $validated['contact_name'] ?? $address->contact_name ?? $user->name,
                'mobile' => $validated['mobile'] ?? $address->mobile ?? $user->mobile,
                'address_line' => $validated['address_line'],
                'landmark' => array_key_exists('landmark', $validated)
                    ? $validated['landmark']
                    : $address->landmark,
                'city' => $validated['city'] ?? $address->city,
                'state' => $validated['state'] ?? $address->state,
                'country' => $validated['country'] ?? $address->country,
                'pincode' => $validated['pincode'] ?? $address->pincode,
                'address_type' => $validated['address_type'] ?? $address->address_type ?? 'other',
                'is_default' => $makeDefault,
            ]);
            $address->save();

            $defaultAddress = $customer->fresh()->defaultAddress ?: $address;
            $customer->syncLegacyAddress($defaultAddress);

            return $address->fresh();
        });

        return response()->json([
            'status' => true,
            'message' => 'Shipping address updated successfully',
            'data' => $this->transformAddress($address),
        ]);
    }

    public function destroy(Request $request, int $addressId)
    {
        [$user, $customer, $errorResponse] = $this->resolveCustomer($request);

        if ($errorResponse !== null) {
            return $errorResponse;
        }

        $address = $customer->addresses()->where('customer_address_id', $addressId)->first();
        if (!$address) {
            return response()->json([
                'status' => false,
                'message' => 'Shipping address not found',
            ], 404);
        }

        DB::transaction(function () use ($customer, $address) {
            $wasDefault = (bool) $address->is_default;
            $address->delete();

            if ($wasDefault) {
                $replacement = $customer->addresses()->orderBy('customer_address_id')->first();
                if ($replacement) {
                    $customer->addresses()
                        ->where('customer_address_id', $replacement->customer_address_id)
                        ->update(['is_default' => true]);
                    $replacement = $replacement->fresh();
                }
                $customer->syncLegacyAddress($replacement);
                return;
            }

            $customer->syncLegacyAddress($customer->defaultAddress);
        });

        return response()->json([
            'status' => true,
            'message' => 'Shipping address deleted successfully',
        ]);
    }

    private function resolveCustomer(Request $request): array
    {
        /** @var Users|null $user */
        $user = $request->user();

        if (!$user || (int) $user->role_type !== self::CUSTOMER_ROLE_TYPE) {
            return [null, null, response()->json([
                'status' => false,
                'message' => 'Unauthorized customer access',
            ], 403)];
        }

        $customer = Customers::with(['addresses' => function ($query) {
            $query->orderByDesc('is_default')->orderByDesc('updated_at')->orderByDesc('customer_address_id');
        }, 'defaultAddress'])->where('user_id', $user->user_id)->first();

        if (!$customer) {
            return [$user, null, response()->json([
                'status' => false,
                'message' => 'Customer profile not found',
            ], 404)];
        }

        return [$user, $customer, null];
    }

    private function validateAddress(Request $request): array
    {
        $this->prepareAddressRequest($request);

        return $request->validate([
            'contact_name' => ['nullable', 'string', 'max:120'],
            'full_name' => ['nullable', 'string', 'max:120'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'mobile_number' => ['nullable', 'string', 'max:20'],
            'address_line' => ['required', 'string', 'max:1000'],
            'house_no_building_name' => ['nullable', 'string', 'max:255'],
            'road_name_area_colony' => ['nullable', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'pincode' => ['nullable', 'string', 'max:20'],
            'address_type' => ['nullable', 'string', 'max:50'],
            'delivery_type' => ['nullable', 'string', 'max:50'],
            'is_default' => ['nullable', 'boolean'],
        ]);
    }

    private function prepareAddressRequest(Request $request): void
    {
        if ($request->filled('full_name') && !$request->filled('contact_name')) {
            $request->merge(['contact_name' => $request->input('full_name')]);
        }
        if ($request->filled('mobile_number') && !$request->filled('mobile')) {
            $request->merge(['mobile' => $request->input('mobile_number')]);
        }
        if ($request->filled('delivery_type') && !$request->filled('address_type')) {
            $request->merge(['address_type' => strtolower((string) $request->input('delivery_type'))]);
        }
        if (!$request->filled('address_line')) {
            $line1 = trim((string) $request->input('house_no_building_name', ''));
            $line2 = trim((string) $request->input('road_name_area_colony', ''));
            $combined = trim($line1 . ($line1 !== '' && $line2 !== '' ? ', ' : '') . $line2);
            if ($combined !== '') {
                $request->merge(['address_line' => $combined]);
            }
        }

        if ($request->has('is_default')) {
            $normalized = $this->normalizeBooleanInput($request->input('is_default'));
            if ($normalized !== null) {
                $request->merge(['is_default' => $normalized]);
            }
        }
    }

    private function normalizeBooleanInput(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (bool) $value;
        }

        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
            return false;
        }

        return null;
    }

    private function transformAddress(CustomerAddress $address): array
    {
        return [
            'customer_address_id' => $address->customer_address_id,
            'contact_name' => $address->contact_name,
            'mobile' => $address->mobile,
            'address_line' => $address->address_line,
            'landmark' => $address->landmark,
            'city' => $address->city,
            'state' => $address->state,
            'country' => $address->country,
            'pincode' => $address->pincode,
            'address_type' => $address->address_type,
            'is_default' => (bool) $address->is_default,
            'formatted_address' => $address->formattedAddress(),
            'created_at' => optional($address->created_at)?->toDateTimeString(),
            'updated_at' => optional($address->updated_at)?->toDateTimeString(),
        ];
    }
}