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
        $fromProfile = ['address', 'city', 'vehicle_number', 'driving_license_number', 'account_holder_name', 'bank_name', 'branch_name', 'account_number', 'ifsc_code', 'account_type'];
        if (in_array($key, $fromProfile, true)) {
            return old($key, $profile?->{$key} ?? $default);
        }
        if (in_array($key, ['name', 'mobile', 'email'], true)) {
            return old($key, $driver?->{$key} ?? $default);
        }

        return old($key, $default);
    };
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
                                    <label class="form-label">Email Address (Optional)</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ $val('email') }}" placeholder="Enter email address" maxlength="150">
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
                                    <label class="form-label d-block">Upload Driving License <span class="text-danger">*</span></label>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="driver-file-drop">
                                                <label class="form-label small mb-1">Upload Front Side</label>
                                                <input type="file" name="driving_license" id="driving_license" class="form-control @error('driving_license') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                                                <small class="text-muted">Accepted: JPG, PNG, PDF</small>
                                                @if($isEdit && !empty($profile?->driving_license))
                                                    <small class="d-block mt-1"><a href="{{ asset('public/uploads/drivers/' . $profile->driving_license) }}" target="_blank" rel="noopener">View {{ basename($profile->driving_license) }}</a></small>
                                                @endif
                                                @include('admin.partials.field-error', ['field' => 'driving_license'])
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="driver-file-drop">
                                                <label class="form-label small mb-1">Upload Back Side</label>
                                                <input type="file" name="driving_license_back" id="driving_license_back" class="form-control @error('driving_license_back') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                                                <small class="text-muted">Accepted: JPG, PNG, PDF</small>
                                                @if($isEdit && !empty($profile?->driving_license_back))
                                                    <small class="d-block mt-1"><a href="{{ asset('public/uploads/drivers/' . $profile->driving_license_back) }}" target="_blank" rel="noopener">View {{ basename($profile->driving_license_back) }}</a></small>
                                                @elseif($isEdit && !empty($profile?->driving_license) && empty($profile?->driving_license_back))
                                                    <small class="d-block mt-1 text-muted">Back side not uploaded yet — please add if available.</small>
                                                @endif
                                                @include('admin.partials.field-error', ['field' => 'driving_license_back'])
                                            </div>
                                        </div>
                                    </div>
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
                                        </select>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center gap-2 mt-4 pt-2">
                        <div>
                            @if($tab !== 'personal')
                                <button type="button" class="btn btn-outline-secondary" id="driverWizardBack">Back</button>
                            @endif
                        </div>
                        <div class="d-flex gap-2 ms-auto">
                            @if($isEdit && $tab !== 'bank')
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="ri-save-line me-1"></i>Save Changes
                                </button>
                            @endif
                            @if($tab === 'bank')
                                <button type="submit" class="btn {{ $isEdit ? 'btn-success' : 'btn-figma-primary' }}">
                                    <i class="ri-save-line me-1"></i>{{ $isEdit ? 'Save Changes' : 'Submit & Add Driver' }}
                                </button>
                            @else
                                <button type="button" class="btn btn-figma-primary" id="driverWizardNext">
                                    @if($tab === 'personal')
                                        Next: Vehicle Info
                                    @else
                                        Next: Bank Details
                                    @endif
                                </button>
                            @endif
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

    function showStep(index) {
        if (index < 0 || index >= stepKeys.length) {
            return;
        }
        currentIndex = index;
        var tab = stepKeys[currentIndex];
        if (tabInput) {
            tabInput.value = tab;
        }
        panes.forEach(function (pane) {
            pane.classList.toggle('d-none', pane.dataset.pane !== tab);
        });
        steps.forEach(function (step, idx) {
            step.classList.remove('active', 'done');
            if (idx < currentIndex) {
                step.classList.add('done');
            } else if (idx === currentIndex) {
                step.classList.add('active');
            }
        });
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
});
</script>
