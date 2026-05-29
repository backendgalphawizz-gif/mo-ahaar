<?php

namespace App\Support;

use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VendorFormValidator
{
    public const TABS = ['personal', 'business', 'bank', 'documents'];

    public static function nextTab(string $tab): ?string
    {
        $index = array_search($tab, self::TABS, true);
        if ($index === false || $index >= count(self::TABS) - 1) {
            return null;
        }

        return self::TABS[$index + 1];
    }

    public static function rulesForTab(string $tab, ?Vendor $vendor, bool $isCreate): array
    {
        $userId = $vendor?->user_id;

        $personal = [
            'owner_name' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[a-zA-Z\s.\']+$/u'],
            'mobile' => [
                'required',
                'digits:10',
                'regex:/^[6-9][0-9]{9}$/',
                Rule::unique('users', 'mobile')
                    ->where(fn ($q) => $q->where('role_type', 3))
                    ->ignore($userId, 'user_id'),
            ],
            'email' => [
                'nullable',
                'email:rfc,filter',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId, 'user_id'),
            ],
            'dob' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'others'])],
            'address' => ['required', 'string', 'min:5', 'max:500'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];

        if ($isCreate) {
            $personal['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $business = [
            'business_name' => ['required', 'string', 'min:2', 'max:150'],
            'business_email' => ['nullable', 'email:rfc,filter', 'max:255'],
            'business_phone' => ['nullable', 'digits:10', 'regex:/^[6-9][0-9]{9}$/'],
            'business_description' => ['nullable', 'string', 'max:2000'],
            'tax_name' => ['nullable', 'string', 'max:100'],
            'tax_number' => ['nullable', 'string', 'max:50'],
            'pan_number' => ['nullable', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/'],
            'gst_number' => ['nullable', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];

        $bank = [
            'bank_name' => ['nullable', 'string', 'max:150'],
            'branch_name' => ['nullable', 'string', 'max:150'],
            'bank_account' => ['nullable', 'regex:/^[0-9]{9,18}$/'],
            'account_holder_name' => ['nullable', 'string', 'max:150', 'regex:/^[a-zA-Z\s.\']+$/u'],
            'ifsc_code' => ['nullable', 'regex:/^[A-Za-z]{4}0[A-Za-z0-9]{6}$/'],
            'account_type' => ['nullable', 'string', 'max:50'],
            'commission_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'approval_status' => ['nullable', Rule::in(['pending', 'approved', 'suspended', 'rejected'])],
        ];

        $documents = [
            'business_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'business_banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'shop_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'aadhaar_card' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'pan_card' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'gst_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'food_license_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'bank_passbook_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'address_proof_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'national_identity_card_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ];

        return match ($tab) {
            'personal' => $personal,
            'business' => $business,
            'bank' => $bank,
            'documents' => $documents,
            default => [],
        };
    }

    public static function allRules(?Vendor $vendor, bool $isCreate): array
    {
        $merged = [];
        foreach (self::TABS as $tab) {
            $merged = array_merge($merged, self::rulesForTab($tab, $vendor, $isCreate));
        }

        return $merged;
    }

    public static function messages(): array
    {
        return [
            'owner_name.required' => 'Owner full name is required.',
            'owner_name.regex' => 'Owner name may only contain letters, spaces, dots and apostrophes.',
            'owner_name.min' => 'Owner name must be at least 2 characters.',
            'owner_name.max' => 'Owner name may not exceed 100 characters.',
            'mobile.required' => 'Mobile number is required.',
            'mobile.digits' => 'Mobile number must be exactly 10 digits.',
            'mobile.regex' => 'Enter a valid 10-digit Indian mobile number starting with 6–9.',
            'mobile.unique' => 'This mobile number is already registered.',
            'email.email' => 'Please enter a valid email address with a proper domain.',
            'email.unique' => 'This email is already registered.',
            'address.required' => 'Address is required.',
            'address.min' => 'Address must be at least 5 characters.',
            'address.max' => 'Address may not exceed 500 characters.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'business_name.required' => 'Restaurant name is required.',
            'business_name.min' => 'Restaurant name must be at least 2 characters.',
            'business_email.email' => 'Please enter a valid business email address.',
            'business_phone.digits' => 'Business phone must be exactly 10 digits.',
            'business_phone.regex' => 'Enter a valid 10-digit Indian mobile number.',
            'pan_number.regex' => 'PAN must be in format ABCDE1234F (5 letters, 4 digits, 1 letter).',
            'gst_number.regex' => 'Please enter a valid 15-character GSTIN.',
            'ifsc_code.regex' => 'Please enter a valid IFSC code (e.g. SBIN0001234).',
            'bank_account.regex' => 'Account number must be 9 to 18 digits.',
            'account_holder_name.regex' => 'Account holder name may only contain letters and spaces.',
            'profile_image.image' => 'Profile image must be a valid image file.',
            'profile_image.max' => 'Profile image may not be greater than 2MB.',
        ];
    }

    public static function validateTab(Request $request, string $tab, ?Vendor $vendor, bool $isCreate): array
    {
        return $request->validate(
            self::rulesForTab($tab, $vendor, $isCreate),
            self::messages()
        );
    }

    public static function validateComplete(Request $request, ?Vendor $vendor, bool $isCreate): array
    {
        return $request->validate(
            self::allRules($vendor, $isCreate),
            self::messages()
        );
    }
}
