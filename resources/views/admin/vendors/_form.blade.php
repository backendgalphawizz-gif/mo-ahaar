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
                                    <label class="form-label">GSTIN</label>
                                    <input type="text" name="gst_number" class="form-control" value="{{ old('gst_number', $vendor->gst_number ?? '') }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Business Address</label>
                                    <input type="text" name="address" class="form-control" value="{{ old('address', $vendor->address ?? '') }}">
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
                                    'business_logo' => 'Shop Logo',
                                    'shop_image' => 'Shop Image',
                                    'business_banner' => 'Shop Banner',
                                ] as $field => $label)
                                    <div class="col-md-3">
                                        <label class="form-label">{{ $label }}</label>
                                        <input type="file" name="{{ $field }}" class="form-control" accept="image/*,.pdf">
                                        @if($isEdit && !empty($vendor->{$field}))
                                            @php
                                                $docPath = in_array($field, ['aadhaar_card', 'pan_card'], true)
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
