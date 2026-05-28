<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Registration</title>
    @include('layouts.favicon-dynamic')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            background: #eef0f3;
            font-family: Arial, Helvetica, sans-serif;
            color: #1f2937;
            padding: 24px 12px;
        }
        .register-wrap {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
            padding: 20px 18px 24px;
        }
        .brand-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 4px;
        }
        .brand-row img {
            height: 22px;
            width: auto;
        }
        .title {
            font-size: 31px;
            font-weight: 700;
            margin: 0;
            color: #111827;
        }
        .stepper {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin: 14px 0 18px;
        }
        .step {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #ef4444;
        }
        .step .dot {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #ef4444;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
        }
        .step-line {
            width: 34px;
            height: 2px;
            background: #fca5a5;
        }
        .section-title {
            font-size: 22px;
            font-weight: 700;
            margin: 14px 0 10px;
            color: #111827;
        }
        .field-label {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 5px;
            color: #374151;
        }
        .req {
            color: #ef4444;
            font-weight: 700;
        }
        .form-control, .form-select {
            min-height: 40px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            font-size: 13px;
        }
        textarea.form-control {
            min-height: 78px;
        }
        .field-help {
            font-size: 11px;
            margin-top: 4px;
            color: #6b7280;
        }
        .strength-wrap {
            margin-top: 7px;
        }
        .strength-track {
            height: 5px;
            background: #e5e7eb;
            border-radius: 999px;
            overflow: hidden;
        }
        .strength-fill {
            height: 100%;
            width: 0;
            background: #ef4444;
            transition: width .15s linear;
        }
        .strength-text {
            margin-top: 4px;
            font-size: 11px;
            color: #6b7280;
        }
        .btn-row {
            margin-top: 16px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn-primary-custom {
            background: #ef4444;
            border: 1px solid #ef4444;
            color: #fff;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            padding: 8px 20px;
        }
        .btn-primary-custom:hover {
            background: #dc2626;
            border-color: #dc2626;
            color: #fff;
        }
        .btn-outline-custom {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            padding: 8px 20px;
            background: #fff;
            color: #374151;
        }
        .seller-login-link {
            display: inline-block;
            margin-top: 10px;
            font-size: 12px;
            color: #111827;
            text-decoration: none;
            font-weight: 600;
        }
        .seller-login-link:hover { text-decoration: underline; }
        .invalid-feedback { display: block; }
        .pac-container {
            z-index: 9999 !important;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.1);
            font-family: Arial, Helvetica, sans-serif;
        }
        .file-preview-wrap {
            margin-top: 8px;
            display: inline-flex;
            flex-direction: column;
            gap: 4px;
        }
        .file-preview-wrap .label {
            font-size: 11px;
            color: #6b7280;
        }
        .file-preview-wrap img {
            max-width: 140px;
            max-height: 140px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            object-fit: cover;
            background: #f9fafb;
            padding: 2px;
        }
        @media (max-width: 768px) {
            .title { font-size: 23px; }
            .step-line { width: 18px; }
        }
    </style>
