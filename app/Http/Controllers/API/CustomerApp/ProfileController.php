<?php

namespace App\Http\Controllers\API\CustomerApp;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\Customers;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
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

        $isWholesaler = Schema::hasColumn('users', 'user_type')
            && strcasecmp((string) ($user->user_type ?? ''), 'Wholesaler') === 0;

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
            'gst_number' => [
                'nullable',
                'string',
                'max:30',
                Rule::requiredIf($isWholesaler),
            ],
            'company_name' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf($isWholesaler && Schema::hasColumn('users', 'company_name')),
            ],
            'dob' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],
            'customer_address' => ['nullable', 'string', 'max:1000'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'gst_number.required' => 'GST number is mandatory for wholesaler accounts.',
            'company_name.required' => 'Company name is mandatory for wholesaler accounts.',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'] ?? null;

        if (Schema::hasColumn('users', 'company_name') && array_key_exists('company_name', $validated)) {
            $raw = $validated['company_name'];
            $user->company_name = is_string($raw) && trim($raw) !== '' ? trim($raw) : null;
        }

        $gstChangeRequiresReapproval = false;

        if (Schema::hasColumn('users', 'gst_number') && array_key_exists('gst_number', $validated)) {
            $gstRaw = $validated['gst_number'];
            $gstNormalized = null;
            if (is_string($gstRaw)) {
                $t = trim($gstRaw);
                $gstNormalized = $t !== '' ? strtoupper($t) : null;
            }
            $oldGst = strtoupper(trim((string) ($user->gst_number ?? '')));
            $newGst = strtoupper(trim((string) ($gstNormalized ?? '')));
            $user->gst_number = $gstNormalized;
            if ($oldGst !== $newGst) {
                if (Schema::hasColumn('users', 'gst_verified_at')) {
                    $user->gst_verified_at = null;
                }
                if (Schema::hasColumn('users', 'approval_status')) {
                    $user->approval_status = 'pending';
                    $gstChangeRequiresReapproval = true;
                    if (Schema::hasColumn('users', 'status')) {
                        $user->status = 0;
                    }
                }
            }
        }

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

        $profileStatus = [
            'requires_admin_approval' => $gstChangeRequiresReapproval,
            'account_status' => $refreshed->customerAccountApprovalLabel(),
            'can_place_orders' => $refreshed->canPlaceOrdersAsCustomer(),
            'gst_verification' => $this->gstVerificationForProfileResponse($refreshed),
        ];

        return response()->json([
            'status' => true,
            'message' => $gstChangeRequiresReapproval
                ? 'Profile updated. Your GST change is pending admin approval; ordering stays disabled until approved.'
                : 'Profile updated successfully',
            'requires_admin_approval' => $gstChangeRequiresReapproval,
            'profile_status' => $profileStatus,
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

        $isWholesaler = Schema::hasColumn('users', 'user_type')
            && strcasecmp((string) ($user->user_type ?? ''), 'Wholesaler') === 0;

        $personalInformation = [
            'user_id' => $user->user_id,
            'name' => $user->name,
            'role_type' => $user->role_type,
            'user_type' => $user->user_type,
            'gst_number' => $user->gst_number,
            'account_status' => $user->customerAccountApprovalLabel(),
            'can_place_orders' => $user->canPlaceOrdersAsCustomer(),
            'preferred_language' => $user->preferred_language ?: config('app.locale'),
            'dob' => $customer?->dob,
            'gender' => $customer?->gender,
            'profile_photo' => $user->profile_image,
            'profile_photo_url' => $profileImageUrl,
        ];

        $gstVerification = $this->gstVerificationForProfileResponse($user);
        if ($gstVerification !== null) {
            $personalInformation['gst_verification'] = $gstVerification;
        }

        if ($isWholesaler && Schema::hasColumn('users', 'company_name')) {
            $personalInformation['company_name'] = $user->company_name;
        }

        return [
            'personal_information' => $personalInformation,
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

    /**
     * GST admin verification for wholesalers; retailers get null (omit from nested profile, null in profile_status on update).
     */
    private function gstVerificationForProfileResponse(Users $user): ?array
    {
        if (!Schema::hasColumn('users', 'user_type')
            || strcasecmp((string) ($user->user_type ?? ''), 'Wholesaler') !== 0) {
            return null;
        }

        $gst = trim((string) ($user->gst_number ?? ''));
        if ($gst === '') {
            return [
                'gst_verified' => false,
                'gst_verified_at' => null,
                'gst_verification_status' => 'no_gst_on_file',
            ];
        }

        if (!Schema::hasColumn('users', 'gst_verified_at')) {
            return [
                'gst_verified' => false,
                'gst_verified_at' => null,
                'gst_verification_status' => 'unavailable',
            ];
        }

        $verifiedAt = $user->gst_verified_at;
        $isVerified = $verifiedAt !== null;

        return [
            'gst_verified' => $isVerified,
            'gst_verified_at' => $isVerified ? $verifiedAt->toIso8601String() : null,
            'gst_verification_status' => $isVerified ? 'verified' : 'pending_verification',
        ];
    }
}
