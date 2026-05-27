<?php

namespace App\Http\Controllers\API\DriverApp;

use App\Models\DriverProfile;
use App\Models\Users;
use App\Support\DriverProfileValidator;
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

        $profile = $this->getOrCreateDriverProfile((int) $driver->user_id);
        $hasIdentity = !empty($profile->identityDocumentFile());

        $rules = DriverProfileValidator::personalRules((int) $driver->user_id, !$hasIdentity);
        $validated = $request->validate($rules, DriverProfileValidator::messages());

        $driver->name = trim($validated['name']);
        $driver->mobile = $validated['mobile'];
        $driver->email = $validated['email'];

        if ($request->hasFile('profile_image')) {
            $this->deleteDriverFile($driver->profile_image);
            $driver->profile_image = $this->storeDriverFile(
                $request->file('profile_image'),
                'profile_' . $driver->user_id
            );
        }

        $driver->save();

        $profile->document_type = strtolower(trim((string) $validated['document_type']));
        if ($request->hasFile('identity_document')) {
            $documentType = $profile->document_type;
            if ($documentType === DriverProfile::DOCUMENT_PAN) {
                $this->deleteDriverFile($profile->pan_card);
                $profile->pan_card = $this->storeDriverFile($request->file('identity_document'), 'pan_' . $driver->user_id);
                $profile->pan_card_uploaded_at = now();
                $this->deleteDriverFile($profile->aadhar_card);
                $profile->aadhar_card = null;
                $profile->aadhar_card_uploaded_at = null;
            } else {
                $this->deleteDriverFile($profile->aadhar_card);
                $profile->aadhar_card = $this->storeDriverFile($request->file('identity_document'), 'aadhar_' . $driver->user_id);
                $profile->aadhar_card_uploaded_at = now();
                $this->deleteDriverFile($profile->pan_card);
                $profile->pan_card = null;
                $profile->pan_card_uploaded_at = null;
            }
        }
        $profile->save();

        return response()->json([
            'status' => true,
            'message' => 'Personal information updated successfully',
            'data' => [
                'personal_information' => $this->formatPersonalInformation($driver->fresh(), $profile->fresh()),
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

        $validated = $request->validate(
            DriverProfileValidator::bankRules(),
            DriverProfileValidator::messages()
        );

        $profile = $this->getOrCreateDriverProfile((int) $driver->user_id);
        $profile->fill([
            'account_holder_name' => trim($validated['account_holder_name']),
            'bank_name' => trim($validated['bank_name']),
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

        $profile = $this->getOrCreateDriverProfile((int) $driver->user_id);
        $rules = DriverProfileValidator::vehicleRules(
            false,
            !empty($profile->rc_image),
            !empty($profile->driving_license)
        );
        $rules = DriverProfileValidator::applyPucImageRule($rules, $profile);

        $validated = $request->validate($rules, DriverProfileValidator::messages());

        DriverProfileValidator::syncProfileFromRequest(
            $profile,
            array_merge($validated, [
                'document_type' => $profile->document_type ?? DriverProfile::DOCUMENT_AADHAR,
            ]),
            $request,
            fn ($file, $prefix) => $this->storeDriverFile($file, $prefix),
            fn ($fileName) => $this->deleteDriverFile($fileName)
        );

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

        $profile = $this->getOrCreateDriverProfile((int) $driver->user_id);

        $validated = $request->validate([
            'document_type' => ['nullable', Rule::in(DriverProfileValidator::DOCUMENT_TYPES)],
            'identity_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'driving_license' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'rc_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'puc_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ], DriverProfileValidator::messages());

        if (!$request->hasFile('identity_document')
            && !$request->hasFile('driving_license')
            && !$request->hasFile('rc_image')
            && !$request->hasFile('puc_image')
            && empty($validated['document_type'])) {
            return response()->json([
                'status' => false,
                'message' => 'At least one document update is required.',
            ], 422);
        }

        if (!empty($validated['document_type'])) {
            $profile->document_type = strtolower(trim((string) $validated['document_type']));
        }

        if ($request->hasFile('identity_document') && $profile->document_type) {
            $documentType = $profile->document_type;
            if ($documentType === DriverProfile::DOCUMENT_PAN) {
                $this->deleteDriverFile($profile->pan_card);
                $profile->pan_card = $this->storeDriverFile($request->file('identity_document'), 'pan_' . $driver->user_id);
                $profile->pan_card_uploaded_at = now();
                $this->deleteDriverFile($profile->aadhar_card);
                $profile->aadhar_card = null;
                $profile->aadhar_card_uploaded_at = null;
            } else {
                $this->deleteDriverFile($profile->aadhar_card);
                $profile->aadhar_card = $this->storeDriverFile($request->file('identity_document'), 'aadhar_' . $driver->user_id);
                $profile->aadhar_card_uploaded_at = now();
                $this->deleteDriverFile($profile->pan_card);
                $profile->pan_card = null;
                $profile->pan_card_uploaded_at = null;
            }
        }

        if ($request->hasFile('driving_license')) {
            $this->deleteDriverFile($profile->driving_license);
            $profile->driving_license = $this->storeDriverFile(
                $request->file('driving_license'),
                'license_' . $driver->user_id
            );
            $profile->driving_license_uploaded_at = now();
        }

        if ($request->hasFile('rc_image')) {
            $this->deleteDriverFile($profile->rc_image);
            $profile->rc_image = $this->storeDriverFile(
                $request->file('rc_image'),
                'rc_' . $driver->user_id
            );
            $profile->rc_image_uploaded_at = now();
        }

        if ($request->hasFile('puc_image')) {
            $this->deleteDriverFile($profile->puc_image);
            $profile->puc_image = $this->storeDriverFile(
                $request->file('puc_image'),
                'puc_' . $driver->user_id
            );
            $profile->puc_image_uploaded_at = now();
        }

        $profile->save();

        return response()->json([
            'status' => true,
            'message' => 'Documents updated successfully',
            'data' => [
                'personal_information' => $this->formatPersonalInformation($driver, $profile->fresh()),
                'vehicle_information' => $this->formatVehicleInformation($profile->fresh()),
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
        $profileImageUrl = !empty($driver->profile_image)
            ? url('public/uploads/drivers/' . $driver->profile_image)
            : null;

        return [
            'user_id' => $driver->user_id,
            'full_name' => $driver->name,
            'name' => $driver->name,
            'mobile' => $driver->mobile,
            'country_code' => '+91',
            'email' => $driver->email,
            'profile_photo' => $driver->profile_image,
            'profile_photo_url' => $profileImageUrl,
            'document_type' => $profile?->document_type,
            'identity_document' => $this->formatDocumentField(
                $profile?->identityDocumentFile(),
                $profile?->identityDocumentUploadedAt()
            ),
        ];
    }

    private function formatBankInformation(?DriverProfile $profile): array
    {
        return [
            'account_holder_name' => $profile?->account_holder_name,
            'bank_name' => $profile?->bank_name,
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
            'rc_image' => $this->formatDocumentField(
                $profile?->rc_image,
                $profile?->rc_image_uploaded_at
            ),
            'driving_license_number' => $profile?->driving_license_number,
            'driving_license_image' => $this->formatDocumentField(
                $profile?->driving_license,
                $profile?->driving_license_uploaded_at
            ),
            'puc_number' => $profile?->puc_number,
            'puc_expiry_date' => $profile?->puc_expiry_date?->format('Y-m-d'),
            'puc_image' => $this->formatDocumentField(
                $profile?->puc_image,
                $profile?->puc_image_uploaded_at
            ),
            'is_complete' => $this->isVehicleInformationComplete($profile),
        ];
    }

    private function formatDocuments(?DriverProfile $profile): array
    {
        return [
            'document_type' => $profile?->document_type,
            'identity_document' => $this->formatDocumentField(
                $profile?->identityDocumentFile(),
                $profile?->identityDocumentUploadedAt()
            ),
            'driving_license_image' => $this->formatDocumentField(
                $profile?->driving_license,
                $profile?->driving_license_uploaded_at
            ),
            'rc_image' => $this->formatDocumentField(
                $profile?->rc_image,
                $profile?->rc_image_uploaded_at
            ),
            'puc_image' => $this->formatDocumentField(
                $profile?->puc_image,
                $profile?->puc_image_uploaded_at
            ),
            'is_complete' => $this->isDocumentsComplete($profile),
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

        $pucOk = empty($profile->puc_number)
            || (!empty($profile->puc_expiry_date) && !empty($profile->puc_image));

        return !empty($profile->vehicle_number)
            && !empty($profile->rc_image)
            && !empty($profile->driving_license_number)
            && !empty($profile->driving_license)
            && $pucOk;
    }

    private function isDocumentsComplete(?DriverProfile $profile): bool
    {
        if (!$profile) {
            return false;
        }

        return !empty($profile->document_type)
            && !empty($profile->identityDocumentFile())
            && !empty($profile->driving_license)
            && !empty($profile->rc_image);
    }

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
