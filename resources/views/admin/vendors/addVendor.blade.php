@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card form-shell">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex align-items-center mb-4">
                            <h5 class="mb-0"><i class="ri-store-2-line me-2"></i>{{ $title }}</h5>
                        </div>    

                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <div class="form-hero mb-4">
                            <div class="form-hero-title">Create Vendor Account</div>
                            <div class="form-hero-subtitle">Complete onboarding with business profile, KYC documents, and payout details.</div>
                        </div>

                        <!-- Vendor add form removed -->
                            @csrf

                            <h6 class="section-title"><i class="ri-user-line"></i>Vendor Details</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="owner_name" class="form-control @error('owner_name') is-invalid @enderror" value="{{ old('owner_name') }}" maxlength="100" data-alpha-name data-required="true" data-label="Owner Name">
                                    @error('owner_name')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mobile <span class="text-danger">*</span></label>
                                    <input type="text" name="mobile" maxlength="10" class="form-control @error('mobile') is-invalid @enderror" value="{{ old('mobile') }}" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)" data-required="true" data-label="Mobile">
                                    @error('mobile')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" maxlength="255" data-required="true" data-label="Email">
                                    @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" data-required="true" data-label="Password">
                                    <div id="passwordStrengthWrap" class="mt-1">
                                        <div style="height:5px;background:#ececff;border-radius:999px;overflow:hidden;">
                                            <div id="passwordStrengthFill" style="height:100%;width:0;transition:width .2s ease;background:#ef4444;"></div>
                                        </div>
                                        <small id="passwordStrengthText" class="text-muted">Password strength: -</small>
                                    </div>
                                    @error('password')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password_confirmation" class="form-control" data-required="true" data-label="Confirm Password">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">DOB</label>
                                    <input type="date" name="dob" class="form-control @error('dob') is-invalid @enderror" value="{{ old('dob') }}">
                                    @error('dob')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label d-block">Gender</label>
                                    <div class="d-flex gap-3 pt-2">
                                        <label><input type="radio" name="gender" value="male" {{ old('gender') === 'male' ? 'checked' : '' }}> Male</label>
                                        <label><input type="radio" name="gender" value="female" {{ old('gender') === 'female' ? 'checked' : '' }}> Female</label>
                                        <label><input type="radio" name="gender" value="others" {{ old('gender') === 'others' ? 'checked' : '' }}> Others</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Address <span class="text-danger">*</span></label>
                                    <textarea name="address" class="form-control textarea-2-lines @error('address') is-invalid @enderror" rows="2" maxlength="500" data-required="true" data-label="Address">{{ old('address') }}</textarea>
                                    @error('address')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Profile Picture</label>
                                    <input type="file" name="profile_image" class="form-control @error('profile_image') is-invalid @enderror" accept="image/*">
                                    @error('profile_image')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>

                            <h6 class="section-title mt-4"><i class="ri-store-2-line"></i>Store Details</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Store Name <span class="text-danger">*</span></label>
                                    <input type="text" name="business_name" class="form-control @error('business_name') is-invalid @enderror" value="{{ old('business_name') }}" maxlength="150" data-required="true" data-label="Store Name">
                                    @error('business_name')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Business Type</label>
                                    <select name="business_type" class="form-select @error('business_type') is-invalid @enderror">
                                        <option value="">Select Type</option>
                                        @foreach(['Individual','Proprietorship','Partnership','Pvt Ltd','LLP'] as $bt)
                                            <option value="{{ $bt }}" {{ old('business_type') === $bt ? 'selected' : '' }}>{{ $bt }}</option>
                                        @endforeach
                                    </select>
                                    @error('business_type')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Business Email</label>
                                    <input type="email" name="business_email" class="form-control @error('business_email') is-invalid @enderror" value="{{ old('business_email') }}" maxlength="255">
                                    @error('business_email')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Logo</label>
                                    <input type="file" name="business_logo" class="form-control @error('business_logo') is-invalid @enderror" accept="image/*">
                                    @error('business_logo')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Banner</label>
                                    <input type="file" name="business_banner" class="form-control @error('business_banner') is-invalid @enderror" accept="image/*">
                                    @error('business_banner')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Latitude</label>
                                    <input type="number" name="latitude" class="form-control @error('latitude') is-invalid @enderror" value="{{ old('latitude') }}" min="-90" max="90" step="any">
                                    @error('latitude')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Longitude</label>
                                    <input type="number" name="longitude" class="form-control @error('longitude') is-invalid @enderror" value="{{ old('longitude') }}" min="-180" max="180" step="any">
                                    @error('longitude')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Description <span class="text-danger">*</span></label>
                                    <textarea name="business_description" class="form-control textarea-3-lines @error('business_description') is-invalid @enderror" rows="3" maxlength="1000" data-required="true" data-label="Description">{{ old('business_description') }}</textarea>
                                    @error('business_description')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>

                            <h6 class="section-title mt-4"><i class="ri-file-list-3-line"></i>KYC Documents</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">GST File</label>
                                    <input type="file" name="gst_file" class="form-control @error('gst_file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                                    @error('gst_file')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Gumasta / Other License</label>
                                    <input type="file" name="food_license_file" class="form-control @error('food_license_file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                                    @error('food_license_file')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Bank Passbook</label>
                                    <input type="file" name="bank_passbook_file" class="form-control @error('bank_passbook_file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                                    @error('bank_passbook_file')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Address Proof <span class="text-danger">*</span></label>
                                    <input type="file" name="address_proof_file" class="form-control @error('address_proof_file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf" data-required="true" data-label="Address Proof">
                                    @error('address_proof_file')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">National Identity Card</label>
                                    <input type="file" name="national_identity_card_file" class="form-control @error('national_identity_card_file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                                    @error('national_identity_card_file')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>

                            <h6 class="section-title mt-4"><i class="ri-bank-card-line"></i>Store Tax Details</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Tax Name</label>
                                    <input type="text" name="tax_name" class="form-control @error('tax_name') is-invalid @enderror" value="{{ old('tax_name') }}" maxlength="100">
                                    @error('tax_name')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tax Number</label>
                                    <input type="text" name="tax_number" class="form-control @error('tax_number') is-invalid @enderror" value="{{ old('tax_number') }}" maxlength="100">
                                    @error('tax_number')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">PAN Number</label>
                                    <input type="text" name="pan_number" class="form-control @error('pan_number') is-invalid @enderror" value="{{ old('pan_number') }}" maxlength="10" oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9]/g,'').slice(0,10)">
                                    @error('pan_number')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">GST Number</label>
                                    <input type="text" name="gst_number" class="form-control @error('gst_number') is-invalid @enderror" value="{{ old('gst_number') }}" maxlength="15" oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9]/g,'').slice(0,15)">
                                    @error('gst_number')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>

                            <h6 class="section-title mt-4"><i class="ri-bank-line"></i>Bank Details</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Account Number <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_account" class="form-control @error('bank_account') is-invalid @enderror" value="{{ old('bank_account') }}" oninput="this.value=this.value.replace(/\D/g,'').slice(0,18)" data-required="true" data-label="Account Number">
                                    @error('bank_account')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Account Holder Name <span class="text-danger">*</span></label>
                                    <input type="text" name="account_holder_name" class="form-control @error('account_holder_name') is-invalid @enderror" value="{{ old('account_holder_name') }}" maxlength="150" data-alpha-name data-required="true" data-label="Account Holder Name">
                                    @error('account_holder_name')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">IFSC Code <span class="text-danger">*</span></label>
                                    <input type="text" name="ifsc_code" class="form-control @error('ifsc_code') is-invalid @enderror" value="{{ old('ifsc_code') }}" maxlength="11" oninput="this.value=this.value.toUpperCase()" data-required="true" data-label="IFSC Code">
                                    @error('ifsc_code')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror" value="{{ old('bank_name') }}" maxlength="150" data-required="true" data-label="Bank Name">
                                    @error('bank_name')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">UPI ID</label>
                                    <input type="text" name="upi_id" class="form-control @error('upi_id') is-invalid @enderror" value="{{ old('upi_id') }}" maxlength="100">
                                    @error('upi_id')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Commission %</label>
                                    <input type="number" name="commission_percent" class="form-control" value="{{ old('commission_percent', 0) }}" min="0" max="100" step="0.01">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="1" {{ (string) old('status','1') === '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ (string) old('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4 d-flex gap-2 form-actions">
                                <button type="reset" class="btn btn-outline-secondary">Reset</button>
                                <button type="submit" class="btn btn-theme">Register Vendor</button>
                            </div>
                        </form>
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
.form-actions {
    position: sticky;
    bottom: 0;
    background: #fff;
    padding-top: 10px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('adminAddVendorForm');
    if (!form) return;

    const patterns = {
        mobile:       /^[6-9][0-9]{9}$/,
        email:        /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        password:     /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{8,}$/,
        bank_account: /^[0-9]{9,18}$/,
        ifsc_code:    /^[A-Z]{4}0[A-Z0-9]{6}$/,
        pan_number:   /^[A-Z]{5}[0-9]{4}[A-Z]$/,
        gst_number:   /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][A-Z0-9]Z[A-Z0-9]$/,
        alpha_name:   /^[a-zA-Z\s'\-.]+$/,
        upi_id:       /^[a-zA-Z0-9._\-]{2,}@[a-zA-Z0-9._\-]{2,}$/,
    };

    const knownUpiHandles = [
        'oksbi', 'okhdfcbank', 'okicici', 'okaxis', 'ybl', 'ibl', 'axl', 'apl',
        'paytm', 'ptsbi', 'pthdfc', 'ptyes', 'kotak', 'sbi', 'icici', 'hdfcbank',
        'axisbank', 'barodampay', 'upi'
    ];

    // ── Password strength indicator ────────────────────────────────────────
    const passwordInput = form.querySelector('[name="password"]');
    const strengthWrap  = document.getElementById('passwordStrengthWrap');
    const strengthFill  = document.getElementById('passwordStrengthFill');
    const strengthText  = document.getElementById('passwordStrengthText');

    function evaluateStrength(value) {
        let score = 0;
        if (value.length >= 8)       score++;
        if (/[a-z]/.test(value))     score++;
        if (/[A-Z]/.test(value))     score++;
        if (/\d/.test(value))        score++;
        if (/[^\w\s]/.test(value))   score++;

        const colors = ['#ef4444','#f97316','#f59e0b','#eab308','#10b981','#22c55e'];
        const labels = ['-','Very Weak','Weak','Fair','Good','Strong'];
        if (strengthFill) {
            strengthFill.style.width = (score * 20) + '%';
            strengthFill.style.background = colors[score];
        }
        if (strengthText) strengthText.textContent = 'Password strength: ' + labels[score];
    }

    if (passwordInput && strengthFill) {
        passwordInput.addEventListener('input', () => evaluateStrength(passwordInput.value));
    }

    // ── Helper functions ───────────────────────────────────────────────────
    function errorNode(input) {
        let node = input.parentElement.querySelector('.js-client-error');
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
        const node = input.parentElement.querySelector('.js-client-error');
        if (node) node.textContent = '';
    }

    function isEmpty(input) {
        if (input.type === 'file') return !(input.files && input.files.length > 0);
        return !String(input.value || '').trim();
    }

    function isValidUpiId(value) {
        if (!patterns.upi_id.test(value)) return false;
        const parts = value.split('@');
        if (parts.length !== 2) return false;
        return knownUpiHandles.includes(parts[1].toLowerCase());
    }

    // ── Submit validation ──────────────────────────────────────────────────
    form.addEventListener('submit', function (e) {
        let isValid = true;

        // Required fields
        form.querySelectorAll('[data-required="true"]').forEach((input) => {
            clearError(input);
            if (isEmpty(input)) {
                isValid = false;
                setError(input, (input.dataset.label || 'This field') + ' is required.');
            }
        });

        // Mobile format
        const mobile = form.querySelector('[name="mobile"]');
        if (mobile && !isEmpty(mobile) && !patterns.mobile.test(mobile.value.trim())) {
            isValid = false;
            setError(mobile, 'Enter a valid 10-digit mobile number starting with 6-9.');
        }

        // Alternate mobile (if filled)
        const altMobile = form.querySelector('[name="alternate_mobile"]');
        if (altMobile && !isEmpty(altMobile)) {
            if (!patterns.mobile.test(altMobile.value.trim())) {
                isValid = false;
                setError(altMobile, 'Enter a valid 10-digit mobile number starting with 6-9.');
            } else if (mobile && altMobile.value.trim() === mobile.value.trim()) {
                isValid = false;
                setError(altMobile, 'Alternate mobile must differ from primary mobile.');
            }
        }

        // Email format
        const email = form.querySelector('[name="email"]');
        if (email && !isEmpty(email) && !patterns.email.test(email.value.trim())) {
            isValid = false;
            setError(email, 'Enter a valid email address.');
        }

        // Business email format
        const businessEmail = form.querySelector('[name="business_email"]');
        if (businessEmail && !isEmpty(businessEmail) && !patterns.email.test(businessEmail.value.trim())) {
            isValid = false;
            setError(businessEmail, 'Enter a valid business email address.');
        }

        // Password strength
        const password = form.querySelector('[name="password"]');
        if (password && !isEmpty(password) && !patterns.password.test(password.value)) {
            isValid = false;
            setError(password, 'Password must include uppercase, lowercase, number and special character.');
        }

        // Password confirmation match
        const confirmPassword = form.querySelector('[name="password_confirmation"]');
        if (password && confirmPassword && !isEmpty(confirmPassword) && password.value !== confirmPassword.value) {
            isValid = false;
            setError(confirmPassword, 'Passwords do not match.');
        }

        // Alpha-only name fields
        form.querySelectorAll('[data-alpha-name]').forEach((input) => {
            if (!isEmpty(input) && !patterns.alpha_name.test(input.value.trim())) {
                isValid = false;
                setError(input, (input.dataset.label || 'This field') + ' may only contain letters, spaces, hyphens, and apostrophes.');
            }
        });

        // Business description length
        const desc = form.querySelector('[name="business_description"]');
        if (desc && !isEmpty(desc) && desc.value.trim().length < 20) {
            isValid = false;
            setError(desc, 'Business description must be at least 20 characters.');
        } else if (desc && !isEmpty(desc) && desc.value.trim().length > 1000) {
            isValid = false;
            setError(desc, 'Business description may not exceed 1000 characters.');
        }

        // PAN format
        const panNumber = form.querySelector('[name="pan_number"]');
        if (panNumber && !isEmpty(panNumber) && !patterns.pan_number.test(panNumber.value.trim())) {
            isValid = false;
            setError(panNumber, 'Enter a valid PAN number in format AAAAA9999A.');
        }

        // GST format
        const gstNumber = form.querySelector('[name="gst_number"]');
        if (gstNumber && !isEmpty(gstNumber) && !patterns.gst_number.test(gstNumber.value.trim())) {
            isValid = false;
            setError(gstNumber, 'Enter a valid GST number in format 22ABCDE1234F1Z5.');
        }

        // Coordinates
        const latitude = form.querySelector('[name="latitude"]');
        if (latitude && !isEmpty(latitude)) {
            const value = Number(latitude.value);
            if (Number.isNaN(value) || value < -90 || value > 90) {
                isValid = false;
                setError(latitude, 'Latitude must be between -90 and 90.');
            }
        }

        const longitude = form.querySelector('[name="longitude"]');
        if (longitude && !isEmpty(longitude)) {
            const value = Number(longitude.value);
            if (Number.isNaN(value) || value < -180 || value > 180) {
                isValid = false;
                setError(longitude, 'Longitude must be between -180 and 180.');
            }
        }

        // Bank account format
        const bankAccount = form.querySelector('[name="bank_account"]');
        if (bankAccount && !isEmpty(bankAccount) && !patterns.bank_account.test(bankAccount.value.trim())) {
            isValid = false;
            setError(bankAccount, 'Account number must be 9 to 18 digits.');
        }

        // IFSC format
        const ifsc = form.querySelector('[name="ifsc_code"]');
        if (ifsc && !isEmpty(ifsc) && !patterns.ifsc_code.test(ifsc.value.trim())) {
            isValid = false;
            setError(ifsc, 'Enter a valid IFSC code (example: SBIN0001234).');
        }

        // UPI format and known extension
        const upiId = form.querySelector('[name="upi_id"]');
        if (upiId && !isEmpty(upiId) && !isValidUpiId(upiId.value.trim())) {
            isValid = false;
            setError(upiId, 'Enter a valid UPI ID with supported extension (example: name@oksbi).');
        }

        if (!isValid) {
            e.preventDefault();
            const firstError = form.querySelector('.is-invalid');
            if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    // Clear errors live
    form.querySelectorAll('input, textarea, select').forEach((input) => {
        input.addEventListener('input', () => clearError(input));
        input.addEventListener('change', () => clearError(input));
    });
});
</script>
@endsection
