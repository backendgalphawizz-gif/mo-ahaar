<?php

namespace App\Support;

use App\Models\DriverProfile;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DriverProfileValidator
{
    public const ADMIN_TABS = ['personal', 'vehicle', 'bank'];

    public const DOCUMENT_TYPES = ['pan', 'aadhar'];

    public const ACCOUNT_TYPES = ['savings', 'current', 'Savings', 'Current'];

    public static function adminPersonalRules(?int $driverUserId = null, bool $isCreate = false): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'mobile' => [
                'required',
                'digits:10',
                'regex:/^[6-9][0-9]{9}$/',
                Rule::unique('users', 'mobile')
                    ->where(fn ($query) => $query->where('role_type', Users::DRIVER_APP_ROLE_TYPE))
                    ->ignore($driverUserId, 'user_id'),
            ],
            'email' => [
                'nullable',
                'email',
                'max:150',
                Rule::unique('users', 'email')
                    ->where(fn ($query) => $query->where('role_type', Users::DRIVER_APP_ROLE_TYPE))
                    ->ignore($driverUserId, 'user_id'),
            ],
            'address' => ['required', 'string', 'min:5', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];

        if ($isCreate) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        } else {
            $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
        }

        return $rules;
    }

    public static function adminVehicleRules(bool $isCreate = false, ?DriverProfile $profile = null): array
    {
        $hasLicenseFront = !empty($profile?->driving_license);
        $hasLicenseBack = !empty($profile?->driving_license_back);

        return [
            'vehicle_number' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9\s\-]+$/'],
            'driving_license_number' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9\s\-]+$/'],
            'driving_license' => array_filter([
                ($isCreate && !$hasLicenseFront) ? 'required' : 'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:5120',
            ]),
            'driving_license_back' => array_filter([
                ($isCreate && !$hasLicenseBack) ? 'required' : 'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:5120',
            ]),
        ];
    }

    public static function adminBankRules(): array
    {
        return [
            'account_holder_name' => ['required', 'string', 'max:150'],
            'bank_name' => ['required', 'string', 'max:150'],
            'branch_name' => ['required', 'string', 'max:150'],
            'account_number' => ['required', 'string', 'max:30', 'regex:/^[0-9]+$/'],
            'ifsc_code' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z]{4}0[A-Za-z0-9]{6}$/'],
            'account_type' => ['required', 'string', Rule::in(['savings', 'current', 'Savings', 'Current', 'saving', 'Saving'])],
        ];
    }

    public static function personalRules(?int $driverUserId = null, ?DriverProfile $profile = null, bool $isCreate = false): array
    {
        return array_merge([
            'name' => ['required', 'string', 'max:100'],
            'mobile' => [
                'required',
                'digits:10',
                'regex:/^[6-9][0-9]{9}$/',
                Rule::unique('users', 'mobile')
                    ->where(fn ($query) => $query->where('role_type', Users::DRIVER_APP_ROLE_TYPE))
                    ->ignore($driverUserId, 'user_id'),
            ],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')
                    ->where(fn ($query) => $query->where('role_type', Users::DRIVER_APP_ROLE_TYPE))
                    ->ignore($driverUserId, 'user_id'),
            ],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'document_type' => ['required', Rule::in(self::DOCUMENT_TYPES)],
        ], self::documentFileRules($profile, $isCreate));
    }

    public static function documentFileRules(?DriverProfile $profile, bool $isCreate): array
    {
        $fileRule = ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'];

        return [
            'identity_document' => array_merge([
                Rule::requiredIf(fn () => request('document_type') === 'pan'
                    && !request()->hasFile('identity_document')
                    && ($isCreate || empty($profile?->pan_card))),
            ], $fileRule),
            'aadhar_card' => array_merge([
                Rule::requiredIf(fn () => request('document_type') === 'aadhar'
                    && !request()->hasFile('aadhar_card')
                    && ($isCreate || empty($profile?->aadhar_card))),
            ], $fileRule),
            'aadhar_card_back' => array_merge([
                Rule::requiredIf(fn () => request('document_type') === 'aadhar'
                    && !request()->hasFile('aadhar_card_back')
                    && ($isCreate || empty($profile?->aadhar_card_back))),
            ], $fileRule),
        ];
    }

    public static function vehicleRules(bool $requireFiles = false, bool $hasRc = false, bool $hasLicenseImage = false): array
    {
        return [
            'vehicle_number' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9\s\-]+$/'],
            'driving_license_number' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9\s\-]+$/'],
            'rc_image' => array_filter([
                ($requireFiles && !$hasRc) ? 'required' : 'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:5120',
            ]),
            'driving_license' => array_filter([
                ($requireFiles && !$hasLicenseImage) ? 'required' : 'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:5120',
            ]),
            'puc_number' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9\s\-\/]+$/'],
            'puc_expiry_date' => ['nullable', 'date', 'required_with:puc_number'],
            'puc_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120', 'required_with:puc_number'],
        ];
    }

    public static function bankRules(): array
    {
        return [
            'account_holder_name' => ['required', 'string', 'max:150'],
            'bank_name' => ['required', 'string', 'max:150'],
            'account_number' => ['required', 'string', 'max:30', 'regex:/^[0-9]+$/'],
            'ifsc_code' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z]{4}0[A-Za-z0-9]{6}$/'],
            'account_type' => ['required', 'string', Rule::in(self::ACCOUNT_TYPES)],
        ];
    }

    public static function adminCreateRules(?int $driverUserId = null): array
    {
        return array_merge(
            self::adminPersonalRules($driverUserId, true),
            self::adminVehicleRules(true),
            self::adminBankRules(),
            [
                'approval_status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            ]
        );
    }

    public static function adminUpdateRules(?int $driverUserId, ?DriverProfile $profile): array
    {
        return array_merge(
            self::adminPersonalRules($driverUserId, false),
            self::adminVehicleRules(false, $profile),
            self::adminBankRules(),
            [
                'approval_status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
                'status' => ['nullable', Rule::in(['0', '1', 0, 1])],
            ]
        );
    }

    /**
     * @param  array<int, string>  $errorKeys
     */
    public static function tabForFirstError(array $errorKeys): string
    {
        $fieldsByTab = [
            'personal' => ['name', 'mobile', 'email', 'address', 'city', 'profile_image', 'password', 'password_confirmation'],
            'vehicle' => ['vehicle_number', 'driving_license_number', 'driving_license', 'driving_license_back'],
            'bank' => ['account_holder_name', 'bank_name', 'branch_name', 'account_number', 'ifsc_code', 'account_type', 'approval_status'],
        ];

        foreach (self::ADMIN_TABS as $tab) {
            foreach ($errorKeys as $key) {
                if (in_array($key, $fieldsByTab[$tab], true)) {
                    return $tab;
                }
            }
        }

        return 'personal';
    }

    public static function applyPucImageRule(array $rules, ?DriverProfile $profile): array
    {
        if (!empty($profile?->puc_image)) {
            $rules['puc_image'] = ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120', 'required_with:puc_number'];
        }

        return $rules;
    }

    public static function hasIdentityDocuments(?DriverProfile $profile): bool
    {
        if (!$profile) {
            return false;
        }

        if ($profile->document_type === DriverProfile::DOCUMENT_PAN) {
            return !empty($profile->pan_card);
        }

        if ($profile->document_type === DriverProfile::DOCUMENT_AADHAR) {
            return !empty($profile->aadhar_card) && !empty($profile->aadhar_card_back);
        }

        return !empty($profile->pan_card)
            || (!empty($profile->aadhar_card) && !empty($profile->aadhar_card_back));
    }

    public static function messages(): array
    {
        return [
            'name.required' => 'Full name is required.',
            'mobile.required' => 'Mobile number is required.',
            'mobile.regex' => 'Please enter a valid 10-digit Indian mobile number.',
            'mobile.digits' => 'Mobile number must be exactly 10 digits.',
            'email.email' => 'Please enter a valid email address.',
            'address.required' => 'Full address is required.',
            'address.min' => 'Address must be at least 5 characters.',
            'city.required' => 'City is required.',
            'password.required' => 'Password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
            'document_type.required' => 'Document type is required.',
            'vehicle_number.required' => 'Vehicle registration number is required.',
            'driving_license_number.required' => 'Driving license number is required.',
            'account_holder_name.required' => 'Account holder name is required.',
            'bank_name.required' => 'Bank name is required.',
            'branch_name.required' => 'Branch name is required.',
            'account_number.required' => 'Account number is required.',
            'ifsc_code.required' => 'IFSC code is required.',
            'account_type.required' => 'Account type is required.',
            'document_type.in' => 'Document type must be PAN or Aadhaar.',
            'identity_document.required' => 'PAN card image is required.',
            'identity_document.mimes' => 'PAN card must be JPG, PNG, WEBP, or PDF.',
            'aadhar_card.required' => 'Aadhaar front image is required.',
            'aadhar_card.mimes' => 'Aadhaar front must be JPG, PNG, WEBP, or PDF.',
            'aadhar_card_back.required' => 'Aadhaar back image is required.',
            'aadhar_card_back.mimes' => 'Aadhaar back must be JPG, PNG, WEBP, or PDF.',
            'account_number.regex' => 'Account number must contain digits only.',
            'ifsc_code.regex' => 'Please enter a valid IFSC code.',
            'vehicle_number.regex' => 'Vehicle number format is invalid.',
            'driving_license_number.regex' => 'Driving license number format is invalid.',
            'driving_license.required' => 'Driving license front image is required.',
            'driving_license_back.required' => 'Driving license back image is required.',
            'rc_image.required' => 'RC image is required.',
            'puc_expiry_date.required_with' => 'PUC expiry date is required when PUC number is provided.',
            'puc_image.required_with' => 'PUC image is required when PUC number is provided.',
            'password.regex' => 'Password must include uppercase, lowercase, number and special character.',
        ];
    }

    public static function syncProfileFromRequest(DriverProfile $profile, array $validated, Request $request, callable $storeFile, callable $deleteFile): DriverProfile
    {
        $scalarFields = [
            'document_type' => fn ($v) => strtolower(trim((string) $v)),
            'address' => fn ($v) => trim((string) $v),
            'city' => fn ($v) => trim((string) $v),
            'vehicle_number' => fn ($v) => strtoupper(preg_replace('/\s+/', ' ', trim($v))),
            'driving_license_number' => fn ($v) => strtoupper(trim($v)),
            'account_holder_name' => fn ($v) => trim($v),
            'bank_name' => fn ($v) => trim($v),
            'branch_name' => fn ($v) => trim($v),
            'account_number' => fn ($v) => trim($v),
            'ifsc_code' => fn ($v) => strtoupper(trim($v)),
            'account_type' => function ($v) {
                $type = strtolower(trim((string) $v));
                if ($type === 'saving') {
                    return 'savings';
                }

                return $type;
            },
            'puc_number' => fn ($v) => $v !== '' && $v !== null ? strtoupper(trim($v)) : null,
            'puc_expiry_date' => fn ($v) => $v,
        ];

        foreach ($scalarFields as $field => $normalizer) {
            if (array_key_exists($field, $validated)) {
                $profile->{$field} = $normalizer($validated[$field]);
            }
        }

        if (array_key_exists('puc_number', $validated) && empty($profile->puc_number)) {
            $profile->puc_expiry_date = null;
            if ($profile->puc_image) {
                $deleteFile($profile->puc_image);
                $profile->puc_image = null;
                $profile->puc_image_uploaded_at = null;
            }
        }

        $documentType = $profile->document_type;

        if ($documentType === DriverProfile::DOCUMENT_PAN) {
            if ($request->hasFile('identity_document')) {
                $deleteFile($profile->pan_card);
                $profile->pan_card = $storeFile($request->file('identity_document'), 'pan_' . $profile->driver_id);
                $profile->pan_card_uploaded_at = now();
            }
            self::clearAadharDocuments($profile, $deleteFile);
        } elseif ($documentType === DriverProfile::DOCUMENT_AADHAR) {
            if ($request->hasFile('aadhar_card')) {
                $deleteFile($profile->aadhar_card);
                $profile->aadhar_card = $storeFile($request->file('aadhar_card'), 'aadhar_front_' . $profile->driver_id);
                $profile->aadhar_card_uploaded_at = now();
            }
            if ($request->hasFile('aadhar_card_back')) {
                $deleteFile($profile->aadhar_card_back);
                $profile->aadhar_card_back = $storeFile($request->file('aadhar_card_back'), 'aadhar_back_' . $profile->driver_id);
                $profile->aadhar_card_back_uploaded_at = now();
            }
            self::clearPanDocument($profile, $deleteFile);
        }

        if ($request->hasFile('rc_image')) {
            $deleteFile($profile->rc_image);
            $profile->rc_image = $storeFile($request->file('rc_image'), 'rc_' . $profile->driver_id);
            $profile->rc_image_uploaded_at = now();
        }

        if ($request->hasFile('driving_license')) {
            $deleteFile($profile->driving_license);
            $profile->driving_license = $storeFile($request->file('driving_license'), 'license_front_' . $profile->driver_id);
            $profile->driving_license_uploaded_at = now();
        }

        if ($request->hasFile('driving_license_back')) {
            $deleteFile($profile->driving_license_back);
            $profile->driving_license_back = $storeFile($request->file('driving_license_back'), 'license_back_' . $profile->driver_id);
            $profile->driving_license_back_uploaded_at = now();
        }

        if ($request->hasFile('puc_image')) {
            $deleteFile($profile->puc_image);
            $profile->puc_image = $storeFile($request->file('puc_image'), 'puc_' . $profile->driver_id);
            $profile->puc_image_uploaded_at = now();
        }

        $profile->save();

        return $profile;
    }

    private static function clearAadharDocuments(DriverProfile $profile, callable $deleteFile): void
    {
        if ($profile->aadhar_card) {
            $deleteFile($profile->aadhar_card);
            $profile->aadhar_card = null;
            $profile->aadhar_card_uploaded_at = null;
        }
        if ($profile->aadhar_card_back) {
            $deleteFile($profile->aadhar_card_back);
            $profile->aadhar_card_back = null;
            $profile->aadhar_card_back_uploaded_at = null;
        }
    }

    private static function clearPanDocument(DriverProfile $profile, callable $deleteFile): void
    {
        if ($profile->pan_card) {
            $deleteFile($profile->pan_card);
            $profile->pan_card = null;
            $profile->pan_card_uploaded_at = null;
        }
    }
}
