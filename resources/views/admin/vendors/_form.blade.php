@php
    $isEdit = !empty($vendor);
    $tab = $tab ?? 'personal';
    $wizard = $wizard ?? [];
    $formAction = $isEdit ? route('admin.update-vendor', $vendor->vendor_id) : route('admin.store-vendor');
    $stepKeys = ['personal', 'business', 'bank', 'documents'];
    $currentIndex = array_search($tab, $stepKeys, true);
    if ($currentIndex === false) {
        $currentIndex = 0;
    }
    $val = function (string $key, $default = '') use ($wizard, $vendor) {
        return old($key, $wizard[$key] ?? ($vendor->{$key} ?? $default));
    };
@endphp

@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        <div class="card dashboard-card">
            <div class="card-body p-4">
                @php
                    $resCode = $isEdit ? 'RES-' . str_pad((string) $vendor->vendor_id, 3, '0', STR_PAD_LEFT) : null;
                @endphp
                <div class="vendor-wizard-head d-flex align-items-start gap-3">
                    <a href="{{ route('admin.vendors') }}" class="btn-back-figma" title="Back"><i class="ri-arrow-left-line"></i></a>
                    <div>
                        <h4 class="figma-page-title mb-1">
                            {{ $isEdit ? 'Edit Restaurant' : 'Add New Restaurant' }}
                            @if($isEdit)<span class="vendor-code-accent">#{{ $resCode }}</span>@endif
                        </h4>
                        <p class="figma-page-subtitle mb-0">{{ $isEdit ? 'Update the details for this restaurant' : 'Fill in the details to register a new restaurant on the platform' }}</p>
                    </div>
                </div>

                @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Please fix the highlighted fields below and try again.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @include('admin.vendors.partials.stepper', ['tab' => $tab, 'isEdit' => $isEdit, 'vendor' => $vendor])

                <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" id="vendorWizardForm" novalidate>
                    @csrf
                    <input type="hidden" name="tab" value="{{ $tab }}">

                    @if($tab === 'personal')
                        <div class="figma-form-block">
                            <h6>Personal Information</h6>
                            <hr class="figma-section-rule">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Owner Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="owner_name" class="form-control @error('owner_name') is-invalid @enderror" value="{{ $val('owner_name') }}" placeholder="Enter owner name" maxlength="100" autocomplete="name">
                                    @include('admin.partials.field-error', ['field' => 'owner_name'])
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                    <input type="text" name="mobile" class="form-control @error('mobile') is-invalid @enderror" value="{{ $val('mobile') }}" maxlength="10" inputmode="numeric" placeholder="Enter mobile number" autocomplete="tel" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)">
                                    @include('admin.partials.field-error', ['field' => 'mobile'])
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Email Address (Optional)</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ $val('email') }}" placeholder="Enter email address" maxlength="255" autocomplete="email">
                                    @include('admin.partials.field-error', ['field' => 'email'])
                                </div>
                            </div>
                            <div class="figma-form-extra">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">DOB</label>
                                    @php
                                        $dobDefault = '';
                                        if (!empty($wizard['dob'])) {
                                            $dobDefault = $wizard['dob'];
                                        } elseif (!empty($vendor?->dob)) {
                                            $dobDefault = \Illuminate\Support\Carbon::parse($vendor->dob)->format('Y-m-d');
                                        }
                                    @endphp
                                    <input type="date" name="dob" class="form-control @error('dob') is-invalid @enderror" value="{{ old('dob', $dobDefault) }}">
                                    @include('admin.partials.field-error', ['field' => 'dob'])
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Gender</label>
                                    <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                                        <option value="">Select</option>
                                        @foreach(['male' => 'Male', 'female' => 'Female', 'others' => 'Others'] as $genderKey => $genderLabel)
                                            <option value="{{ $genderKey }}" {{ $val('gender') === $genderKey ? 'selected' : '' }}>{{ $genderLabel }}</option>
                                        @endforeach
                                    </select>
                                    @include('admin.partials.field-error', ['field' => 'gender'])
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address <span class="text-danger">*</span></label>
                                    <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ $val('address') }}" placeholder="Enter full address" maxlength="500" autocomplete="street-address">
                                    @include('admin.partials.field-error', ['field' => 'address'])
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Profile Image</label>
                                    <input type="file" name="profile_image" class="form-control @error('profile_image') is-invalid @enderror" accept="image/jpeg,image/png,image/webp">
                                    @include('admin.partials.field-error', ['field' => 'profile_image'])
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
                                @endif
                            </div>
                            </div>
                        </div>
                    @elseif($tab === 'business')
                        <div class="figma-form-block">
                            <h6>Business Information</h6>
                            <hr class="figma-section-rule">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Restaurant Name <span class="text-danger">*</span></label>
                                    <input type="text" name="business_name" class="form-control @error('business_name') is-invalid @enderror" value="{{ $val('business_name') }}" placeholder="Enter restaurant name" maxlength="150">
                                    @include('admin.partials.field-error', ['field' => 'business_name'])
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Business Phone</label>
                                    <input type="text" name="business_phone" class="form-control @error('business_phone') is-invalid @enderror" value="{{ $val('business_phone') }}" maxlength="10" inputmode="numeric" placeholder="10-digit mobile" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)">
                                    @include('admin.partials.field-error', ['field' => 'business_phone'])
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Business Email</label>
                                    <input type="email" name="business_email" class="form-control @error('business_email') is-invalid @enderror" value="{{ $val('business_email') }}" maxlength="255" placeholder="business@example.com">
                                    @include('admin.partials.field-error', ['field' => 'business_email'])
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">GSTIN</label>
                                    <input type="text" name="gst_number" class="form-control @error('gst_number') is-invalid @enderror" value="{{ $val('gst_number') }}" maxlength="15" placeholder="15-character GSTIN" oninput="this.value=this.value.toUpperCase()">
                                    @include('admin.partials.field-error', ['field' => 'gst_number'])
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">PAN Number</label>
                                    <input type="text" name="pan_number" class="form-control @error('pan_number') is-invalid @enderror" value="{{ $val('pan_number') }}" maxlength="10" placeholder="ABCDE1234F" oninput="this.value=this.value.toUpperCase()">
                                    @include('admin.partials.field-error', ['field' => 'pan_number'])
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Business Description</label>
                                    <textarea name="business_description" class="form-control @error('business_description') is-invalid @enderror" rows="2" maxlength="2000">{{ $val('business_description') }}</textarea>
                                    @include('admin.partials.field-error', ['field' => 'business_description'])
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Latitude</label>
                                    <input type="text" name="latitude" class="form-control @error('latitude') is-invalid @enderror" value="{{ $val('latitude') }}">
                                    @include('admin.partials.field-error', ['field' => 'latitude'])
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Longitude</label>
                                    <input type="text" name="longitude" class="form-control @error('longitude') is-invalid @enderror" value="{{ $val('longitude') }}">
                                    @include('admin.partials.field-error', ['field' => 'longitude'])
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tax Name</label>
                                    <input type="text" name="tax_name" class="form-control @error('tax_name') is-invalid @enderror" value="{{ $val('tax_name') }}" maxlength="100">
                                    @include('admin.partials.field-error', ['field' => 'tax_name'])
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tax Number</label>
                                    <input type="text" name="tax_number" class="form-control @error('tax_number') is-invalid @enderror" value="{{ $val('tax_number') }}" maxlength="50">
                                    @include('admin.partials.field-error', ['field' => 'tax_number'])
                                </div>
                            </div>
                        </div>
                    @elseif($tab === 'bank')
                        <div class="figma-form-block">
                            <h6>Bank Details</h6>
                            <hr class="figma-section-rule">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Bank Name</label>
                                    <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror" value="{{ $val('bank_name') }}" maxlength="150">
                                    @include('admin.partials.field-error', ['field' => 'bank_name'])
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Branch Name</label>
                                    <input type="text" name="branch_name" class="form-control @error('branch_name') is-invalid @enderror" value="{{ $val('branch_name') }}" maxlength="150">
                                    @include('admin.partials.field-error', ['field' => 'branch_name'])
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Account Number</label>
                                    <input type="text" name="bank_account" class="form-control @error('bank_account') is-invalid @enderror" value="{{ $val('bank_account') }}" maxlength="18" oninput="this.value=this.value.replace(/\D/g,'')">
                                    @include('admin.partials.field-error', ['field' => 'bank_account'])
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">IFSC Code</label>
                                    <input type="text" name="ifsc_code" class="form-control @error('ifsc_code') is-invalid @enderror" value="{{ $val('ifsc_code') }}" maxlength="11" oninput="this.value=this.value.toUpperCase()">
                                    @include('admin.partials.field-error', ['field' => 'ifsc_code'])
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Account Type</label>
                                    <input type="text" name="account_type" class="form-control @error('account_type') is-invalid @enderror" value="{{ $val('account_type') }}" maxlength="50" placeholder="Savings / Current">
                                    @include('admin.partials.field-error', ['field' => 'account_type'])
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Account Holder Name</label>
                                    <input type="text" name="account_holder_name" class="form-control @error('account_holder_name') is-invalid @enderror" value="{{ $val('account_holder_name') }}" maxlength="150">
                                    @include('admin.partials.field-error', ['field' => 'account_holder_name'])
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Commission %</label>
                                    <input type="number" step="0.01" min="0" max="100" name="commission_percent" class="form-control @error('commission_percent') is-invalid @enderror" value="{{ $val('commission_percent', 15) }}">
                                    @include('admin.partials.field-error', ['field' => 'commission_percent'])
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="figma-form-block">
                            <h6>Documents Information</h6>
                            <hr class="figma-section-rule">
                            <p class="text-muted small mb-3">Upload verification documents (JPG, PNG or PDF, max 4MB each)</p>
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
                                        <input type="file" name="{{ $field }}" class="form-control @error($field) is-invalid @enderror" accept="image/jpeg,image/png,image/webp,application/pdf">
                                        @include('admin.partials.field-error', ['field' => $field])
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

                    <div class="d-flex justify-content-between align-items-center gap-2 mt-4 pt-2">
                        @if($tab !== 'personal')
                            <a href="{{ $isEdit ? route('admin.edit-vendor', ['id' => $vendor->vendor_id, 'tab' => $stepKeys[max(0, $currentIndex - 1)] ?? 'personal']) : route('admin.add-vendor', ['tab' => $stepKeys[max(0, $currentIndex - 1)] ?? 'personal']) }}" class="btn btn-outline-secondary">Back</a>
                        @else
                            <span class="d-none d-md-block"></span>
                        @endif
                        @if($tab === 'documents')
                            <button type="submit" name="wizard_action" value="submit" class="btn btn-figma-primary">
                                <i class="ri-save-line me-1"></i>{{ $isEdit ? 'Save Changes' : 'Submit & Add Restaurant' }}
                            </button>
                        @else
                            <button type="submit" name="wizard_action" value="next" class="btn btn-figma-primary">
                                @if($tab === 'personal')
                                    Next: Business Info
                                @elseif($tab === 'business')
                                    Next: Bank Details
                                @else
                                    Next: Documents
                                @endif
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
