@php
    $isEdit = !empty($driver);
    $formAction = $isEdit ? route('admin.delivery.update', $driver->user_id) : route('admin.delivery.store');
    $documentType = old('document_type', $profile->document_type ?? 'aadhar');
    $hasPan = !empty($profile->pan_card ?? null);
    $hasAadharFront = !empty($profile->aadhar_card ?? null);
    $hasAadharBack = !empty($profile->aadhar_card_back ?? null);
@endphp

<div class="page-body">
    <div class="container-fluid">
        <div class="card dashboard-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <a href="{{ route('admin.delivery.index') }}" class="btn btn-outline-secondary btn-sm me-2"><i class="ri-arrow-left-line"></i></a>
                    <div>
                        <h5 class="mb-0">{{ $isEdit ? 'Edit Delivery Partner' : 'Add New Driver' }}</h5>
                        <small class="text-muted">{{ $isEdit ? 'Update details for driver ' . ($profile->driver_code ?? '') : 'Register a new delivery partner' }}</small>
                    </div>
                </div>

                @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" id="driverForm">
                    @csrf

                    <div class="form-section mb-4">
                        <h6>Personal Details</h6>
                        <p class="text-muted">Name, contact, profile photo and identity document</p>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Profile Picture</label>
                                <input type="file" name="profile_image" class="form-control" accept="image/*">
                                @if($isEdit && !empty($driver->profile_image))
                                    <small><a href="{{ asset('public/uploads/drivers/' . $driver->profile_image) }}" target="_blank">View current photo</a></small>
                                @else
                                    <small class="text-muted">JPG, PNG, WEBP. Max 2MB.</small>
                                @endif
                            </div>
                            <div class="col-md-9">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name', $driver->name ?? '') }}" maxlength="100" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Mobile No. <span class="text-danger">*</span></label>
                                        <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $driver->mobile ?? '') }}" maxlength="10" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control" value="{{ old('email', $driver->email ?? '') }}" maxlength="150" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Document Type <span class="text-danger">*</span></label>
                                        <select name="document_type" id="document_type" class="form-select" required>
                                            <option value="aadhar" {{ $documentType === 'aadhar' ? 'selected' : '' }}>Aadhaar</option>
                                            <option value="pan" {{ $documentType === 'pan' ? 'selected' : '' }}>PAN</option>
                                        </select>
                                    </div>
                                    <div class="col-md-8" id="panDocumentFields" style="{{ $documentType === 'pan' ? '' : 'display:none;' }}">
                                        <label class="form-label">PAN Card Image <span class="text-danger pan-required">{{ (!$isEdit || !$hasPan) ? '*' : '' }}</span></label>
                                        <input type="file" name="identity_document" id="identity_document" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf" data-has-existing="{{ ($isEdit && $hasPan) ? '1' : '0' }}">
                                        @if($isEdit && $hasPan)
                                            <small><a href="{{ asset('public/uploads/drivers/' . $profile->pan_card) }}" target="_blank">View current PAN</a></small>
                                        @endif
                                    </div>
                                    <div class="col-md-4 aadhar-document-fields" style="{{ $documentType === 'aadhar' ? '' : 'display:none;' }}">
                                        <label class="form-label">Aadhaar Front <span class="text-danger aadhar-required">{{ (!$isEdit || !$hasAadharFront) ? '*' : '' }}</span></label>
                                        <input type="file" name="aadhar_card" id="aadhar_card" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf" data-has-existing="{{ ($isEdit && $hasAadharFront) ? '1' : '0' }}">
                                        @if($isEdit && $hasAadharFront)
                                            <small><a href="{{ asset('public/uploads/drivers/' . $profile->aadhar_card) }}" target="_blank">View front</a></small>
                                        @endif
                                    </div>
                                    <div class="col-md-4 aadhar-document-fields" style="{{ $documentType === 'aadhar' ? '' : 'display:none;' }}">
                                        <label class="form-label">Aadhaar Back <span class="text-danger aadhar-required">{{ (!$isEdit || !$hasAadharBack) ? '*' : '' }}</span></label>
                                        <input type="file" name="aadhar_card_back" id="aadhar_card_back" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf" data-has-existing="{{ ($isEdit && $hasAadharBack) ? '1' : '0' }}">
                                        @if($isEdit && $hasAadharBack)
                                            <small><a href="{{ asset('public/uploads/drivers/' . $profile->aadhar_card_back) }}" target="_blank">View back</a></small>
                                        @endif
                                    </div>
                                    @if(!$isEdit)
                                        <div class="col-md-6">
                                            <label class="form-label">Password <span class="text-danger">*</span></label>
                                            <input type="password" name="password" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                            <input type="password" name="password_confirmation" class="form-control" required>
                                        </div>
                                    @else
                                        <div class="col-md-6">
                                            <label class="form-label">New Password</label>
                                            <input type="password" name="password" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Confirm Password</label>
                                            <input type="password" name="password_confirmation" class="form-control">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section mb-4">
                        <h6>Vehicle Details</h6>
                        <p class="text-muted">Registration, RC, driving license and optional PUC</p>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Vehicle Number <span class="text-danger">*</span></label>
                                <input type="text" name="vehicle_number" class="form-control" value="{{ old('vehicle_number', $profile->vehicle_number ?? '') }}" maxlength="20" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">RC Image <span class="text-danger">{{ $isEdit && !empty($profile?->rc_image) ? '' : '*' }}</span></label>
                                <input type="file" name="rc_image" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf" {{ !$isEdit || empty($profile?->rc_image) ? 'required' : '' }}>
                                @if($isEdit && !empty($profile?->rc_image))
                                    <small><a href="{{ asset('public/uploads/drivers/' . $profile->rc_image) }}" target="_blank">View RC</a></small>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Driving License No. <span class="text-danger">*</span></label>
                                <input type="text" name="driving_license_number" class="form-control" value="{{ old('driving_license_number', $profile->driving_license_number ?? '') }}" maxlength="50" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Driving License Image <span class="text-danger">{{ $isEdit && !empty($profile?->driving_license) ? '' : '*' }}</span></label>
                                <input type="file" name="driving_license" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf" {{ !$isEdit || empty($profile?->driving_license) ? 'required' : '' }}>
                                @if($isEdit && !empty($profile?->driving_license))
                                    <small><a href="{{ asset('public/uploads/drivers/' . $profile->driving_license) }}" target="_blank">View license</a></small>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">PUC Number</label>
                                <input type="text" name="puc_number" id="puc_number" class="form-control" value="{{ old('puc_number', $profile->puc_number ?? '') }}" maxlength="50">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">PUC Expiry Date</label>
                                <input type="date" name="puc_expiry_date" id="puc_expiry_date" class="form-control" value="{{ old('puc_expiry_date', optional($profile?->puc_expiry_date)->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">PUC Image</label>
                                <input type="file" name="puc_image" id="puc_image" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf" @if($isEdit && !empty($profile?->puc_image)) data-has-existing="1" @endif>
                                @if($isEdit && !empty($profile?->puc_image))
                                    <small><a href="{{ asset('public/uploads/drivers/' . $profile->puc_image) }}" target="_blank">View PUC</a></small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form-section mb-4">
                        <h6>Bank Details</h6>
                        <p class="text-muted">Payment routing info for driver payouts</p>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Account Holder Name <span class="text-danger">*</span></label>
                                <input type="text" name="account_holder_name" class="form-control" value="{{ old('account_holder_name', $profile->account_holder_name ?? '') }}" maxlength="150" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                                <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $profile->bank_name ?? '') }}" maxlength="150" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Account Number <span class="text-danger">*</span></label>
                                <input type="text" name="account_number" class="form-control" value="{{ old('account_number', $profile->account_number ?? '') }}" maxlength="30" oninput="this.value=this.value.replace(/\D/g,'')" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">IFSC Code <span class="text-danger">*</span></label>
                                <input type="text" name="ifsc_code" class="form-control" value="{{ old('ifsc_code', $profile->ifsc_code ?? '') }}" maxlength="11" oninput="this.value=this.value.toUpperCase()" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Account Type <span class="text-danger">*</span></label>
                                <select name="account_type" class="form-select" required>
                                    @foreach(['savings' => 'Savings', 'current' => 'Current'] as $val => $label)
                                        <option value="{{ $val }}" {{ strtolower(old('account_type', $profile->account_type ?? 'savings')) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if(!$isEdit)
                                <div class="col-md-6">
                                    <label class="form-label">Approval Status</label>
                                    <select name="approval_status" class="form-select">
                                        <option value="pending" {{ old('approval_status', 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ old('approval_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                    </select>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('admin.delivery.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-theme">
                            <i class="ri-save-line me-1"></i>{{ $isEdit ? 'Save Changes' : 'Create Driver' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var documentType = document.getElementById('document_type');
    var panFields = document.getElementById('panDocumentFields');
    var aadharFields = document.querySelectorAll('.aadhar-document-fields');
    var identityDocument = document.getElementById('identity_document');
    var aadharFront = document.getElementById('aadhar_card');
    var aadharBack = document.getElementById('aadhar_card_back');
    var pucNumber = document.getElementById('puc_number');
    var pucExpiry = document.getElementById('puc_expiry_date');
    var pucImage = document.getElementById('puc_image');

    function syncDocumentRequirements() {
        var isPan = documentType && documentType.value === 'pan';
        if (panFields) panFields.style.display = isPan ? '' : 'none';
        aadharFields.forEach(function (el) { el.style.display = isPan ? 'none' : ''; });

        if (identityDocument) {
            identityDocument.required = isPan && identityDocument.dataset.hasExisting !== '1';
        }
        if (aadharFront) {
            aadharFront.required = !isPan && aadharFront.dataset.hasExisting !== '1';
        }
        if (aadharBack) {
            aadharBack.required = !isPan && aadharBack.dataset.hasExisting !== '1';
        }
    }

    function syncPucRequirements() {
        var hasPuc = pucNumber && pucNumber.value.trim() !== '';
        if (pucExpiry) pucExpiry.required = hasPuc;
        if (pucImage) pucImage.required = hasPuc && pucImage.dataset.hasExisting !== '1';
    }

    if (documentType) {
        documentType.addEventListener('change', syncDocumentRequirements);
        syncDocumentRequirements();
    }
    if (pucNumber) {
        pucNumber.addEventListener('input', syncPucRequirements);
        syncPucRequirements();
    }
});
</script>