</head>
<body>
    @php
        $logoUrl = asset('public/uploads/settings/moaahar-logo.png');
        $googleMapsApiKey = (string) config('services.google_maps.api_key', '');
    @endphp
    <div class="register-wrap">
        <div class="brand-row">
            <img src="{{ $logoUrl }}" alt="mo-ahaar">
            <h1 class="title">Vendor Registration</h1>
        </div>

        <div class="stepper">
            <div class="step"><span class="dot">1</span><span>Personal Details</span></div>
            <span class="step-line"></span>
            <div class="step"><span class="dot">2</span><span>Business Details</span></div>
            <span class="step-line"></span>
            <div class="step"><span class="dot">3</span><span>Bank Details</span></div>
            <span class="step-line"></span>
            <div class="step"><span class="dot">4</span><span>Documents Upload</span></div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                Please correct the highlighted fields.
            </div>
        @endif

        <form action="{{ route('vendor.register.submit') }}" method="POST" enctype="multipart/form-data" id="vendorRegisterForm" novalidate>
            @csrf

            <div class="section-title">Personal Details</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="field-label">Name <span class="req">*</span></label>
                    <input type="text" name="owner_name" class="form-control @error('owner_name') is-invalid @enderror" value="{{ old('owner_name') }}" placeholder="Vendor Name" required>
                    @error('owner_name')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-6">
                    <label class="field-label">Mobile <span class="req">*</span></label>
                    <input type="text" name="mobile" maxlength="10" class="form-control @error('mobile') is-invalid @enderror" value="{{ old('mobile') }}" placeholder="Enter Mobile" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)" pattern="[6-9][0-9]{9}" required>
                    <div class="field-help">Use 10 digits, starting from 6-9.</div>
                    @error('mobile')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="col-md-6">
                    <label class="field-label">Email <span class="req">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Enter Email" required>
                    @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-6">
                    <label class="field-label">Password <span class="req">*</span></label>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Enter Password" required>
                    <div class="field-help">Minimum 8 chars with uppercase, lowercase, number, special character.</div>
                    <div class="strength-wrap">
                        <div class="strength-track"><div class="strength-fill" id="strengthFill"></div></div>
                        <div class="strength-text" id="strengthText">Password strength: -</div>
                    </div>
                    @error('password')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="col-md-6">
                    <label class="field-label">Confirm Password <span class="req">*</span></label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Enter Confirm Password" required>
                    <div class="invalid-feedback" id="confirmPasswordError"></div>
                </div>

                <div class="col-md-6">
                    <label class="field-label">DOB</label>
                    <input type="date" name="dob" class="form-control @error('dob') is-invalid @enderror" value="{{ old('dob') }}">
                    @error('dob')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="col-md-6">
                    <label class="field-label">Gender</label>
                    <div class="gender-row mt-2">
                        <label><input type="radio" name="gender" value="male" {{ old('gender') === 'male' ? 'checked' : '' }}> Male</label>
                        <label><input type="radio" name="gender" value="female" {{ old('gender') === 'female' ? 'checked' : '' }}> Female</label>
                        <label><input type="radio" name="gender" value="others" {{ old('gender') === 'others' ? 'checked' : '' }}> Others</label>
                    </div>
                    @error('gender')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="col-md-12">
                    <label class="field-label">Address <span class="req">*</span></label>
                    <input type="text" id="address_autocomplete" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address') }}" placeholder="Type and select address" required>
                    <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude') }}">
                    <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude') }}">
                    <div class="field-help">Start typing address and select from suggestions.</div>
                    @error('address')<small class="text-danger">{{ $message }}</small>@enderror
                    @error('latitude')<small class="text-danger">{{ $message }}</small>@enderror
                    @error('longitude')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="col-md-6">
                    <label class="field-label">Profile Picture</label>
                    <input type="file" name="profile_image" class="form-control @error('profile_image') is-invalid @enderror" accept="image/*">
                    @error('profile_image')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>

            <div class="section-title">Business Details</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="field-label">Business Name <span class="req">*</span></label>
                    <input type="text" name="business_name" class="form-control @error('business_name') is-invalid @enderror" value="{{ old('business_name') }}" placeholder="Store Name" required>
                    @error('business_name')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="col-md-6">
                    <label class="field-label">Logo</label>
                    <input type="file" name="business_logo" class="form-control @error('business_logo') is-invalid @enderror" accept="image/*">
                    @error('business_logo')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="col-md-12">
                    <label class="field-label">Description <span class="req">*</span></label>
                    <textarea name="business_description" class="form-control @error('business_description') is-invalid @enderror" rows="2" placeholder="Store Description" required>{{ old('business_description') }}</textarea>
                    @error('business_description')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>

            <div class="section-title">Documents Upload</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="field-label">GST File</label>
                    <input type="file" name="gst_file" class="form-control @error('gst_file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                    @error('gst_file')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="col-md-6">
                    <label class="field-label">Gumasta / Other License</label>
                    <input type="file" name="food_license_file" class="form-control @error('food_license_file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                    @error('food_license_file')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="col-md-6">
                    <label class="field-label">Bank Passbook</label>
                    <input type="file" name="bank_passbook_file" class="form-control @error('bank_passbook_file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                    @error('bank_passbook_file')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="col-md-6">
                    <label class="field-label">Address Proof <span class="req">*</span></label>
                    <input type="file" name="address_proof_file" class="form-control @error('address_proof_file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                    @error('address_proof_file')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="col-md-6">
                    <label class="field-label">National Identity Card</label>
                    <input type="file" name="national_identity_card_file" class="form-control @error('national_identity_card_file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                    @error('national_identity_card_file')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>

            <div class="section-title">Store Tax Details</div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="field-label">Tax Name</label>
                    <input type="text" name="tax_name" class="form-control @error('tax_name') is-invalid @enderror" value="{{ old('tax_name') }}" placeholder="GST">
                    @error('tax_name')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-4">
                    <label class="field-label">Tax Number</label>
                    <input type="text" name="tax_number" class="form-control @error('tax_number') is-invalid @enderror" value="{{ old('tax_number') }}" placeholder="GSTIN1234">
                    @error('tax_number')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-4">
                    <label class="field-label">PAN Number</label>
                    <input type="text" name="pan_number" class="form-control @error('pan_number') is-invalid @enderror text-uppercase" value="{{ old('pan_number') }}" placeholder="ABCDE1234F" maxlength="10">
                    <div class="field-help">Format: ABCDE1234F</div>
                    @error('pan_number')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-12">
                    <label class="field-label">GST Number</label>
                    <input type="text" name="gst_number" class="form-control @error('gst_number') is-invalid @enderror text-uppercase" value="{{ old('gst_number') }}" placeholder="22AAAAA0000A1Z5" maxlength="15">
                    <div class="field-help">Format: 15 characters GSTIN.</div>
                    @error('gst_number')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>

            <div class="section-title">Bank Details</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="field-label">Account Number <span class="req">*</span></label>
                    <input type="text" name="bank_account" class="form-control @error('bank_account') is-invalid @enderror" value="{{ old('bank_account') }}" placeholder="Account Number" oninput="this.value=this.value.replace(/\D/g,'').slice(0,18)" required>
                    <div class="field-help">Use 9 to 18 digits.</div>
                    @error('bank_account')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-6">
                    <label class="field-label">Account Holder Name <span class="req">*</span></label>
                    <input type="text" name="account_holder_name" class="form-control @error('account_holder_name') is-invalid @enderror" value="{{ old('account_holder_name') }}" placeholder="Account Holder Name" required>
                    @error('account_holder_name')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-6">
                    <label class="field-label">IFSC Code <span class="req">*</span></label>
                    <input type="text" name="ifsc_code" class="form-control @error('ifsc_code') is-invalid @enderror text-uppercase" value="{{ old('ifsc_code') }}" placeholder="SBIN0001234" maxlength="11" required>
                    <div class="field-help">Format: 4 letters + 0 + 6 alphanumeric.</div>
                    @error('ifsc_code')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-6">
                    <label class="field-label">Bank Name <span class="req">*</span></label>
                    <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror" value="{{ old('bank_name') }}" placeholder="Bank Name" required>
                    @error('bank_name')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>

            <div class="btn-row">
                <button type="reset" class="btn btn-outline-custom">Reset</button>
                <button type="submit" class="btn btn-primary-custom">Submit</button>
            </div>

            <a href="{{ route('vendor.login') }}" class="seller-login-link">Vendor Login</a>
        </form>
    </div>

    <script>
        (function () {
            const form = document.getElementById('vendorRegisterForm');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('password_confirmation');
            const confirmPasswordError = document.getElementById('confirmPasswordError');
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            const addressInput = document.getElementById('address_autocomplete');
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');

            function evaluatePasswordStrength(value) {
                let score = 0;
                if (value.length >= 8) score++;
                if (/[a-z]/.test(value)) score++;
                if (/[A-Z]/.test(value)) score++;
                if (/\d/.test(value)) score++;
                if (/[^\w\s]/.test(value)) score++;

                const widthMap = ['0%', '20%', '40%', '60%', '80%', '100%'];
                const colorMap = ['#ef4444', '#f97316', '#f59e0b', '#10b981', '#0ea5e9', '#22c55e'];
                const labelMap = ['-', 'Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];

                strengthFill.style.width = widthMap[score];
                strengthFill.style.background = colorMap[score];
                strengthText.textContent = 'Password strength: ' + labelMap[score];
            }

            function validateConfirmPassword() {
                if (!confirmPassword.value) {
                    confirmPassword.setCustomValidity('Please confirm your password.');
                    confirmPasswordError.textContent = 'Please confirm your password.';
                    return;
                }

                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Passwords do not match.');
                    confirmPasswordError.textContent = 'Passwords do not match.';
                } else {
                    confirmPassword.setCustomValidity('');
                    confirmPasswordError.textContent = '';
                }
            }

            password.addEventListener('input', function () {
                evaluatePasswordStrength(password.value);
                validateConfirmPassword();
            });

            confirmPassword.addEventListener('input', validateConfirmPassword);

            if (addressInput && latInput && lngInput) {
                addressInput.addEventListener('input', function () {
                    latInput.value = '';
                    lngInput.value = '';
                });
            }

            form.addEventListener('submit', function (event) {
                validateConfirmPassword();

                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                form.classList.add('was-validated');
            });

            const fileInputs = form.querySelectorAll('input[type="file"]');
            fileInputs.forEach(function (fileInput, index) {
                if (!fileInput.id) {
                    fileInput.id = 'vendor-file-' + index;
                }

                fileInput.addEventListener('change', function () {
                    const files = fileInput.files;
                    const previewId = 'preview-' + fileInput.id;
                    const existing = document.getElementById(previewId);

                    if (existing) {
                        existing.remove();
                    }

                    if (!files || !files.length) {
                        return;
                    }

                    const file = files[0];
                    if (!file.type || !file.type.startsWith('image/')) {
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function (evt) {
                        const wrap = document.createElement('div');
                        wrap.id = previewId;
                        wrap.className = 'file-preview-wrap';
                        wrap.innerHTML = '<small class="label">Preview</small><img alt="Selected image preview">';
                        wrap.querySelector('img').src = evt.target.result;
                        fileInput.insertAdjacentElement('afterend', wrap);
                    };
                    reader.readAsDataURL(file);
                });
            });
        })();

        function initVendorAddressAutocomplete() {
            const addressInput = document.getElementById('address_autocomplete');
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            if (!addressInput || !latInput || !lngInput || typeof google === 'undefined' || !google.maps || !google.maps.places) {
                return;
            }

            const autocomplete = new google.maps.places.Autocomplete(addressInput, {
                fields: ['formatted_address', 'geometry'],
                types: ['address'],
            });

            autocomplete.addListener('place_changed', function () {
                const place = autocomplete.getPlace();
                if (!place || !place.geometry || !place.geometry.location) {
                    latInput.value = '';
                    lngInput.value = '';
                    return;
                }

                addressInput.value = place.formatted_address || addressInput.value;
                latInput.value = place.geometry.location.lat().toFixed(7);
                lngInput.value = place.geometry.location.lng().toFixed(7);
            });
        }
    </script>
    @if($googleMapsApiKey !== '')
        <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places&callback=initVendorAddressAutocomplete"></script>
    @else
        <script>
            console.warn('GOOGLE_MAPS_API_KEY is not configured. Address suggestions are disabled.');
        </script>
    @endif
</body>
</html>
