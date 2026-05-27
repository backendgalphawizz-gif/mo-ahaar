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
            'vehicle_number' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9\s\-]+$/'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'name.required' => 'Full name is required.',
            'mobile.regex' => 'Please enter a valid 10-digit Indian mobile number.',
            'vehicle_number.regex' => 'Vehicle number format is invalid.',
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

        $profile = $this->getOrCreateDriverProfile((int) $driver->user_id);
        $profile->vehicle_number = strtoupper(preg_replace('/\s+/', ' ', trim($validated['vehicle_number'])));
        $profile->save();

        return response()->json([
            'status' => true,
            'message' => 'Personal information updated successfully',
            'data' => $this->formatFullProfile($driver->fresh()),
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

        $validated = $request->validate([
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

        $profileImageUrl = null;
        if (!empty($driver->profile_image)) {
            $profileImageUrl = url('public/uploads/drivers/' . $driver->profile_image);
        }

        return [
            'personal_information' => [
                'user_id' => $driver->user_id,
                'full_name' => $driver->name,
                'name' => $driver->name,
                'mobile' => $driver->mobile,
                'country_code' => '+91',
                'email' => $driver->email,
                'vehicle_number' => $profile?->vehicle_number,
                'profile_photo' => $driver->profile_image,
                'profile_photo_url' => $profileImageUrl,
            ],
            'documents' => $this->formatDocuments($profile),
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
        ];
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
