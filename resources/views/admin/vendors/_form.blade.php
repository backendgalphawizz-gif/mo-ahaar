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
                        <strong class="d-block mb-1">Please fix the following:</strong>
                        <ul class="mb-0 ps-3 small">
                            @foreach($errors->all() as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                                <div class="col-12">
                                    <label class="form-label" for="vendor_business_address">Restaurant Address <span class="text-danger">*</span></label>
                                    <input type="text"
                                        id="vendor_business_address"
                                        name="address"
                                        class="form-control @error('address') is-invalid @enderror"
                                        value="{{ $val('address') }}"
                                        placeholder="Type address and select from Google suggestions"
                                        maxlength="500"
                                        autocomplete="off">
                                    @include('admin.partials.field-error', ['field' => 'address'])
                                    <small class="text-muted d-block mt-1">Suggestions से address चुनें — location automatically save होगी।</small>
                                    <small id="vendor-address-maps-help" class="text-warning d-none d-block mt-1">Google Maps API key (.env में GOOGLE_MAPS_API_KEY) configure नहीं है।</small>
                                    <small id="vendor-address-coords-error" class="text-danger d-none d-block mt-1">कृपया dropdown से valid address select करें (lat/long required)।</small>
                                    <input type="hidden" name="latitude" id="vendor_latitude" value="{{ $val('latitude') }}">
                                    <input type="hidden" name="longitude" id="vendor_longitude" value="{{ $val('longitude') }}">
                                    @include('admin.partials.field-error', ['field' => 'latitude'])
                                    @include('admin.partials.field-error', ['field' => 'longitude'])
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tax Name</label>
                                    <input type="text" name="tax_name" class="form-control @error('tax_name') is-invalid @enderror" value="{{ $val('tax_name') }}" maxlength="100">
                                    @include('admin.partials.field-error', ['field' => 'tax_name'])
                                </div>
                                <div class="col-md-6">
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
                            @php $adminAccountType = strtolower((string) $val('account_type', '')); @endphp
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror" value="{{ $val('bank_name') }}" maxlength="150">
                                    @include('admin.partials.field-error', ['field' => 'bank_name'])
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Account Holder Name <span class="text-danger">*</span></label>
                                    <input type="text" name="account_holder_name" class="form-control @error('account_holder_name') is-invalid @enderror" value="{{ $val('account_holder_name') }}" maxlength="150">
                                    @include('admin.partials.field-error', ['field' => 'account_holder_name'])
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Account Number <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_account" class="form-control @error('bank_account') is-invalid @enderror" value="{{ $val('bank_account') }}" maxlength="18" oninput="this.value=this.value.replace(/\D/g,'').slice(0,18)">
                                    @include('admin.partials.field-error', ['field' => 'bank_account'])
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Account Type <span class="text-danger">*</span></label>
                                    <select name="account_type" class="form-select @error('account_type') is-invalid @enderror">
                                        <option value="">Select account type</option>
                                        <option value="savings" {{ in_array($adminAccountType, ['savings', 'saving'], true) ? 'selected' : '' }}>Savings</option>
                                        <option value="current" {{ $adminAccountType === 'current' ? 'selected' : '' }}>Current</option>
                                    </select>
                                    @include('admin.partials.field-error', ['field' => 'account_type'])
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">IFSC Code <span class="text-danger">*</span></label>
                                    <input type="text" name="ifsc_code" class="form-control @error('ifsc_code') is-invalid @enderror" value="{{ $val('ifsc_code') }}" maxlength="11" oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9]/g,'').slice(0,11)">
                                    @include('admin.partials.field-error', ['field' => 'ifsc_code'])
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
                            <p class="text-muted small mb-3">Upload verification documents (JPG, PNG or PDF, max 4MB each). Fields marked <span class="text-danger">*</span> are required.</p>
                            @php
                                $documentUploads = [
                                    ['field' => 'aadhaar_card_front', 'label' => 'Aadhaar Card (Front)', 'required' => true, 'folder' => 'documents'],
                                    ['field' => 'aadhaar_card_back', 'label' => 'Aadhaar Card (Back)', 'required' => true, 'folder' => 'documents'],
                                    ['field' => 'pan_card', 'label' => 'PAN Card', 'required' => true, 'folder' => 'documents'],
                                    ['field' => 'gst_file', 'label' => 'GST Certificate', 'required' => true, 'folder' => 'documents'],
                                    ['field' => 'food_license_file', 'label' => 'Food/Gumasta License', 'required' => false, 'folder' => 'documents'],
                                    ['field' => 'business_logo', 'label' => 'Shop Logo', 'required' => false, 'folder' => 'vendors'],
                                    ['field' => 'shop_image', 'label' => 'Shop Image', 'required' => false, 'folder' => 'vendors'],
                                    ['field' => 'business_banner', 'label' => 'Shop Banner', 'required' => false, 'folder' => 'vendors'],
                                ];
                            @endphp
                            <div class="row g-3">
                                @foreach($documentUploads as $doc)
                                    @php
                                        $field = $doc['field'];
                                        $existingFile = $isEdit && !empty($vendor->{$field});
                                        $showRequired = $doc['required'] && (!$isEdit || !$existingFile);
                                    @endphp
                                    <div class="col-md-3">
                                        <label class="form-label">
                                            {{ $doc['label'] }}
                                            @if($showRequired)
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>
                                        <input type="file"
                                            name="{{ $field }}"
                                            class="form-control @if($errors->has($field)) is-invalid @endif"
                                            accept="image/jpeg,image/png,image/webp,application/pdf"
                                            @if($showRequired) required @endif>
                                        @if($errors->has($field))
                                            <div class="text-danger small mt-1 fw-semibold">{{ $errors->first($field) }}</div>
                                        @endif
                                        @if($existingFile)
                                            @php
                                                $docPath = $doc['folder'] === 'documents'
                                                    ? 'public/uploads/vendors/documents/' . $vendor->{$field}
                                                    : 'public/uploads/vendors/' . $vendor->{$field};
                                            @endphp
                                            <small class="d-block mt-1"><a href="{{ asset($docPath) }}" target="_blank" rel="noopener">View uploaded file</a></small>
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
