<?php

namespace App\Http\Controllers\API\CustomerApp;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\Customers;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    private const CUSTOMER_ROLE_TYPE = 2;

    public function show(Request $request)
    {
        /** @var Users $user */
        $user = $request->user();

        if (!$user || (int) $user->role_type !== self::CUSTOMER_ROLE_TYPE) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized customer access',
            ], 403);
        }

        return response()->json([
            'status' => true,
            'message' => 'Profile retrieved successfully',
            'data' => $this->formatProfile($user),
        ], 200);
    }

    public function update(Request $request)
    {
        /** @var Users $user */
        $user = $request->user();

        if (!$user || (int) $user->role_type !== self::CUSTOMER_ROLE_TYPE) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized customer access',
            ], 403);
        }

        if ($request->filled('full_name') && !$request->filled('name')) {
            $request->merge(['name' => $request->input('full_name')]);
        }
        if ($request->filled('email_id') && !$request->filled('email')) {
            $request->merge(['email' => $request->input('email_id')]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->user_id, 'user_id'),
            ],
            'email_id' => ['nullable', 'email', 'max:255'],
            'dob' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],
            'customer_address' => ['nullable', 'string', 'max:1000'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'] ?? null;

        if ($request->hasFile('profile_image')) {
            $uploadPath = public_path('uploads/customers');
            File::ensureDirectoryExists($uploadPath);

            $file = $request->file('profile_image');
            $fileName = 'customer_' . $user->user_id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadPath, $fileName);

            $user->profile_image = $fileName;
        }

        $user->save();

        $customer = Customers::firstOrNew(['user_id' => $user->user_id]);
        $customer->dob = $validated['dob'] ?? null;
        $customer->gender = $validated['gender'] ?? null;
        $customer->customer_address = $validated['customer_address'] ?? null;
        $customer->save();

        if (array_key_exists('customer_address', $validated)) {
            $this->syncProfileAddress($customer, $user, $validated['customer_address']);
        }

        $refreshed = $user->fresh();

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully',
            'profile_status' => [
                'account_status' => $refreshed->customerAccountApprovalLabel(),
                'can_place_orders' => $refreshed->canPlaceOrdersAsCustomer(),
            ],
            'data' => $this->formatProfile($refreshed),
        ], 200);
    }

    private function formatProfile(Users $user): array
    {
        $customer = Customers::with([
            'addresses' => function ($query) {
                $query->orderByDesc('is_default')->orderByDesc('updated_at')->orderByDesc('customer_address_id');
            },
            'defaultAddress',
        ])->where('user_id', $user->user_id)->first();
        $profileImageUrl = null;

        if (!empty($user->profile_image)) {
            $profileImageUrl = url('public/uploads/customers/' . $user->profile_image);
        }

        return [
            'personal_information' => [
                'user_id' => $user->user_id,
                'name' => $user->name,
                'role_type' => $user->role_type,
                'account_status' => $user->customerAccountApprovalLabel(),
                'can_place_orders' => $user->canPlaceOrdersAsCustomer(),
                'preferred_language' => $user->preferred_language ?: config('app.locale'),
                'dob' => $customer?->dob,
                'gender' => $customer?->gender,
                'profile_photo' => $user->profile_image,
                'profile_photo_url' => $profileImageUrl,
            ],
            'contact_details' => [
                'email' => $user->email,
                'mobile' => $user->mobile,
                'customer_address' => $customer?->customer_address,
                'default_shipping_address' => $customer?->defaultAddress
                    ? $this->transformAddress($customer->defaultAddress)
                    : null,
                'shipping_addresses' => $customer?->addresses
                    ? $customer->addresses->map(fn (CustomerAddress $address) => $this->transformAddress($address))->values()->all()
                    : [],
            ],
        ];
    }

    private function syncProfileAddress(Customers $customer, Users $user, ?string $customerAddress): void
    {
        $normalizedAddress = is_string($customerAddress) ? trim($customerAddress) : '';
        $defaultAddress = $customer->addresses()->where('is_default', true)->first();

        if ($normalizedAddress === '') {
            if ($defaultAddress) {
                $defaultAddress->delete();
            }

            $nextDefaultAddress = $customer->addresses()->orderBy('customer_address_id')->first();
            if ($nextDefaultAddress) {
                $customer->addresses()
                    ->where('customer_address_id', $nextDefaultAddress->customer_address_id)
                    ->update(['is_default' => true]);
                $nextDefaultAddress = $nextDefaultAddress->fresh();
            }

            $customer->syncLegacyAddress($nextDefaultAddress);
            return;
        }

        if (!$defaultAddress) {
            $customer->addresses()->update(['is_default' => false]);
            $defaultAddress = $customer->addresses()->create([
                'contact_name' => $user->name,
                'mobile' => $user->mobile,
                'address_line' => $normalizedAddress,
                'address_type' => 'other',
                'is_default' => true,
            ]);
        } else {
            $defaultAddress->address_line = $normalizedAddress;
            $defaultAddress->contact_name = $defaultAddress->contact_name ?: $user->name;
            $defaultAddress->mobile = $defaultAddress->mobile ?: $user->mobile;
            $defaultAddress->is_default = true;
            $defaultAddress->save();
        }

        $customer->syncLegacyAddress($defaultAddress->fresh());
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
        ];
    }
}
