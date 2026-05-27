<?php

namespace App\Http\Controllers\API\DriverApp;

use App\Models\DriverProfile;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ProfileController extends DriverAppController
{
    public function show(Request $request)
    {
        $driver = $this->resolveDriver($request);
        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized driver access',
            ], 403);
        }

        return response()->json([
            'status' => true,
            'message' => 'Profile retrieved successfully',
            'data' => $this->formatFullProfile($driver),
        ], 200);
    }

    public function updatePersonalInformation(Request $request)
    {
        $driver = $this->resolveDriver($request);
        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized driver access',
            ], 403);
        }

        if ($request->filled('full_name') && !$request->filled('name')) {
            $request->merge(['name' => $request->input('full_name')]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'full_name' => ['sometimes', 'string', 'max:100'],
            'mobile' => [
                'required',
                'digits:10',
                'regex:/^[6-9][0-9]{9}$/',
                Rule::unique('users', 'mobile')
                    ->where(fn ($query) => $query->where('role_type', self::DRIVER_ROLE_TYPE))
                    ->ignore($driver->user_id, 'user_id'),
            ],
            'country_code' => ['nullable', 'regex:/^\+\d{1,4}$/'],
            'email' => [
                'nullable',
                'email',
                'max:150',
                Rule::unique('users', 'email')
                    ->where(fn ($query) => $query->where('role_type', self::DRIVER_ROLE_TYPE))
                    ->ignore($driver->user_id, 'user_id'),
            ],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'name.required' => 'Full name is required.',
            'mobile.regex' => 'Please enter a valid 10-digit Indian mobile number.',
        ]);

        $driver->name = trim($validated['name']);
        $driver->mobile = $validated['mobile'];
        $driver->email = $validated['email'] ?? null;

        if ($request->hasFile('profile_image')) {
            $this->deleteDriverFile($driver->profile_image);
            $driver->profile_image = $this->storeDriverFile(
                $request->file('profile_image'),
                'profile_' . $driver->user_id
            );
        }

        $driver->save();

        return response()->json([
            'status' => true,
            'message' => 'Personal information updated successfully',
            'data' => [
                'personal_information' => $this->formatPersonalInformation($driver->fresh(), $this->getOrCreateDriverProfile((int) $driver->user_id)),
            ],
        ], 200);
    }

    public function updateBankInformation(Request $request)
    {
        $driver = $this->resolveDriver($request);
        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized driver access',
            ], 403);
        }

        $validated = $request->validate([
            'account_holder_name' => ['required', 'string', 'max:150'],
            'bank_name' => ['required', 'string', 'max:150'],
            'branch_name' => ['nullable', 'string', 'max:150'],
            'account_number' => ['required', 'string', 'max:30', 'regex:/^[0-9]+$/'],
            'ifsc_code' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z]{4}0[A-Za-z0-9]{6}$/'],
            'account_type' => ['required', 'string', Rule::in(['savings', 'current', 'Savings', 'Current'])],
        ], [
            'account_number.regex' => 'Account number must contain digits only.',
            'ifsc_code.regex' => 'Please enter a valid IFSC code.',
        ]);

        $profile = $this->getOrCreateDriverProfile((int) $driver->user_id);
        $profile->fill([
            'account_holder_name' => trim($validated['account_holder_name']),
            'bank_name' => trim($validated['bank_name']),
            'branch_name' => isset($validated['branch_name']) ? trim($validated['branch_name']) : null,
            'account_number' => trim($validated['account_number']),
            'ifsc_code' => strtoupper(trim($validated['ifsc_code'])),
            'account_type' => strtolower(trim($validated['account_type'])),
        ]);
        $profile->save();

        return response()->json([
            'status' => true,
            'message' => 'Bank information updated successfully',
            'data' => [
                'bank_information' => $this->formatBankInformation($profile->fresh()),
            ],
        ], 200);
    }

    public function updateVehicleInformation(Request $request)
    {
        $driver = $this->resolveDriver($request);
        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized driver access',
            ], 403);
        }

        $validated = $request->validate([
            'vehicle_number' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9\s\-]+$/'],
            'vehicle_type' => ['required', 'string', 'max:50', Rule::in(['bike', 'scooter', 'car', 'van', 'truck', 'other', 'Bike', 'Scooter', 'Car', 'Van', 'Truck', 'Other'])],
            'vehicle_model' => ['nullable', 'string', 'max:100'],
            'vehicle_color' => ['nullable', 'string', 'max:50'],
            'registration_year' => ['nullable', 'integer', 'min:1990', 'max:' . (date('Y') + 1)],
        ], [
            'vehicle_number.regex' => 'Vehicle number format is invalid.',
        ]);

        $profile = $this->getOrCreateDriverProfile((int) $driver->user_id);
        $profile->vehicle_number = strtoupper(preg_replace('/\s+/', ' ', trim($validated['vehicle_number'])));
        $profile->vehicle_type = ucfirst(strtolower(trim($validated['vehicle_type'])));
        $profile->vehicle_model = isset($validated['vehicle_model']) ? trim($validated['vehicle_model']) : null;
        $profile->vehicle_color = isset($validated['vehicle_color']) ? trim($validated['vehicle_color']) : null;
        $profile->registration_year = $validated['registration_year'] ?? null;
        $profile->save();

        return response()->json([
            'status' => true,
            'message' => 'Vehicle information updated successfully',
            'data' => [
                'vehicle_information' => $this->formatVehicleInformation($profile->fresh()),
            ],
        ], 200);
    }

    public function updateDocuments(Request $request)
    {
        $driver = $this->resolveDriver($request);
        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized driver access',
            ], 403);
        }

        $request->validate([
            'driving_license' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'aadhar_card' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ], [
            'driving_license.mimes' => 'Driving license must be JPG, PNG, WEBP, or PDF.',
            'aadhar_card.mimes' => 'Aadhar card must be JPG, PNG, WEBP, or PDF.',
        ]);

        if (!$request->hasFile('driving_license') && !$request->hasFile('aadhar_card')) {
            return response()->json([
                'status' => false,
                'message' => 'At least one document file is required.',
                'errors' => [
                    'driving_license' => ['Upload driving license or Aadhar card.'],
                ],
            ], 422);
        }

        $profile = $this->getOrCreateDriverProfile((int) $driver->user_id);

        if ($request->hasFile('driving_license')) {
            $this->deleteDriverFile($profile->driving_license);
            $profile->driving_license = $this->storeDriverFile(
                $request->file('driving_license'),
                'license_' . $driver->user_id
            );
            $profile->driving_license_uploaded_at = now();
        }

        if ($request->hasFile('aadhar_card')) {
            $this->deleteDriverFile($profile->aadhar_card);
            $profile->aadhar_card = $this->storeDriverFile(
                $request->file('aadhar_card'),
                'aadhar_' . $driver->user_id
            );
            $profile->aadhar_card_uploaded_at = now();
        }

        $profile->save();

        return response()->json([
            'status' => true,
            'message' => 'Documents updated successfully',
            'data' => [
                'documents' => $this->formatDocuments($profile->fresh()),
            ],
        ], 200);
    }

    private function getOrCreateDriverProfile(int $driverId): DriverProfile
    {
        if (!Schema::hasTable('driver_profiles')) {
            return new DriverProfile(['driver_id' => $driverId]);
        }

        return DriverProfile::firstOrCreate(['driver_id' => $driverId]);
    }

    private function formatFullProfile(Users $driver): array
    {
        $profile = Schema::hasTable('driver_profiles')
            ? DriverProfile::where('driver_id', $driver->user_id)->first()
            : null;

        return [
            'personal_information' => $this->formatPersonalInformation($driver, $profile),
            'bank_information' => $this->formatBankInformation($profile),
            'vehicle_information' => $this->formatVehicleInformation($profile),
            'documents' => $this->formatDocuments($profile),
        ];
    }

    private function formatPersonalInformation(Users $driver, ?DriverProfile $profile): array
    {
        $profileImageUrl = null;
        if (!empty($driver->profile_image)) {
            $profileImageUrl = url('public/uploads/drivers/' . $driver->profile_image);
        }

        return [
            'user_id' => $driver->user_id,
            'full_name' => $driver->name,
            'name' => $driver->name,
            'mobile' => $driver->mobile,
            'country_code' => '+91',
            'email' => $driver->email,
            'profile_photo' => $driver->profile_image,
            'profile_photo_url' => $profileImageUrl,
        ];
    }

    private function formatBankInformation(?DriverProfile $profile): array
    {
        return [
            'account_holder_name' => $profile?->account_holder_name,
            'bank_name' => $profile?->bank_name,
            'branch_name' => $profile?->branch_name,
            'account_number' => $profile?->account_number,
            'ifsc_code' => $profile?->ifsc_code,
            'account_type' => $profile?->account_type,
            'is_complete' => $this->isBankInformationComplete($profile),
        ];
    }

    private function formatVehicleInformation(?DriverProfile $profile): array
    {
        return [
            'vehicle_number' => $profile?->vehicle_number,
            'vehicle_type' => $profile?->vehicle_type,
            'vehicle_model' => $profile?->vehicle_model,
            'vehicle_color' => $profile?->vehicle_color,
            'registration_year' => $profile?->registration_year,
            'is_complete' => $this->isVehicleInformationComplete($profile),
        ];
    }

    private function formatDocuments(?DriverProfile $profile): array
    {
        return [
            'driving_license' => $this->formatDocumentField(
                $profile?->driving_license,
                $profile?->driving_license_uploaded_at
            ),
            'aadhar_card' => $this->formatDocumentField(
                $profile?->aadhar_card,
                $profile?->aadhar_card_uploaded_at
            ),
            'is_complete' => !empty($profile?->driving_license) && !empty($profile?->aadhar_card),
        ];
    }

    private function isBankInformationComplete(?DriverProfile $profile): bool
    {
        if (!$profile) {
            return false;
        }

        return !empty($profile->account_holder_name)
            && !empty($profile->bank_name)
            && !empty($profile->account_number)
            && !empty($profile->ifsc_code)
            && !empty($profile->account_type);
    }

    private function isVehicleInformationComplete(?DriverProfile $profile): bool
    {
        if (!$profile) {
            return false;
        }

        return !empty($profile->vehicle_number) && !empty($profile->vehicle_type);
    }

    /**
     * @return array{status: string, status_label: string, file_name: string|null, file_url: string|null, thumbnail_url: string|null, uploaded_at: string|null}
     */
    private function formatDocumentField(?string $fileName, $uploadedAt): array
    {
        $uploaded = !empty($fileName);

        return [
            'status' => $uploaded ? 'uploaded' : 'not_uploaded',
            'status_label' => $uploaded ? 'Uploaded' : 'Not uploaded',
            'file_name' => $fileName,
            'file_url' => $uploaded ? url('public/uploads/drivers/' . $fileName) : null,
            'thumbnail_url' => $uploaded ? url('public/uploads/drivers/' . $fileName) : null,
            'uploaded_at' => $uploadedAt?->toIso8601String(),
        ];
    }

    private function storeDriverFile($file, string $prefix): string
    {
        $uploadPath = public_path('uploads/drivers');
        File::ensureDirectoryExists($uploadPath);

        $fileName = $prefix . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move($uploadPath, $fileName);

        return $fileName;
    }

    private function deleteDriverFile(?string $fileName): void
    {
        if (!$fileName) {
            return;
        }

        $path = public_path('uploads/drivers/' . $fileName);
        if (File::exists($path)) {
            File::delete($path);
        }
    }
}
