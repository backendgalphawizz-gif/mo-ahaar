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

    public static function prevTab(string $tab): ?string
    {
        $index = array_search($tab, self::TABS, true);
        if ($index === false || $index <= 0) {
            return null;
        }

        return self::TABS[$index - 1];
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
            'pan_number' => ['required', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/'],
            'gst_number' => ['required', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'],
            'address' => ['required', 'string', 'min:5', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];

        $bank = [
            'bank_name' => ['required', 'string', 'min:2', 'max:150'],
            'account_holder_name' => ['required', 'string', 'min:2', 'max:150', 'regex:/^[a-zA-Z\s.\']+$/u'],
            'bank_account' => ['required', 'regex:/^[0-9]{8,18}$/'],
            'account_type' => ['required', Rule::in(['savings', 'current'])],
            'ifsc_code' => ['required', 'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/'],
            'branch_name' => ['nullable', 'string', 'max:150'],
            'commission_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'approval_status' => ['nullable', Rule::in(['pending', 'approved', 'suspended', 'rejected'])],
        ];

        $documents = [
            'business_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'business_banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'shop_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'aadhaar_card_front' => self::documentFileRules('aadhaar_card_front', $vendor, $isCreate),
            'aadhaar_card_back' => self::documentFileRules('aadhaar_card_back', $vendor, $isCreate),
            'pan_card' => self::documentFileRules('pan_card', $vendor, $isCreate),
            'gst_file' => self::documentFileRules('gst_file', $vendor, $isCreate),
            'food_license_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
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
            'business_phone.digits' => 'Business mobile must be exactly 10 digits.',
            'business_phone.regex' => 'Enter a valid 10-digit Indian mobile number starting with 6–9.',
            'pan_number.required' => 'PAN number is required.',
            'pan_number.regex' => 'PAN must be in format ABCDE1234F (5 letters, 4 digits, 1 letter).',
            'gst_number.required' => 'GST number is required.',
            'gst_number.regex' => 'Please enter a valid 15-character GSTIN.',
            'latitude.required' => 'Please select a valid address from Google suggestions to set location.',
            'latitude.between' => 'Invalid latitude. Select address from suggestions.',
            'longitude.required' => 'Please select a valid address from Google suggestions to set location.',
            'longitude.between' => 'Invalid longitude. Select address from suggestions.',
            'aadhaar_card_front.required' => 'Aadhaar card (front) upload is required.',
            'aadhaar_card_back.required' => 'Aadhaar card (back) upload is required.',
            'pan_card.required' => 'PAN card document upload is required.',
            'gst_file.required' => 'GST certificate upload is required.',
            'gst_file.mimes' => 'GST certificate must be JPG, PNG or PDF.',
            'gst_file.max' => 'GST certificate file may not be greater than 4MB.',
            'aadhaar_card_front.mimes' => 'Aadhaar front must be JPG, PNG or PDF.',
            'aadhaar_card_back.mimes' => 'Aadhaar back must be JPG, PNG or PDF.',
            'pan_card.mimes' => 'PAN card must be JPG, PNG or PDF.',
            'aadhaar_card_front.max' => 'Aadhaar front file may not be greater than 4MB.',
            'aadhaar_card_back.max' => 'Aadhaar back file may not be greater than 4MB.',
            'pan_card.max' => 'PAN card file may not be greater than 4MB.',
            'bank_name.required' => 'Bank name is required.',
            'bank_name.min' => 'Bank name must be at least 2 characters.',
            'account_holder_name.required' => 'Account holder name is required.',
            'account_holder_name.regex' => 'Account holder name may only contain letters and spaces.',
            'account_holder_name.min' => 'Account holder name must be at least 2 characters.',
            'bank_account.required' => 'Account number is required.',
            'bank_account.regex' => 'Account number must be 8 to 18 digits (numbers only).',
            'account_type.required' => 'Please select account type.',
            'account_type.in' => 'Account type must be Savings or Current.',
            'ifsc_code.required' => 'IFSC code is required.',
            'ifsc_code.regex' => 'Please enter a valid IFSC code (e.g. SBIN0001234).',
            'profile_image.image' => 'Profile image must be a valid image file.',
            'profile_image.max' => 'Profile image may not be greater than 2MB.',
        ];
    }

    /**
     * Pick wizard tab to reopen when validation fails on final submit.
     *
     * @param  array<int, string>  $errorKeys
     */
    public static function tabForFirstError(array $errorKeys): string
    {
        $fieldsByTab = [
            'personal' => ['owner_name', 'mobile', 'email', 'dob', 'gender', 'password', 'password_confirmation', 'profile_image'],
            'business' => [
                'business_name', 'business_email', 'business_phone', 'business_description',
                'tax_name', 'tax_number', 'pan_number', 'gst_number', 'address', 'latitude', 'longitude',
            ],
            'bank' => ['bank_name', 'account_holder_name', 'bank_account', 'account_type', 'ifsc_code', 'branch_name', 'commission_percent', 'approval_status'],
            'documents' => [
                'business_logo', 'business_banner', 'shop_image',
                'aadhaar_card_front', 'aadhaar_card_back', 'pan_card', 'gst_file', 'food_license_file',
            ],
        ];

        foreach (self::TABS as $tab) {
            foreach ($errorKeys as $key) {
                if (in_array($key, $fieldsByTab[$tab], true)) {
                    return $tab;
                }
            }
        }

        return 'documents';
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

    /**
     * @return array<int, string>
     */
    private static function documentFileRules(string $field, ?Vendor $vendor, bool $isCreate): array
    {
        $hasExisting = $vendor && !empty($vendor->{$field});
        $required = $isCreate || !$hasExisting;

        return array_merge(
            [$required ? 'required' : 'nullable'],
            ['file', 'mimes:jpg,jpeg,png,pdf', 'max:4096']
        );
    }
}
