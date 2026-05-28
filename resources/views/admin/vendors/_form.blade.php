@php
    $isEdit = !empty($vendor);
    $tab = $tab ?? 'personal';
    $formAction = $isEdit ? route('admin.update-vendor', $vendor->vendor_id) : route('admin.store-vendor');
@endphp

<div class="page-body">
    <div class="container-fluid">
        <div class="card dashboard-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <a href="{{ route('admin.vendors') }}" class="btn btn-outline-secondary btn-sm me-2"><i class="ri-arrow-left-line"></i></a>
                    <div>
                        <h5 class="mb-0">{{ $isEdit ? 'Edit Vendor' : 'Add New Vendor' }}</h5>
                        <small class="text-muted">{{ $isEdit ? 'Update details for vendor ' . ($vendor->vendor_code ?? '') : 'Register a new vendor' }}</small>
                    </div>
                </div>

                @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
                @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

                <ul class="nav nav-tabs vendor-form-tabs mb-4">
                    @foreach(['personal' => 'Personal Info', 'business' => 'Business Info', 'bank' => 'Bank Details', 'documents' => 'Documents'] as $key => $label)
                        <li class="nav-item">
                            <a class="nav-link {{ $tab === $key ? 'active' : '' }}"
                               href="{{ $isEdit ? route('admin.edit-vendor', ['id' => $vendor->vendor_id, 'tab' => $key]) : route('admin.add-vendor', ['tab' => $key]) }}">{{ $label }}</a>
                        </li>
                    @endforeach
                </ul>

                <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="tab" value="{{ $tab }}">

                    @if($isEdit && $tab !== 'personal')
                        {{-- Include required fields from personal tab as hidden inputs when on other tabs --}}
                        <input type="hidden" name="owner_name" value="{{ $vendor->owner_name ?? '' }}">
                        <input type="hidden" name="mobile" value="{{ $vendor->mobile ?? '' }}">
                        <input type="hidden" name="email" value="{{ $vendor->email ?? '' }}">
                        <input type="hidden" name="address" value="{{ $vendor->address ?? '' }}">
                    @endif

                    @if($isEdit && $tab !== 'business')
                        {{-- Include required fields from business tab as hidden inputs when on other tabs --}}
                        <input type="hidden" name="business_name" value="{{ $vendor->business_name ?? '' }}">
                    @endif

                    @if($tab === 'personal')
                        <div class="form-section">
                            <h6>Personal Information</h6>
                            <p class="text-muted">Vendor's contact details</p>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Vendor Name</label>
                                    <input type="text" name="owner_name" class="form-control" value="{{ old('owner_name', $vendor->owner_name ?? '') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Mobile No.</label>
                                    <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $vendor->mobile ?? '') }}" required maxlength="15">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $vendor->email ?? '') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">DOB</label>
                                    <input type="date" name="dob" class="form-control" value="{{ old('dob', isset($vendor->dob) ? \Illuminate\Support\Carbon::parse($vendor->dob)->format('Y-m-d') : '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Gender</label>
                                    <select name="gender" class="form-control">
                                        <option value="">Select</option>
                                        @foreach(['male' => 'Male', 'female' => 'Female', 'others' => 'Others'] as $genderKey => $genderLabel)
                                            <option value="{{ $genderKey }}" {{ old('gender', $vendor->gender ?? '') === $genderKey ? 'selected' : '' }}>{{ $genderLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Address</label>
                                    <input type="text" name="address" class="form-control" value="{{ old('address', $vendor->address ?? '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Profile Image</label>
                                    <input type="file" name="profile_image" class="form-control" accept="image/*">
                                </div>
                                @if(!$isEdit)
                                    <div class="col-md-6">
                                        <label class="form-label">Password</label>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Confirm Password</label>
                                        <input type="password" name="password_confirmation" class="form-control" required>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @elseif($tab === 'business')
                        <div class="form-section">
                            <h6>Business Information</h6>
                            <p class="text-muted">Company and registration details</p>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Business Name</label>
                                    <input type="text" name="business_name" class="form-control" value="{{ old('business_name', $vendor->business_name ?? '') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Business Phone</label>
                                    <input type="text" name="business_phone" class="form-control" value="{{ old('business_phone', $vendor->business_phone ?? '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Business Email</label>
                                    <input type="email" name="business_email" class="form-control" value="{{ old('business_email', $vendor->business_email ?? '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">GSTIN</label>
                                    <input type="text" name="gst_number" class="form-control" value="{{ old('gst_number', $vendor->gst_number ?? '') }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Business Description</label>
                                    <textarea name="business_description" class="form-control" rows="2">{{ old('business_description', $vendor->business_description ?? '') }}</textarea>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Latitude</label>
                                    <input type="text" name="latitude" class="form-control" value="{{ old('latitude', $vendor->latitude ?? '') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Longitude</label>
                                    <input type="text" name="longitude" class="form-control" value="{{ old('longitude', $vendor->longitude ?? '') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tax Name</label>
                                    <input type="text" name="tax_name" class="form-control" value="{{ old('tax_name', $vendor->tax_name ?? '') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tax Number</label>
                                    <input type="text" name="tax_number" class="form-control" value="{{ old('tax_number', $vendor->tax_number ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">PAN Number</label>
                                    <input type="text" name="pan_number" class="form-control" value="{{ old('pan_number', $vendor->pan_number ?? '') }}">
                                </div>
                            </div>
                        </div>
                    @elseif($tab === 'bank')
                        <div class="form-section">
                            <h6>Bank Details</h6>
                            <p class="text-muted">Financial and payment routing details</p>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Bank Name</label>
                                    <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $vendor->bank_name ?? '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Branch Name</label>
                                    <input type="text" name="branch_name" class="form-control" value="{{ old('branch_name', $vendor->branch_name ?? '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Account Number</label>
                                    <input type="text" name="bank_account" class="form-control" value="{{ old('bank_account', $vendor->bank_account ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">IFSC Code</label>
                                    <input type="text" name="ifsc_code" class="form-control" value="{{ old('ifsc_code', $vendor->ifsc_code ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Account Type</label>
                                    <input type="text" name="account_type" class="form-control" value="{{ old('account_type', $vendor->account_type ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Account Holder Name</label>
                                    <input type="text" name="account_holder_name" class="form-control" value="{{ old('account_holder_name', $vendor->account_holder_name ?? '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Commission %</label>
                                    <input type="number" step="0.01" name="commission_percent" class="form-control" value="{{ old('commission_percent', $vendor->commission_percent ?? 15) }}">
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="form-section">
                            <h6>Documents</h6>
                            <p class="text-muted">Upload necessary verification documents</p>
                            <div class="row g-3">
                                @foreach([
                                    'aadhaar_card' => 'Aadhaar Card',
                                    'pan_card' => 'PAN Card',
                                    'gst_file' => 'GST File',
                                    'food_license_file' => 'Food/Gumasta License',
                                    'bank_passbook_file' => 'Bank Passbook',
                                    'address_proof_file' => 'Address Proof',
                                    'national_identity_card_file' => 'National Identity Card',
                                    'business_logo' => 'Shop Logo',
                                    'shop_image' => 'Shop Image',
                                    'business_banner' => 'Shop Banner',
                                ] as $field => $label)
                                    <div class="col-md-3">
                                        <label class="form-label">{{ $label }}</label>
                                        <input type="file" name="{{ $field }}" class="form-control" accept="image/*,.pdf">
                                        @if($isEdit && !empty($vendor->{$field}))
                                            @php
                                                $docPath = in_array($field, ['aadhaar_card', 'pan_card', 'gst_file', 'food_license_file', 'bank_passbook_file', 'address_proof_file', 'national_identity_card_file'], true)
                                                    ? 'public/uploads/vendors/documents/' . $vendor->{$field}
                                                    : 'public/uploads/vendors/' . $vendor->{$field};
                                            @endphp
                                            <small><a href="{{ asset($docPath) }}" target="_blank">View File</a></small>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('admin.vendors') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-theme">
                            <i class="ri-save-line me-1"></i>{{ $isEdit ? 'Save Changes' : 'Create Vendor' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
