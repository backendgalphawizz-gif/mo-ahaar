<?php

namespace App\Support;

use App\Models\DriverProfile;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DriverProfileValidator
{
    public const DOCUMENT_TYPES = ['pan', 'aadhar'];

    public const ACCOUNT_TYPES = ['savings', 'current', 'Savings', 'Current'];

    public static function personalRules(?int $driverUserId = null, bool $requireIdentityDocument = false): array
    {
        return [
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
            'identity_document' => array_filter([
                $requireIdentityDocument ? 'required' : 'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:5120',
            ]),
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
            self::personalRules($driverUserId, true),
            self::vehicleRules(true),
            self::bankRules(),
            [
                'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).+$/', 'confirmed'],
                'approval_status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            ]
        );
    }

    public static function adminUpdateRules(?int $driverUserId, ?DriverProfile $profile): array
    {
        $hasPan = !empty($profile?->pan_card);
        $hasAadhar = !empty($profile?->aadhar_card);
        $hasIdentity = ($profile?->document_type === 'pan' && $hasPan)
            || ($profile?->document_type === 'aadhar' && $hasAadhar)
            || $hasPan
            || $hasAadhar;

        return array_merge(
            self::personalRules($driverUserId, !$hasIdentity),
            self::vehicleRules(false, !empty($profile?->rc_image), !empty($profile?->driving_license)),
            self::bankRules(),
            [
                'password' => ['nullable', 'string', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).+$/', 'confirmed'],
                'approval_status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
                'status' => ['nullable', Rule::in(['0', '1', 0, 1])],
            ]
        );
    }

    public static function applyPucImageRule(array $rules, ?DriverProfile $profile): array
    {
        if (!empty($profile?->puc_image)) {
            $rules['puc_image'] = ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120', 'required_with:puc_number'];
        }

        return $rules;
    }

    public static function messages(): array
    {
        return [
            'name.required' => 'Full name is required.',
            'mobile.regex' => 'Please enter a valid 10-digit Indian mobile number.',
            'mobile.digits' => 'Mobile number must be exactly 10 digits.',
            'document_type.required' => 'Document type is required.',
            'document_type.in' => 'Document type must be PAN or Aadhaar.',
            'identity_document.required' => 'Identity document image is required.',
            'identity_document.mimes' => 'Identity document must be JPG, PNG, WEBP, or PDF.',
            'account_number.regex' => 'Account number must contain digits only.',
            'ifsc_code.regex' => 'Please enter a valid IFSC code.',
            'vehicle_number.regex' => 'Vehicle number format is invalid.',
            'driving_license_number.regex' => 'Driving license number format is invalid.',
            'driving_license.required' => 'Driving license image is required.',
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
            'vehicle_number' => fn ($v) => strtoupper(preg_replace('/\s+/', ' ', trim($v))),
            'driving_license_number' => fn ($v) => strtoupper(trim($v)),
            'account_holder_name' => fn ($v) => trim($v),
            'bank_name' => fn ($v) => trim($v),
            'account_number' => fn ($v) => trim($v),
            'ifsc_code' => fn ($v) => strtoupper(trim($v)),
            'account_type' => fn ($v) => strtolower(trim($v)),
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
        if ($request->hasFile('identity_document')) {
            if ($documentType === DriverProfile::DOCUMENT_PAN) {
                $deleteFile($profile->pan_card);
                $profile->pan_card = $storeFile($request->file('identity_document'), 'pan_' . $profile->driver_id);
                $profile->pan_card_uploaded_at = now();
                $deleteFile($profile->aadhar_card);
                $profile->aadhar_card = null;
                $profile->aadhar_card_uploaded_at = null;
            } else {
                $deleteFile($profile->aadhar_card);
                $profile->aadhar_card = $storeFile($request->file('identity_document'), 'aadhar_' . $profile->driver_id);
                $profile->aadhar_card_uploaded_at = now();
                $deleteFile($profile->pan_card);
                $profile->pan_card = null;
                $profile->pan_card_uploaded_at = null;
            }
        }

        if ($request->hasFile('rc_image')) {
            $deleteFile($profile->rc_image);
            $profile->rc_image = $storeFile($request->file('rc_image'), 'rc_' . $profile->driver_id);
            $profile->rc_image_uploaded_at = now();
        }

        if ($request->hasFile('driving_license')) {
            $deleteFile($profile->driving_license);
            $profile->driving_license = $storeFile($request->file('driving_license'), 'license_' . $profile->driver_id);
            $profile->driving_license_uploaded_at = now();
        }

        if ($request->hasFile('puc_image')) {
            $deleteFile($profile->puc_image);
            $profile->puc_image = $storeFile($request->file('puc_image'), 'puc_' . $profile->driver_id);
            $profile->puc_image_uploaded_at = now();
        }

        $profile->save();

        return $profile;
    }
}
