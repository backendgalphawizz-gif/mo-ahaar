@php
    $isEdit = !empty($driver);
    $formAction = $isEdit ? route('admin.delivery.update', $driver->user_id) : route('admin.delivery.store');
    $stepKeys = ['personal', 'vehicle', 'bank'];
    $tab = $tab ?? old('driver_tab', 'personal');
    if ($errors->any()) {
        $tab = \App\Support\DriverProfileValidator::tabForFirstError($errors->keys());
    }
    $currentIndex = array_search($tab, $stepKeys, true);
    if ($currentIndex === false) {
        $currentIndex = 0;
        $tab = 'personal';
    }
    $driverCode = $isEdit
        ? ($profile->driver_code ?? ('DB-' . str_pad((string) $driver->user_id, 3, '0', STR_PAD_LEFT)))
        : null;
    $val = function (string $key, $default = '') use ($driver, $profile) {
        $fromProfile = [
            'address', 'city', 'vehicle_number', 'driving_license_number',
            'account_holder_name', 'bank_name', 'branch_name', 'account_number',
            'ifsc_code', 'account_type', 'document_type', 'puc_number',
        ];
        if (in_array($key, $fromProfile, true)) {
            return old($key, $profile?->{$key} ?? $default);
        }
        if ($key === 'puc_expiry_date') {
            $existing = $profile?->puc_expiry_date
                ? $profile->puc_expiry_date->format('Y-m-d')
                : $default;

            return old($key, $existing);
        }
        if (in_array($key, ['name', 'mobile', 'email'], true)) {
            return old($key, $driver?->{$key} ?? $default);
        }

        return old($key, $default);
    };
    $documentType = old('document_type', $profile?->document_type ?? 'aadhar');
    $hasPan = !empty($profile->pan_card ?? null);
    $hasAadharFront = !empty($profile->aadhar_card ?? null);
    $hasAadharBack = !empty($profile->aadhar_card_back ?? null);
    $accountType = strtolower((string) $val('account_type', 'savings'));
    if ($accountType === 'saving') {
        $accountType = 'savings';
    }
@endphp

