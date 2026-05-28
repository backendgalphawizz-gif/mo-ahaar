<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Registration</title>
    @include('layouts.favicon-dynamic')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{background:#f8fafc;font-family:Arial,Helvetica,sans-serif;padding:26px 10px}
        .register-wrap{max-width:900px;margin:0 auto;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px;box-shadow:0 10px 30px rgba(15,23,42,.08)}
        .title-row{display:flex;align-items:center;gap:8px;margin-bottom:10px}
        .title-row img{height:18px}
        .title-row h3{margin:0;font-size:31px;font-weight:700;color:#111827}
        .stepper{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:16px}
        .step{display:flex;align-items:center;gap:6px;font-size:12px;color:#9ca3af}
        .step .dot{width:20px;height:20px;border-radius:50%;background:#d1d5db;color:#fff;font-size:11px;font-weight:700;display:inline-flex;align-items:center;justify-content:center}
        .step-line{width:28px;height:2px;background:#d1d5db}
        .step.active,.step.done{color:#ef4444}.step.active .dot,.step.done .dot{background:#ef4444}.step.done+.step-line{background:#ef4444}
        .pane{display:none}.pane.active{display:block}
        .section-title{font-size:31px; font-weight:700;margin:0 0 10px;color:#0f172a}
        .field-label{font-size:13px;font-weight:600;margin-bottom:5px;color:#1f2937}.req{color:#ef4444}
        .form-control,.form-select{min-height:40px;border-radius:8px;border:1px solid #d1d5db;font-size:14px}
        textarea.form-control{min-height:84px}
        .field-help{font-size:12px;color:#6b7280;margin-top:4px}
        .btn-row{display:flex;justify-content:space-between;gap:8px;margin-top:12px}
        .btn-nav{min-width:70px;height:34px;border-radius:8px;font-size:13px;font-weight:600}
        .btn-next{background:#ef4444;border:1px solid #ef4444;color:#fff}
        .btn-next:hover{background:#dc2626;color:#fff}
        .btn-back{background:#fff;border:1px solid #d1d5db;color:#475569}
        .seller-login{text-align:center;font-size:13px;color:#6b7280;margin-top:10px}
        .seller-login a{color:#111827;text-decoration:none;font-weight:600}
        .seller-login a:hover{text-decoration:underline}
        .file-drop{border:1px dashed #d1d5db;border-radius:8px;padding:18px;text-align:center;background:#fafafa}
        .file-preview-wrap{margin-top:8px;display:inline-flex;flex-direction:column;gap:4px}
        .file-preview-wrap img{max-width:120px;max-height:120px;border:1px solid #e5e7eb;border-radius:8px;object-fit:cover}
        .strength-track{height:5px;background:#e5e7eb;border-radius:999px;overflow:hidden}
        .strength-fill{height:100%;width:0;background:#ef4444;transition:width .15s linear}
        .strength-text{margin-top:4px;font-size:11px;color:#6b7280}
        .pac-container{z-index:9999!important}
    </style>
</head>
<body>
@php
    $logoUrl = asset('public/uploads/settings/moaahar-logo.png');
    $googleMapsApiKey = (string) config('services.google_maps.api_key', '');
@endphp
<div class="register-wrap">
    <div class="title-row">
        <img src="{{ $logoUrl }}" alt="mo-ahaar">
        <h3>Vendor Registration</h3>
    </div>

    <div class="stepper" id="stepper">
        <div class="step active" data-step="1"><span class="dot">1</span><span>Personal Details</span></div>
        <span class="step-line"></span>
        <div class="step" data-step="2"><span class="dot">2</span><span>Business Details</span></div>
        <span class="step-line"></span>
        <div class="step" data-step="3"><span class="dot">3</span><span>Bank Details</span></div>
        <span class="step-line"></span>
        <div class="step" data-step="4"><span class="dot">4</span><span>Documents Upload</span></div>
    </div>

    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">Please correct the highlighted fields.</div>@endif

    <form action="{{ route('vendor.register.submit') }}" method="POST" enctype="multipart/form-data" id="vendorRegisterForm" novalidate>
        @csrf
        <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude') }}">
        <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude') }}">

        <div class="pane active" data-pane="1">
            <h4 class="section-title">Personal Details</h4>
            <div class="row g-3">
                <div class="col-md-6"><label class="field-label">Full Name <span class="req">*</span></label><input type="text" name="owner_name" class="form-control" value="{{ old('owner_name') }}" placeholder="Enter your full name" required></div>
                <div class="col-md-6"><label class="field-label">Email Address <span class="req">*</span></label><input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="your.email@example.com" required></div>
                <div class="col-md-6"><label class="field-label">Mobile Number <span class="req">*</span></label><input type="text" name="mobile" maxlength="10" class="form-control" value="{{ old('mobile') }}" placeholder="+91 1234567890" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)" required></div>
                <div class="col-md-6"><label class="field-label">DOB</label><input type="date" name="dob" class="form-control" value="{{ old('dob') }}"></div>
                <div class="col-md-6"><label class="field-label">Password <span class="req">*</span></label><input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
                    <div class="field-help">Minimum 8 chars with uppercase, lowercase, number, special character.</div>
                    <div class="strength-track"><div class="strength-fill" id="strengthFill"></div></div>
                    <div class="strength-text" id="strengthText">Password strength: -</div>
                </div>
                <div class="col-md-6"><label class="field-label">Confirm Password <span class="req">*</span></label><input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Confirm password" required><div class="invalid-feedback" id="confirmPasswordError"></div></div>
                <div class="col-md-6">
                    <label class="field-label">Gender</label>
                    <div class="d-flex gap-3 pt-2">
                        <label><input type="radio" name="gender" value="male" {{ old('gender')==='male'?'checked':'' }}> Male</label>
                        <label><input type="radio" name="gender" value="female" {{ old('gender')==='female'?'checked':'' }}> Female</label>
                        <label><input type="radio" name="gender" value="others" {{ old('gender')==='others'?'checked':'' }}> Others</label>
                    </div>
                </div>
                <div class="col-md-6"><label class="field-label">Profile Picture</label><input type="file" name="profile_image" class="form-control" accept="image/*"></div>
            </div>
            <div class="btn-row">
                <button type="button" class="btn btn-nav btn-back" disabled>Back</button>
                <button type="button" class="btn btn-nav btn-next step-next" data-go="2">Next</button>
            </div>
            <div class="seller-login">Already have an account? <a href="{{ route('vendor.login') }}">Login</a></div>
        </div>

        <div class="pane" data-pane="2">
            <h4 class="section-title">Business Details</h4>
            <div class="row g-3">
                <div class="col-md-6"><label class="field-label">Business Name</label><input type="text" name="business_name" class="form-control" value="{{ old('business_name') }}"></div>
                <div class="col-md-6"><label class="field-label">Business Mobile</label><input type="text" name="business_phone" class="form-control" value="{{ old('business_phone') }}"></div>
                <div class="col-12"><label class="field-label">Business Email</label><input type="email" name="business_email" class="form-control" value="{{ old('business_email') }}"></div>
                <div class="col-12"><label class="field-label">Logo</label><input type="file" name="business_logo" class="form-control" accept="image/*"></div>
                <div class="col-12">
                    <label class="field-label">Address <span class="req">*</span></label>
                    <input type="text" id="address_autocomplete" name="address" class="form-control" value="{{ old('address') }}" placeholder="Type and select address" required>
                    <div class="field-help">Start typing address and select from suggestions.</div>
                </div>
                <div class="col-12"><label class="field-label">Description</label><textarea name="business_description" class="form-control">{{ old('business_description') }}</textarea></div>
                <div class="col-md-6"><label class="field-label">PAN Number</label><input type="text" name="pan_number" class="form-control text-uppercase" value="{{ old('pan_number') }}"></div>
                <div class="col-md-6"><label class="field-label">GST Number</label><input type="text" name="gst_number" class="form-control text-uppercase" value="{{ old('gst_number') }}"></div>
            </div>
            <div class="btn-row">
                <button type="button" class="btn btn-nav btn-back step-back" data-go="1">Back</button>
                <button type="button" class="btn btn-nav btn-next step-next" data-go="3">Next</button>
            </div>
        </div>

        <div class="pane" data-pane="3">
            <h4 class="section-title">Bank Details</h4>
            <div class="row g-3">
                <div class="col-md-6"><label class="field-label">Bank Name</label><input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}"></div>
                <div class="col-md-6"><label class="field-label">Branch Name</label><input type="text" name="branch_name" class="form-control" value="{{ old('branch_name') }}"></div>
                <div class="col-12"><label class="field-label">Account Type</label><input type="text" name="account_type" class="form-control" value="{{ old('account_type') }}"></div>
                <div class="col-12"><label class="field-label">Account Number</label><input type="text" name="bank_account" class="form-control" value="{{ old('bank_account') }}"></div>
                <div class="col-12"><label class="field-label">IFSC Code</label><input type="text" name="ifsc_code" class="form-control text-uppercase" value="{{ old('ifsc_code') }}"></div>
            </div>
            <div class="btn-row">
                <button type="button" class="btn btn-nav btn-back step-back" data-go="2">Back</button>
                <button type="button" class="btn btn-nav btn-next step-next" data-go="4">Next</button>
            </div>
        </div>

        <div class="pane" data-pane="4">
            <h4 class="section-title">Documents Upload</h4>
            <div class="row g-3">
                <div class="col-12">
                    <label class="field-label">Aadhar Card</label>
                    <div class="file-drop">
                        <input type="file" name="aadhaar_card" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                </div>
                <div class="col-12">
                    <label class="field-label">PAN Card</label>
                    <div class="file-drop">
                        <input type="file" name="pan_card" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                </div>
                <input type="hidden" name="tax_name" value="{{ old('tax_name') }}">
                <input type="hidden" name="tax_number" value="{{ old('tax_number') }}">
                <input type="hidden" name="account_holder_name" value="{{ old('account_holder_name') }}">
            </div>
            <div class="btn-row">
                <button type="button" class="btn btn-nav btn-back step-back" data-go="3">Back</button>
                <button type="submit" class="btn btn-nav btn-next">Submit</button>
            </div>
        </div>
    </form>
</div>

<script>
(function () {
    const form = document.getElementById('vendorRegisterForm');
    const steps = Array.from(document.querySelectorAll('#stepper .step'));
    const panes = Array.from(document.querySelectorAll('.pane'));
    const pass = document.getElementById('password');
    const cpass = document.getElementById('password_confirmation');
    const err = document.getElementById('confirmPasswordError');
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');
    const addressInput = document.getElementById('address_autocomplete');
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    let current = 1;

    function showStep(step) {
        current = step;
        panes.forEach(p => p.classList.toggle('active', Number(p.dataset.pane) === step));
        steps.forEach((s, idx) => {
            const n = idx + 1;
            s.classList.remove('active', 'done');
            if (n < step) s.classList.add('done');
            else if (n === step) s.classList.add('active');
        });
    }

    function evaluatePasswordStrength(value) {
        let score = 0;
        if (value.length >= 8) score++;
        if (/[a-z]/.test(value)) score++;
        if (/[A-Z]/.test(value)) score++;
        if (/\d/.test(value)) score++;
        if (/[^\w\s]/.test(value)) score++;
        const widthMap = ['0%', '20%', '40%', '60%', '80%', '100%'];
        const labelMap = ['-', 'Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
        const colorMap = ['#ef4444', '#f97316', '#f59e0b', '#f59e0b', '#22c55e', '#16a34a'];
        strengthFill.style.width = widthMap[score];
        strengthFill.style.background = colorMap[score];
        strengthText.textContent = 'Password strength: ' + labelMap[score];
    }

    function validateConfirmPassword() {
        if (!cpass.value) {
            cpass.setCustomValidity('Please confirm your password.');
            err.textContent = 'Please confirm your password.';
            return false;
        }
        if (pass.value !== cpass.value) {
            cpass.setCustomValidity('Passwords do not match.');
            err.textContent = 'Passwords do not match.';
            return false;
        }
        cpass.setCustomValidity('');
        err.textContent = '';
        return true;
    }

    document.querySelectorAll('.step-next').forEach(btn => {
        btn.addEventListener('click', function () {
            if (current === 1 && !validateConfirmPassword()) return;
            showStep(Number(this.dataset.go));
        });
    });
    document.querySelectorAll('.step-back').forEach(btn => {
        btn.addEventListener('click', function () { showStep(Number(this.dataset.go)); });
    });

    pass.addEventListener('input', function () { evaluatePasswordStrength(pass.value); validateConfirmPassword(); });
    cpass.addEventListener('input', validateConfirmPassword);

    if (addressInput && latInput && lngInput) {
        addressInput.addEventListener('input', function () { latInput.value = ''; lngInput.value = ''; });
    }

    form.addEventListener('submit', function (event) {
        if (!validateConfirmPassword()) { event.preventDefault(); event.stopPropagation(); showStep(1); }
    });

    form.querySelectorAll('input[type="file"]').forEach((input, index) => {
        if (!input.id) input.id = 'vendor-file-' + index;
        input.addEventListener('change', function () {
            const previewId = 'preview-' + input.id;
            const ex = document.getElementById(previewId);
            if (ex) ex.remove();
            if (!input.files || !input.files.length) return;
            const file = input.files[0];
            if (!file.type || !file.type.startsWith('image/')) return;
            const reader = new FileReader();
            reader.onload = function (evt) {
                const wrap = document.createElement('div');
                wrap.id = previewId;
                wrap.className = 'file-preview-wrap';
                wrap.innerHTML = '<small>Preview</small><img alt="Selected image">';
                wrap.querySelector('img').src = evt.target.result;
                input.insertAdjacentElement('afterend', wrap);
            };
            reader.readAsDataURL(file);
        });
    });

    showStep(1);
})();

function initVendorAddressAutocomplete() {
    const addressInput = document.getElementById('address_autocomplete');
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    if (!addressInput || !latInput || !lngInput || typeof google === 'undefined' || !google.maps || !google.maps.places) return;
    const ac = new google.maps.places.Autocomplete(addressInput, { fields: ['formatted_address', 'geometry'], types: ['address'] });
    ac.addListener('place_changed', function () {
        const place = ac.getPlace();
        if (!place || !place.geometry || !place.geometry.location) {
            latInput.value = ''; lngInput.value = ''; return;
        }
        addressInput.value = place.formatted_address || addressInput.value;
        latInput.value = place.geometry.location.lat().toFixed(7);
        lngInput.value = place.geometry.location.lng().toFixed(7);
    });
}
</script>
@if($googleMapsApiKey !== '')
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places&callback=initVendorAddressAutocomplete"></script>
@endif
</body>
</html>
