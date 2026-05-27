@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card form-shell">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex align-items-center mb-4">
                            <h5 class="mb-0"><i class="ri-pencil-line me-2"></i>{{ $title }}</h5>
                            <div class="ms-auto d-flex gap-2">
                                <a href="{{ route('admin.view-vendor', $vendor->vendor_id) }}" class="btn btn-outline-primary btn-sm"><i class="ri-eye-line me-1"></i>View</a>
                                <!-- Vendor back link removed -->
                            </div>
                        </div>

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        @if(session('error') && !session('section_error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="form-hero mb-4">
                            <div class="form-hero-title">Edit Vendor Profile</div>
                            <div class="form-hero-subtitle">Each section can be saved independently — no need to complete the entire form at once.</div>
                        </div>

                        {{-- ═══════════════════════════════════════════════════
                             SECTION 1 — Vendor Details
                        ════════════════════════════════════════════════════ --}}
                        <!-- Vendor details form removed -->
                            @csrf
                            <h6 class="section-title {{ session('section_success') === 'vendor_details' ? 'section-saved' : '' }}">
                                <i class="ri-user-line"></i>Vendor Details
                                @if(session('section_success') === 'vendor_details')
                                    <span class="ms-auto badge bg-success">Saved</span>
                                @endif
                            </h6>
                            @if(session('section_error') === 'vendor_details' && (session('error') || session('warning')))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="ri-error-warning-line me-2"></i>{{ session('error') ?? session('warning') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                            <div class="row g-3">
                                <div class="col-md-6">
                                     <label class="form-label">Name <span class="text-danger">*</span></label>
                                     <input type="text" name="owner_name" class="form-control @error('owner_name') is-invalid @enderror" value="{{ old('owner_name', $vendor->owner_name) }}" maxlength="100" data-alpha-name data-required="true" data-label="Owner Name">
                                    @error('owner_name')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mobile <span class="text-danger">*</span></label>
                                    <input type="text" name="mobile" maxlength="10" class="form-control @error('mobile') is-invalid @enderror" value="{{ old('mobile', $vendor->mobile) }}" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)" data-required="true" data-label="Mobile">
                                    @error('mobile')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $vendor->email) }}" maxlength="255" data-required="true" data-label="Email">
                                    @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Alternate Mobile</label>
                                    <input type="text" name="alternate_mobile" maxlength="10" class="form-control @error('alternate_mobile') is-invalid @enderror" value="{{ old('alternate_mobile', $vendor->alternate_mobile) }}" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)">
                                    @error('alternate_mobile')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">DOB</label>
                                    <input type="date" name="dob" class="form-control @error('dob') is-invalid @enderror" value="{{ old('dob', $vendor->dob) }}">
                                    @error('dob')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label d-block">Gender</label>
                                    <div class="d-flex gap-3 pt-2">
                                        <label><input type="radio" name="gender" value="male" {{ old('gender', $vendor->gender) === 'male' ? 'checked' : '' }}> Male</label>
                                        <label><input type="radio" name="gender" value="female" {{ old('gender', $vendor->gender) === 'female' ? 'checked' : '' }}> Female</label>
                                        <label><input type="radio" name="gender" value="others" {{ old('gender', $vendor->gender) === 'others' ? 'checked' : '' }}> Others</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="1" {{ (string) old('status', (string)($vendor->status ?? '1')) === '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ (string) old('status', (string)($vendor->status ?? '1')) === '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Address <span class="text-danger">*</span></label>
                                    <textarea name="address" class="form-control textarea-2-lines @error('address') is-invalid @enderror" rows="2" maxlength="500" data-required="true" data-label="Address">{{ old('address', $vendor->address) }}</textarea>
                                    @error('address')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label d-flex align-items-center justify-content-between">
                                        <span>Profile Picture</span>
                                        @if(!empty($vendor->profile_image))
                                            <span class="mini-file-actions">
                                                <a title="View" target="_blank" href="{{ asset('public/uploads/vendors/' . $vendor->profile_image) }}"><i class="ri-eye-line"></i></a>
                                                <a title="Download" download href="{{ asset('public/uploads/vendors/' . $vendor->profile_image) }}"><i class="ri-download-line"></i></a>
                                            </span>
                                        @endif
                                    </label>
                                    <input type="file" name="profile_image" class="form-control @error('profile_image') is-invalid @enderror" accept="image/*">
                                    @error('profile_image')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>
                            <div class="section-footer mt-3">
                                <button type="submit" class="btn btn-theme btn-sm"><i class="ri-save-line me-1"></i>Save Vendor Details</button>
                            </div>
                        </form>

                        {{-- ═══════════════════════════════════════════════════
                             SECTION 2 — Store Details
                        ════════════════════════════════════════════════════ --}}
                        <!-- Vendor store details form removed -->
                            @csrf
                            <h6 class="section-title {{ session('section_success') === 'store_details' ? 'section-saved' : '' }}">
                                <i class="ri-store-2-line"></i>Store Details
                                @if(session('section_success') === 'store_details')
                                    <span class="ms-auto badge bg-success">Saved</span>
                                @endif
                            </h6>
                            @if(session('section_error') === 'store_details' && (session('error') || session('warning')))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="ri-error-warning-line me-2"></i>{{ session('error') ?? session('warning') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Store Name <span class="text-danger">*</span></label>
                                    <input type="text" name="business_name" class="form-control @error('business_name') is-invalid @enderror" value="{{ old('business_name', $vendor->business_name) }}" maxlength="150" data-required="true" data-label="Store Name">
                                    @error('business_name')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Business Type</label>
                                    <select name="business_type" class="form-select @error('business_type') is-invalid @enderror">
                                        <option value="">Select Type</option>
                                        @foreach(['Individual','Proprietorship','Partnership','Pvt Ltd','LLP'] as $bt)
                                            <option value="{{ $bt }}" {{ old('business_type', $vendor->business_type) === $bt ? 'selected' : '' }}>{{ $bt }}</option>
                                        @endforeach
                                    </select>
                                    @error('business_type')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Business Email</label>
                                    <input type="email" name="business_email" class="form-control @error('business_email') is-invalid @enderror" value="{{ old('business_email', $vendor->business_email) }}" maxlength="255">
                                    @error('business_email')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label d-flex align-items-center justify-content-between">
                                        <span>Logo</span>
                                        @if(!empty($vendor->business_logo))
                                            <span class="mini-file-actions">
                                                <a title="View" target="_blank" href="{{ asset('public/uploads/vendors/' . $vendor->business_logo) }}"><i class="ri-eye-line"></i></a>
                                                <a title="Download" download href="{{ asset('public/uploads/vendors/' . $vendor->business_logo) }}"><i class="ri-download-line"></i></a>
                                            </span>
                                        @endif
                                    </label>
                                    <input type="file" name="business_logo" class="form-control @error('business_logo') is-invalid @enderror" accept="image/*">
                                    @error('business_logo')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label d-flex align-items-center justify-content-between">
                                        <span>Banner</span>
                                        @if(!empty($vendor->business_banner))
                                            <span class="mini-file-actions">
                                                <a title="View" target="_blank" href="{{ asset('public/uploads/vendors/' . $vendor->business_banner) }}"><i class="ri-eye-line"></i></a>
                                                <a title="Download" download href="{{ asset('public/uploads/vendors/' . $vendor->business_banner) }}"><i class="ri-download-line"></i></a>
                                            </span>
                                        @endif
                                    </label>
                                    <input type="file" name="business_banner" class="form-control @error('business_banner') is-invalid @enderror" accept="image/*">
                                    @error('business_banner')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Latitude</label>
                                    <input type="number" name="latitude" class="form-control @error('latitude') is-invalid @enderror" value="{{ old('latitude', $vendor->latitude) }}" min="-90" max="90" step="any">
                                    @error('latitude')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Longitude</label>
                                    <input type="number" name="longitude" class="form-control @error('longitude') is-invalid @enderror" value="{{ old('longitude', $vendor->longitude) }}" min="-180" max="180" step="any">
                                    @error('longitude')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Description <span class="text-danger">*</span></label>
                                    <textarea name="business_description" class="form-control textarea-3-lines @error('business_description') is-invalid @enderror" rows="3" maxlength="1000" data-required="true" data-label="Description">{{ old('business_description', $vendor->business_description) }}</textarea>
                                    @error('business_description')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>
                            <div class="section-footer mt-3">
                                <button type="submit" class="btn btn-theme btn-sm"><i class="ri-save-line me-1"></i>Save Store Details</button>
                            </div>
                        </form>

                        {{-- ═══════════════════════════════════════════════════
                             SECTION 3 — KYC Documents
                        ════════════════════════════════════════════════════ --}}
                        <!-- Vendor KYC documents form removed -->
                            @csrf
                            <h6 class="section-title {{ session('section_success') === 'kyc_documents' ? 'section-saved' : '' }}">
                                <i class="ri-file-list-3-line"></i>KYC Documents
                                @if(session('section_success') === 'kyc_documents')
                                    <span class="ms-auto badge bg-success">Saved</span>
                                @endif
                            </h6>
                            @if(session('section_error') === 'kyc_documents' && (session('error') || session('warning')))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="ri-error-warning-line me-2"></i>{{ session('error') ?? session('warning') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label d-flex align-items-center justify-content-between">
                                        <span>GST File</span>
                                        @if(!empty($vendor->gst_file))
                                            <span class="mini-file-actions">
                                                <a title="View" target="_blank" href="{{ asset('public/uploads/vendors/documents/' . $vendor->gst_file) }}"><i class="ri-eye-line"></i></a>
                                                <a title="Download" download href="{{ asset('public/uploads/vendors/documents/' . $vendor->gst_file) }}"><i class="ri-download-line"></i></a>
                                            </span>
                                        @endif
                                    </label>
                                    <input type="file" name="gst_file" class="form-control @error('gst_file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                                    @error('gst_file')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label d-flex align-items-center justify-content-between">
                                        <span>Gumasta / Other License</span>
                                        @if(!empty($vendor->food_license_file))
                                            <span class="mini-file-actions">
                                                <a title="View" target="_blank" href="{{ asset('public/uploads/vendors/documents/' . $vendor->food_license_file) }}"><i class="ri-eye-line"></i></a>
                                                <a title="Download" download href="{{ asset('public/uploads/vendors/documents/' . $vendor->food_license_file) }}"><i class="ri-download-line"></i></a>
                                            </span>
                                        @endif
                                    </label>
                                    <input type="file" name="food_license_file" class="form-control @error('food_license_file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                                    @error('food_license_file')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label d-flex align-items-center justify-content-between">
                                        <span>Bank Passbook</span>
                                        @if(!empty($vendor->bank_passbook_file))
                                            <span class="mini-file-actions">
                                                <a title="View" target="_blank" href="{{ asset('public/uploads/vendors/documents/' . $vendor->bank_passbook_file) }}"><i class="ri-eye-line"></i></a>
                                                <a title="Download" download href="{{ asset('public/uploads/vendors/documents/' . $vendor->bank_passbook_file) }}"><i class="ri-download-line"></i></a>
                                            </span>
                                        @endif
                                    </label>
                                    <input type="file" name="bank_passbook_file" class="form-control @error('bank_passbook_file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                                    @error('bank_passbook_file')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label d-flex align-items-center justify-content-between">
                                        <span>Address Proof</span>
                                        @if(!empty($vendor->address_proof_file))
                                            <span class="mini-file-actions">
                                                <a title="View" target="_blank" href="{{ asset('public/uploads/vendors/documents/' . $vendor->address_proof_file) }}"><i class="ri-eye-line"></i></a>
                                                <a title="Download" download href="{{ asset('public/uploads/vendors/documents/' . $vendor->address_proof_file) }}"><i class="ri-download-line"></i></a>
                                            </span>
                                        @endif
                                    </label>
                                    <input type="file" name="address_proof_file" class="form-control @error('address_proof_file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                                    @error('address_proof_file')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label d-flex align-items-center justify-content-between">
                                        <span>National Identity Card</span>
                                        @if(!empty($vendor->national_identity_card_file))
                                            <span class="mini-file-actions">
                                                <a title="View" target="_blank" href="{{ asset('public/uploads/vendors/documents/' . $vendor->national_identity_card_file) }}"><i class="ri-eye-line"></i></a>
                                                <a title="Download" download href="{{ asset('public/uploads/vendors/documents/' . $vendor->national_identity_card_file) }}"><i class="ri-download-line"></i></a>
                                            </span>
                                        @endif
                                    </label>
                                    <input type="file" name="national_identity_card_file" class="form-control @error('national_identity_card_file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                                    @error('national_identity_card_file')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>
                            <div class="section-footer mt-3">
                                <button type="submit" class="btn btn-theme btn-sm"><i class="ri-save-line me-1"></i>Save KYC Documents</button>
                            </div>
                        </form>

                        {{-- ═══════════════════════════════════════════════════
                             SECTION 4 — Store Tax Details
                        ════════════════════════════════════════════════════ --}}
                        <!-- Vendor tax details form removed -->
                            @csrf
                            <h6 class="section-title {{ session('section_success') === 'tax_details' ? 'section-saved' : '' }}">
                                <i class="ri-bank-card-line"></i>Store Tax Details
                                @if(session('section_success') === 'tax_details')
                                    <span class="ms-auto badge bg-success">Saved</span>
                                @endif
                            </h6>
                            @if(session('section_error') === 'tax_details' && (session('error') || session('warning')))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="ri-error-warning-line me-2"></i>{{ session('error') ?? session('warning') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Tax Name</label>
                                    <input type="text" name="tax_name" class="form-control @error('tax_name') is-invalid @enderror" value="{{ old('tax_name', $vendor->tax_name) }}" maxlength="100">
                                    @error('tax_name')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tax Number</label>
                                    <input type="text" name="tax_number" class="form-control @error('tax_number') is-invalid @enderror" value="{{ old('tax_number', $vendor->tax_number) }}" maxlength="100">
                                    @error('tax_number')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">PAN Number</label>
                                    <input type="text" name="pan_number" class="form-control @error('pan_number') is-invalid @enderror" value="{{ old('pan_number', $vendor->pan_number) }}" maxlength="10" oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9]/g,'').slice(0,10)">
                                    @error('pan_number')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">GST Number</label>
                                    <input type="text" name="gst_number" class="form-control @error('gst_number') is-invalid @enderror" value="{{ old('gst_number', $vendor->gst_number) }}" maxlength="15" oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9]/g,'').slice(0,15)">
                                    @error('gst_number')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>
                            <div class="section-footer mt-3">
                                <button type="submit" class="btn btn-theme btn-sm"><i class="ri-save-line me-1"></i>Save Tax Details</button>
                            </div>
                        </form>

                        {{-- ═══════════════════════════════════════════════════
                             SECTION 5 — Bank Details
                        ════════════════════════════════════════════════════ --}}
                        <!-- Vendor bank details form removed -->
                            @csrf
                            <h6 class="section-title {{ session('section_success') === 'bank_details' ? 'section-saved' : '' }}">
                                <i class="ri-bank-line"></i>Bank Details
                                @if(session('section_success') === 'bank_details')
                                    <span class="ms-auto badge bg-success">Saved</span>
                                @endif
                            </h6>
                            @if(session('section_error') === 'bank_details' && (session('error') || session('warning')))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="ri-error-warning-line me-2"></i>{{ session('error') ?? session('warning') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Account Number <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_account" class="form-control @error('bank_account') is-invalid @enderror" value="{{ old('bank_account', $vendor->bank_account) }}" oninput="this.value=this.value.replace(/\D/g,'').slice(0,18)" data-required="true" data-label="Account Number">
                                    @error('bank_account')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Account Holder Name <span class="text-danger">*</span></label>
                                    <input type="text" name="account_holder_name" class="form-control @error('account_holder_name') is-invalid @enderror" value="{{ old('account_holder_name', $vendor->account_holder_name) }}" maxlength="150" data-alpha-name data-required="true" data-label="Account Holder Name">
                                    @error('account_holder_name')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">IFSC Code <span class="text-danger">*</span></label>
                                    <input type="text" name="ifsc_code" class="form-control @error('ifsc_code') is-invalid @enderror" value="{{ old('ifsc_code', $vendor->ifsc_code) }}" maxlength="11" oninput="this.value=this.value.toUpperCase()" data-required="true" data-label="IFSC Code">
                                    @error('ifsc_code')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror" value="{{ old('bank_name', $vendor->bank_name) }}" maxlength="150" data-required="true" data-label="Bank Name">
                                    @error('bank_name')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">UPI ID</label>
                                    <input type="text" name="upi_id" class="form-control @error('upi_id') is-invalid @enderror" value="{{ old('upi_id', $vendor->upi_id) }}" maxlength="100">
                                    @error('upi_id')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Commission %</label>
                                    <input type="number" name="commission_percent" class="form-control" value="{{ old('commission_percent', $vendor->commission_percent ?? 0) }}" min="0" max="100" step="0.01">
                                </div>
                            </div>
                            <div class="section-footer mt-3">
                                <button type="submit" class="btn btn-theme btn-sm"><i class="ri-save-line me-1"></i>Save Bank Details</button>
                            </div>
                        </form>

                        <div class="mt-4 pt-2 border-top d-flex justify-content-end">
                            <!-- Vendor back link removed -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
.form-shell {
    border: 1px solid #e9ecf4;
    border-radius: 18px;
    box-shadow: 0 12px 30px rgba(26, 38, 78, 0.08);
}
.form-hero {
    background: linear-gradient(135deg, #f4f7ff, #eef3ff);
    border: 1px solid #dde5ff;
    border-radius: 14px;
    padding: 14px 16px;
}
.form-hero-title {
    font-size: 18px;
    font-weight: 700;
    color: #28325e;
}
.form-hero-subtitle {
    color: #5b6280;
    font-size: 13px;
}
.section-title {
    font-size: 14px;
    font-weight: 700;
    color: #2d3763;
    background: #f7f8fc;
    border: 1px solid #e7eaf3;
    border-radius: 10px;
    padding: 10px 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 14px;
}
.section-title.section-saved {
    background: #efffef;
    border-color: #b2dfdb;
    color: #1e6b4c;
}
.vendor-section-form {
    border: 1px solid #e9ecf4;
    border-radius: 14px;
    padding: 20px 20px 14px;
    background: #fff;
}
.section-footer {
    border-top: 1px solid #f0f2fa;
    padding-top: 12px;
}
.form-shell .form-control,
.form-shell .form-select {
    border-radius: 10px;
    min-height: 42px;
    border-color: #d9dfef;
}
.form-shell textarea.form-control.textarea-2-lines {
    min-height: 84px;
    resize: vertical;
}
.form-shell textarea.form-control.textarea-3-lines {
    min-height: 110px;
    resize: vertical;
}
.form-shell .form-control:focus,
.form-shell .form-select:focus {
    border-color: #6b83ff;
    box-shadow: 0 0 0 0.2rem rgba(107, 131, 255, 0.15);
}
.form-shell .form-label {
    font-weight: 600;
    color: #3b4467;
    margin-bottom: 6px;
}
.mini-file-actions {
    display: inline-flex;
    gap: 8px;
}
.mini-file-actions a {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    background: #eef3ff;
    color: #4056c7;
    border: 1px solid #d8e1ff;
}
.mini-file-actions a:hover {
    background: #4056c7;
    color: #fff;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var patterns = {
        mobile:       /^[6-9][0-9]{9}$/,
        email:        /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        bank_account: /^[0-9]{9,18}$/,
        ifsc_code:    /^[A-Z]{4}0[A-Z0-9]{6}$/,
        pan_number:   /^[A-Z]{5}[0-9]{4}[A-Z]$/,
        gst_number:   /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][A-Z0-9]Z[A-Z0-9]$/,
        alpha_name:   /^[a-zA-Z\s'\-.]+$/,
        upi_id:       /^[a-zA-Z0-9._\-]{2,}@[a-zA-Z0-9._\-]{2,}$/,
    };

    var knownUpiHandles = [
        'oksbi', 'okhdfcbank', 'okicici', 'okaxis', 'ybl', 'ibl', 'axl', 'apl',
        'paytm', 'ptsbi', 'pthdfc', 'ptyes', 'kotak', 'sbi', 'icici', 'hdfcbank',
        'axisbank', 'barodampay', 'upi'
    ];

    function errorNode(input) {
        var node = input.parentElement.querySelector('.js-client-error');
        if (!node) {
            node = document.createElement('small');
            node.className = 'text-danger js-client-error';
            input.parentElement.appendChild(node);
        }
        return node;
    }

    function setError(input, message) {
        input.classList.add('is-invalid');
        errorNode(input).textContent = message;
    }

    function clearError(input) {
        input.classList.remove('is-invalid');
        var node = input.parentElement.querySelector('.js-client-error');
        if (node) node.textContent = '';
    }

    function isEmpty(input) {
        if (input.type === 'file') return !(input.files && input.files.length > 0);
        return !String(input.value || '').trim();
    }

    function isValidUpiId(value) {
        if (!patterns.upi_id.test(value)) return false;
        var parts = value.split('@');
        if (parts.length !== 2) return false;
        return knownUpiHandles.indexOf(parts[1].toLowerCase()) !== -1;
    }

    document.querySelectorAll('.vendor-section-form').forEach(function (form) {

        form.addEventListener('submit', function (e) {
            var isValid = true;

                // Required fields
                form.querySelectorAll('[data-required="true"]').forEach(function (input) {
                    clearError(input);
                    if (isEmpty(input)) {
                        isValid = false;
                        setError(input, (input.dataset.label || 'This field') + ' is required.');
                    }
                });

                // Mobile format (only if not empty)
                var mobile = form.querySelector('[name="mobile"]');
                if (mobile && !isEmpty(mobile) && !patterns.mobile.test(mobile.value.trim())) {
                    isValid = false;
                    setError(mobile, 'Enter a valid 10-digit mobile number starting with 6-9.');
                }

                // Alternate mobile (optional — only validate if filled)
                var altMobile = form.querySelector('[name="alternate_mobile"]');
                if (altMobile && !isEmpty(altMobile)) {
                    clearError(altMobile);
                    if (!patterns.mobile.test(altMobile.value.trim())) {
                        isValid = false;
                        setError(altMobile, 'Enter a valid 10-digit mobile number starting with 6-9.');
                    } else if (mobile && altMobile.value.trim() === mobile.value.trim()) {
                        isValid = false;
                        setError(altMobile, 'Alternate mobile must differ from primary mobile.');
                    }
                }

                // Email format (only if not empty)
                var email = form.querySelector('[name="email"]');
                if (email && !isEmpty(email) && !patterns.email.test(email.value.trim())) {
                    isValid = false;
                    setError(email, 'Enter a valid email address.');
                }

                // Business email format (only if not empty)
                var businessEmail = form.querySelector('[name="business_email"]');
                if (businessEmail && !isEmpty(businessEmail) && !patterns.email.test(businessEmail.value.trim())) {
                    isValid = false;
                    setError(businessEmail, 'Enter a valid business email address.');
                }

                // Alpha-name fields (format only; required handled above)
                form.querySelectorAll('[data-alpha-name]').forEach(function (input) {
                    if (!isEmpty(input) && !patterns.alpha_name.test(input.value.trim())) {
                        isValid = false;
                        setError(input, (input.dataset.label || 'This field') + ' may only contain letters, spaces, hyphens, and apostrophes.');
                    }
                });

                // Business description length (only if not empty)
                var desc = form.querySelector('[name="business_description"]');
                if (desc && !isEmpty(desc) && desc.value.trim().length < 20) {
                    isValid = false;
                    setError(desc, 'Business description must be at least 20 characters.');
                } else if (desc && !isEmpty(desc) && desc.value.trim().length > 1000) {
                    isValid = false;
                    setError(desc, 'Business description may not exceed 1000 characters.');
                }

                // PAN format (only if not empty)
                var panNumber = form.querySelector('[name="pan_number"]');
                if (panNumber && !isEmpty(panNumber) && !patterns.pan_number.test(panNumber.value.trim())) {
                    isValid = false;
                    setError(panNumber, 'Enter a valid PAN number in format AAAAA9999A.');
                }

                // GST format (only if not empty)
                var gstNumber = form.querySelector('[name="gst_number"]');
                if (gstNumber && !isEmpty(gstNumber) && !patterns.gst_number.test(gstNumber.value.trim())) {
                    isValid = false;
                    setError(gstNumber, 'Enter a valid GST number in format 22ABCDE1234F1Z5.');
                }

                // Coordinates (only if not empty)
                var latitude = form.querySelector('[name="latitude"]');
                if (latitude && !isEmpty(latitude)) {
                    var latitudeValue = Number(latitude.value);
                    if (Number.isNaN(latitudeValue) || latitudeValue < -90 || latitudeValue > 90) {
                        isValid = false;
                        setError(latitude, 'Latitude must be between -90 and 90.');
                    }
                }

                var longitude = form.querySelector('[name="longitude"]');
                if (longitude && !isEmpty(longitude)) {
                    var longitudeValue = Number(longitude.value);
                    if (Number.isNaN(longitudeValue) || longitudeValue < -180 || longitudeValue > 180) {
                        isValid = false;
                        setError(longitude, 'Longitude must be between -180 and 180.');
                    }
                }

                // Bank account format (only if not empty)
                var bankAccount = form.querySelector('[name="bank_account"]');
                if (bankAccount && !isEmpty(bankAccount) && !patterns.bank_account.test(bankAccount.value.trim())) {
                    isValid = false;
                    setError(bankAccount, 'Account number must be 9 to 18 digits.');
                }

                // IFSC format (only if not empty)
                var ifsc = form.querySelector('[name="ifsc_code"]');
                if (ifsc && !isEmpty(ifsc) && !patterns.ifsc_code.test(ifsc.value.trim())) {
                    isValid = false;
                    setError(ifsc, 'Enter a valid IFSC code (example: SBIN0001234).');
                }

                // UPI format and known extension (only if not empty)
                var upiId = form.querySelector('[name="upi_id"]');
                if (upiId && !isEmpty(upiId) && !isValidUpiId(upiId.value.trim())) {
                    isValid = false;
                    setError(upiId, 'Enter a valid UPI ID with supported extension (example: name@oksbi).');
                }

            if (!isValid) {
                e.preventDefault();
                var firstError = form.querySelector('.is-invalid');
                if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });

        // Clear errors live as the user types
        form.querySelectorAll('input, textarea, select').forEach(function (input) {
            input.addEventListener('input', function () { clearError(input); });
            input.addEventListener('change', function () { clearError(input); });
        });
    });
});
</script>
@endsection