@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        <div class="card dashboard-card">
            <div class="card-body p-4">
                <div class="vendor-wizard-head d-flex align-items-start gap-3 mb-3">
                    <a href="{{ route('admin.delivery.index') }}" class="btn-back-figma" title="Back"><i class="ri-arrow-left-line"></i></a>
                    <div>
                        <h4 class="figma-page-title mb-1">
                            {{ $isEdit ? 'Edit Delivery Boy' : 'Add New Delivery Boy' }}
                            @if($isEdit)<span class="vendor-code-accent">#{{ $driverCode }}</span>@endif
                        </h4>
                        <p class="figma-page-subtitle mb-0">{{ $isEdit ? 'Update the details for this driver' : 'Fill in the details to register a new driver' }}</p>
                    </div>
                </div>

                @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong class="d-block mb-1">Please fix the following:</strong>
                        <ul class="mb-0 ps-3 small">
                            @foreach($errors->all() as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @include('admin.delivery.partials.stepper', ['tab' => $tab])

                <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" id="driverWizardForm" novalidate>
                    @csrf
                    <input type="hidden" name="driver_tab" id="driver_tab" value="{{ $tab }}">

                    <div class="driver-wizard-pane {{ $tab === 'personal' ? '' : 'd-none' }}" data-pane="personal">
                        <div class="figma-form-block">
                            <h6>Personal Information</h6>
                            <hr class="figma-section-rule">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ $val('name') }}" placeholder="Enter full name" maxlength="100">
                                    @include('admin.partials.field-error', ['field' => 'name'])
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                    <input type="text" name="mobile" class="form-control @error('mobile') is-invalid @enderror" value="{{ $val('mobile') }}" placeholder="Enter mobile number" maxlength="10" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)">
                                    @include('admin.partials.field-error', ['field' => 'mobile'])
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ $val('email') }}" placeholder="Enter email address" maxlength="150" required>
                                    @include('admin.partials.field-error', ['field' => 'email'])
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Full Address <span class="text-danger">*</span></label>
                                    <textarea name="address" rows="3" class="form-control @error('address') is-invalid @enderror" placeholder="Enter complete residential address">{{ $val('address') }}</textarea>
                                    @include('admin.partials.field-error', ['field' => 'address'])
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">City <span class="text-danger">*</span></label>
                                    <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ $val('city') }}" placeholder="Enter city" maxlength="100">
                                    @include('admin.partials.field-error', ['field' => 'city'])
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Profile Picture</label>
                                    <input type="file" name="profile_image" class="form-control @error('profile_image') is-invalid @enderror" accept="image/jpeg,image/png,image/webp">
                                    @include('admin.partials.field-error', ['field' => 'profile_image'])
                                    @if($isEdit && !empty($driver->profile_image))
                                        <small class="d-block mt-1"><a href="{{ asset('public/uploads/drivers/' . $driver->profile_image) }}" target="_blank" rel="noopener">View current photo</a></small>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Document Type <span class="text-danger">*</span></label>
                                    <select name="document_type" id="document_type" class="form-select @error('document_type') is-invalid @enderror" required>
                                        <option value="aadhar" {{ $documentType === 'aadhar' ? 'selected' : '' }}>Aadhaar</option>
                                        <option value="pan" {{ $documentType === 'pan' ? 'selected' : '' }}>PAN</option>
                                    </select>
                                    @include('admin.partials.field-error', ['field' => 'document_type'])
                                </div>
                                <div class="col-md-6" id="panDocumentFields" style="{{ $documentType === 'pan' ? '' : 'display:none;' }}">
                                    <label class="form-label">PAN Card Image <span class="text-danger">{{ (!$isEdit || !$hasPan) ? '*' : '' }}</span></label>
                                    <input type="file" name="identity_document" id="identity_document" class="form-control @error('identity_document') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp,.pdf">
                                    @if($isEdit && $hasPan)
                                        <small class="d-block mt-1"><a href="{{ asset('public/uploads/drivers/' . $profile->pan_card) }}" target="_blank" rel="noopener">View current PAN</a></small>
                                    @endif
                                    @include('admin.partials.field-error', ['field' => 'identity_document'])
                                </div>
                                <div class="col-md-6 aadhar-document-fields" style="{{ $documentType === 'aadhar' ? '' : 'display:none;' }}">
                                    <label class="form-label">Aadhaar Front <span class="text-danger">{{ (!$isEdit || !$hasAadharFront) ? '*' : '' }}</span></label>
                                    <input type="file" name="aadhar_card" id="aadhar_card" class="form-control @error('aadhar_card') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp,.pdf">
                                    @if($isEdit && $hasAadharFront)
                                        <small class="d-block mt-1"><a href="{{ asset('public/uploads/drivers/' . $profile->aadhar_card) }}" target="_blank" rel="noopener">View front</a></small>
                                    @endif
                                    @include('admin.partials.field-error', ['field' => 'aadhar_card'])
                                </div>
                                <div class="col-md-6 aadhar-document-fields" style="{{ $documentType === 'aadhar' ? '' : 'display:none;' }}">
                                    <label class="form-label">Aadhaar Back <span class="text-danger">{{ (!$isEdit || !$hasAadharBack) ? '*' : '' }}</span></label>
                                    <input type="file" name="aadhar_card_back" id="aadhar_card_back" class="form-control @error('aadhar_card_back') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp,.pdf">
                                    @if($isEdit && $hasAadharBack)
                                        <small class="d-block mt-1"><a href="{{ asset('public/uploads/drivers/' . $profile->aadhar_card_back) }}" target="_blank" rel="noopener">View back</a></small>
                                    @endif
                                    @include('admin.partials.field-error', ['field' => 'aadhar_card_back'])
                                </div>
                                @if(!$isEdit)
                                    <div class="col-md-6">
                                        <label class="form-label">Password <span class="text-danger">*</span></label>
                                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                                        @include('admin.partials.field-error', ['field' => 'password'])
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                        <input type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" autocomplete="new-password">
                                        @include('admin.partials.field-error', ['field' => 'password_confirmation'])
                                    </div>
                                @else
                                    <div class="col-md-6">
                                        <label class="form-label">New Password</label>
                                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                                        @include('admin.partials.field-error', ['field' => 'password'])
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Confirm Password</label>
                                        <input type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" autocomplete="new-password">
                                        @include('admin.partials.field-error', ['field' => 'password_confirmation'])
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="driver-wizard-pane {{ $tab === 'vehicle' ? '' : 'd-none' }}" data-pane="vehicle">
                        <div class="figma-form-block">
                            <h6>Vehicle Information</h6>
                            <hr class="figma-section-rule">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Vehicle Registration Number <span class="text-danger">*</span></label>
                                    <input type="text" name="vehicle_number" class="form-control @error('vehicle_number') is-invalid @enderror" value="{{ $val('vehicle_number') }}" placeholder="e.g. KA 01 AB 1234" maxlength="20">
                                    @include('admin.partials.field-error', ['field' => 'vehicle_number'])
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Driving License Number <span class="text-danger">*</span></label>
                                    <input type="text" name="driving_license_number" class="form-control @error('driving_license_number') is-invalid @enderror" value="{{ $val('driving_license_number') }}" placeholder="Enter DL number" maxlength="50">
                                    @include('admin.partials.field-error', ['field' => 'driving_license_number'])
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Upload Driving License (Front) <span class="text-danger">*</span></label>
                                    <div class="driver-file-drop">
                                        <input type="file" name="driving_license" id="driving_license" class="form-control @error('driving_license') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp,.pdf">
                                        <small class="text-muted">Accepted: JPG, PNG, PDF</small>
                                        @if($isEdit && !empty($profile?->driving_license))
                                            <small class="d-block mt-1"><a href="{{ asset('public/uploads/drivers/' . $profile->driving_license) }}" target="_blank" rel="noopener">View {{ basename($profile->driving_license) }}</a></small>
                                        @endif
                                        @include('admin.partials.field-error', ['field' => 'driving_license'])
                                    </div>
                                </div>
                                <div class="col-12">
                                    <hr class="figma-section-rule my-1">
                                    <label class="form-label d-block mb-2">PUC Details <span class="text-muted fw-normal">(Optional)</span></label>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">PUC Number</label>
                                    <input type="text" name="puc_number" id="puc_number" class="form-control @error('puc_number') is-invalid @enderror" value="{{ $val('puc_number') }}" placeholder="Enter PUC number" maxlength="50">
                                    @include('admin.partials.field-error', ['field' => 'puc_number'])
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">PUC Expiry Date</label>
                                    <input type="date" name="puc_expiry_date" id="puc_expiry_date" class="form-control @error('puc_expiry_date') is-invalid @enderror" value="{{ $val('puc_expiry_date') }}">
                                    @include('admin.partials.field-error', ['field' => 'puc_expiry_date'])
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">PUC Certificate</label>
                                    <input type="file" name="puc_image" id="puc_image" class="form-control @error('puc_image') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp,.pdf">
                                    @if($isEdit && !empty($profile?->puc_image))
                                        <small class="d-block mt-1"><a href="{{ asset('public/uploads/drivers/' . $profile->puc_image) }}" target="_blank" rel="noopener">View PUC document</a></small>
                                    @endif
                                    @include('admin.partials.field-error', ['field' => 'puc_image'])
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="driver-wizard-pane {{ $tab === 'bank' ? '' : 'd-none' }}" data-pane="bank">
                        <div class="figma-form-block">
                            <h6>Bank Details</h6>
                            <hr class="figma-section-rule">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Account Holder Name <span class="text-danger">*</span></label>
                                    <input type="text" name="account_holder_name" class="form-control @error('account_holder_name') is-invalid @enderror" value="{{ $val('account_holder_name') }}" placeholder="Enter account holder name" maxlength="150">
                                    @include('admin.partials.field-error', ['field' => 'account_holder_name'])
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror" value="{{ $val('bank_name') }}" placeholder="Enter bank name" maxlength="150">
                                    @include('admin.partials.field-error', ['field' => 'bank_name'])
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Branch Name <span class="text-danger">*</span></label>
                                    <input type="text" name="branch_name" class="form-control @error('branch_name') is-invalid @enderror" value="{{ $val('branch_name') }}" placeholder="Enter branch name" maxlength="150">
                                    @include('admin.partials.field-error', ['field' => 'branch_name'])
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Account Number <span class="text-danger">*</span></label>
                                    <input type="text" name="account_number" class="form-control @error('account_number') is-invalid @enderror" value="{{ $val('account_number') }}" placeholder="Enter account number" maxlength="30" oninput="this.value=this.value.replace(/\D/g,'')">
                                    @include('admin.partials.field-error', ['field' => 'account_number'])
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">IFSC Code <span class="text-danger">*</span></label>
                                    <input type="text" name="ifsc_code" class="form-control text-uppercase @error('ifsc_code') is-invalid @enderror" value="{{ $val('ifsc_code') }}" placeholder="Enter IFSC code" maxlength="11" oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9]/g,'').slice(0,11)">
                                    @include('admin.partials.field-error', ['field' => 'ifsc_code'])
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Account Type <span class="text-danger">*</span></label>
                                    <select name="account_type" class="form-select @error('account_type') is-invalid @enderror">
                                        <option value="">Select account type</option>
                                        <option value="savings" {{ $accountType === 'savings' ? 'selected' : '' }}>Saving Account</option>
                                        <option value="current" {{ $accountType === 'current' ? 'selected' : '' }}>Current Account</option>
                                    </select>
                                    @include('admin.partials.field-error', ['field' => 'account_type'])
                                </div>
                                @if(!$isEdit)
                                    <div class="col-md-6">
                                        <label class="form-label">Approval Status</label>
                                        <select name="approval_status" class="form-select @error('approval_status') is-invalid @enderror">
                                            <option value="pending" {{ old('approval_status', 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="approved" {{ old('approval_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                            <option value="rejected" {{ old('approval_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        </select>
                                    </div>
                                @else
                                    <div class="col-md-6">
                                        <label class="form-label">Approval Status</label>
                                        @php
                                            $approvalVal = old('approval_status', $driver->approval_status ?? 'pending');
                                        @endphp
                                        <select name="approval_status" class="form-select @error('approval_status') is-invalid @enderror">
                                            <option value="pending" {{ $approvalVal === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="approved" {{ $approvalVal === 'approved' ? 'selected' : '' }}>Approved</option>
                                            <option value="rejected" {{ $approvalVal === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        </select>
                                        @include('admin.partials.field-error', ['field' => 'approval_status'])
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center gap-2 mt-4 pt-2">
                        <div>
                            <button type="button" class="btn btn-outline-secondary {{ $tab === 'personal' ? 'd-none' : '' }}" id="driverWizardBack">Back</button>
                        </div>
                        <div class="d-flex gap-2 ms-auto">
                            @if($isEdit)
                                <button type="submit" class="btn btn-outline-primary {{ $tab === 'bank' ? 'd-none' : '' }}" id="driverWizardSavePartial">
                                    <i class="ri-save-line me-1"></i>Save Changes
                                </button>
                            @endif
                            <button type="submit" class="btn {{ $isEdit ? 'btn-success' : 'btn-figma-primary' }} {{ $tab === 'bank' ? '' : 'd-none' }}" id="driverWizardSubmit">
                                <i class="ri-save-line me-1"></i>{{ $isEdit ? 'Save Changes' : 'Submit & Add Driver' }}
                            </button>
                            <button type="button" class="btn btn-figma-primary {{ $tab === 'bank' ? 'd-none' : '' }}" id="driverWizardNext">
                                <span id="driverWizardNextText">Next: Vehicle Info</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.driver-file-drop {
    border: 1px dashed #d1d5db;
    border-radius: 10px;
    padding: 16px;
    background: #fafafa;
    min-height: 120px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var stepKeys = @json($stepKeys);
    var currentIndex = {{ (int) $currentIndex }};
    var tabInput = document.getElementById('driver_tab');
    var panes = document.querySelectorAll('.driver-wizard-pane');
    var steps = document.querySelectorAll('#driverStepper .figma-step');
    var nextBtn = document.getElementById('driverWizardNext');
    var backBtn = document.getElementById('driverWizardBack');
    var submitBtn = document.getElementById('driverWizardSubmit');
    var savePartialBtn = document.getElementById('driverWizardSavePartial');
    var nextBtnText = document.getElementById('driverWizardNextText');

    function showStep(index) {
        if (index < 0 || index >= stepKeys.length) {
            return;
        }
        currentIndex = index;
        var tab = stepKeys[currentIndex];
        if (tabInput) {
            tabInput.value = tab;
        }
        
        // Toggle panes
        panes.forEach(function (pane) {
            pane.classList.toggle('d-none', pane.dataset.pane !== tab);
        });
        
        // Update stepper
        steps.forEach(function (step, idx) {
            step.classList.remove('active', 'done');
            if (idx < currentIndex) {
                step.classList.add('done');
            } else if (idx === currentIndex) {
                step.classList.add('active');
            }
        });
        
        // Update buttons
        var isLastStep = currentIndex === stepKeys.length - 1;
        var isFirstStep = currentIndex === 0;
        
        // Back button - hide on first step
        if (backBtn) {
            backBtn.classList.toggle('d-none', isFirstStep);
        }
        
        // Submit button - show only on last step
        if (submitBtn) {
            submitBtn.classList.toggle('d-none', !isLastStep);
        }
        
        // Next button - hide on last step
        if (nextBtn) {
            nextBtn.classList.toggle('d-none', isLastStep);
            
            // Update next button text
            if (nextBtnText) {
                if (currentIndex === 0) {
                    nextBtnText.textContent = 'Next: Vehicle Info';
                } else if (currentIndex === 1) {
                    nextBtnText.textContent = 'Next: Bank Details';
                }
            }
        }
        
        // Save partial button (edit mode only) - hide on last step
        if (savePartialBtn) {
            savePartialBtn.classList.toggle('d-none', isLastStep);
        }
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            showStep(currentIndex + 1);
        });
    }
    if (backBtn) {
        backBtn.addEventListener('click', function () {
            showStep(currentIndex - 1);
        });
    }

    var documentType = document.getElementById('document_type');
    var panFields = document.getElementById('panDocumentFields');
    var aadharFields = document.querySelectorAll('.aadhar-document-fields');

    function syncDocumentFields() {
        var isPan = documentType && documentType.value === 'pan';
        if (panFields) {
            panFields.style.display = isPan ? '' : 'none';
        }
        aadharFields.forEach(function (el) {
            el.style.display = isPan ? 'none' : '';
        });
    }

    if (documentType) {
        documentType.addEventListener('change', syncDocumentFields);
        syncDocumentFields();
    }

    var form = document.getElementById('driverWizardForm');
    if (form) {
        form.addEventListener('submit', function () {
            syncDocumentFields();
            if (tabInput) {
                tabInput.value = stepKeys[currentIndex];
            }
            var isPan = documentType && documentType.value === 'pan';
            var panInput = document.getElementById('identity_document');
            var aadharFront = document.getElementById('aadhar_card');
            var aadharBack = document.getElementById('aadhar_card_back');
            if (panInput) {
                panInput.disabled = !isPan;
            }
            if (aadharFront) {
                aadharFront.disabled = isPan;
            }
            if (aadharBack) {
                aadharBack.disabled = isPan;
            }
        });
    }
});
</script>
