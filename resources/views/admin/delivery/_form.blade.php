@php
    $isEdit = !empty($driver);
    $formAction = $isEdit ? route('admin.delivery.update', $driver->user_id) : route('admin.delivery.store');
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
                        <p class="text-muted">Driver contact and profile details</p>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Profile Picture</label>
                                <input type="file" name="profile_image" class="form-control" accept="image/*">
                                @if($isEdit && !empty($driver->profile_image))
                                    <small><a href="{{ asset('public/uploads/drivers/' . $driver->profile_image) }}" target="_blank">View current photo</a></small>
                                @else
                                    <small class="text-muted">Clear, front-facing photo. Max 2MB.</small>
                                @endif
                            </div>
                            <div class="col-md-9">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name', $driver->name ?? '') }}" maxlength="100" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control" value="{{ old('email', $driver->email ?? '') }}" maxlength="150" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Mobile No. <span class="text-danger">*</span></label>
                                        <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $driver->mobile ?? '') }}" maxlength="10" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">City <span class="text-danger">*</span></label>
                                        <input type="text" name="city" class="form-control" value="{{ old('city', $profile->city ?? '') }}" maxlength="100" required>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Full Address <span class="text-danger">*</span></label>
                                        <input type="text" name="address" class="form-control" value="{{ old('address', $profile->address ?? '') }}" maxlength="500" required>
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

                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="form-section h-100">
                                <h6>Vehicle Details</h6>
                                <p class="text-muted">Driver's assigned vehicle info</p>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Vehicle Registration No. <span class="text-danger">*</span></label>
                                        <input type="text" name="vehicle_number" class="form-control" value="{{ old('vehicle_number', $profile->vehicle_number ?? '') }}" maxlength="20" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Driving License Number <span class="text-danger">*</span></label>
                                        <input type="text" name="driving_license_number" class="form-control" value="{{ old('driving_license_number', $profile->driving_license_number ?? '') }}" maxlength="50" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Vehicle Type</label>
                                        <select name="vehicle_type" class="form-select">
                                            @foreach(['Bike','Scooter','Car','Van','Truck','Other'] as $type)
                                                <option value="{{ $type }}" {{ old('vehicle_type', $profile->vehicle_type ?? 'Bike') === $type ? 'selected' : '' }}>{{ $type }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Upload Driving License</label>
                                        <input type="file" name="driving_license" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf">
                                        @if($isEdit && !empty($profile?->driving_license))
                                            <small><a href="{{ asset('public/uploads/drivers/' . $profile->driving_license) }}" target="_blank">View current license</a></small>
                                        @endif
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Upload Aadhaar Card</label>
                                        <input type="file" name="aadhar_card" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf">
                                        @if($isEdit && !empty($profile?->aadhar_card))
                                            <small><a href="{{ asset('public/uploads/drivers/' . $profile->aadhar_card) }}" target="_blank">View current Aadhaar</a></small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-section h-100">
                                <h6>Bank Details</h6>
                                <p class="text-muted">Payment routing info for driver</p>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Bank Account Name <span class="text-danger">*</span></label>
                                        <input type="text" name="account_holder_name" class="form-control" value="{{ old('account_holder_name', $profile->account_holder_name ?? '') }}" maxlength="150" required>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Account Number <span class="text-danger">*</span></label>
                                        <input type="text" name="account_number" class="form-control" value="{{ old('account_number', $profile->account_number ?? '') }}" maxlength="30" oninput="this.value=this.value.replace(/\D/g,'')" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                                        <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $profile->bank_name ?? '') }}" maxlength="150" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Branch Name</label>
                                        <input type="text" name="branch_name" class="form-control" value="{{ old('branch_name', $profile->branch_name ?? '') }}" maxlength="150">
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
                                        <div class="col-md-12">
                                            <label class="form-label">Approval Status</label>
                                            <select name="approval_status" class="form-select">
                                                <option value="pending" {{ old('approval_status', 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="approved" {{ old('approval_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                            </select>
                                        </div>
                                    @endif
                                </div>
                            </div>
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
