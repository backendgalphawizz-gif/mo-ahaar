<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Registration - E-Commerce</title>
    @include('layouts.favicon-dynamic')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: Arial, Helvetica, sans-serif;
            padding: 24px 10px;
        }

        .register-wrap {
            max-width: 760px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #e2e3f5;
            border-radius: 12px;
            padding: 20px 22px;
            box-shadow: 0 12px 34px rgba(38, 28, 77, 0.25);
        }

        .brand {
            text-align: center;
            margin-bottom: 8px;
            font-size: 34px;
            color: #667eea;
            font-weight: 700;
            letter-spacing: 2px;
        }

        .title {
            font-size: 22px;
            font-weight: 500;
            color: #333;
            margin-bottom: 12px;
        }

        .section-title {
            font-size: 18px;
            color: #4a4a4a;
            border-top: 1px solid #ececff;
            padding-top: 14px;
            margin-top: 16px;
            margin-bottom: 12px;
        }

        .req {
            color: #e31b23;
            font-weight: 700;
        }

        label {
            font-size: 13px;
            font-weight: 600;
            color: #3d3d3d;
        }

        .form-control,
        .form-select {
            border-radius: 6px;
            font-size: 13px;
            min-height: 36px;
            border: 1px solid #d8daef;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .hint {
            color: #d00000;
            font-size: 11px;
            margin-top: 4px;
        }

        .gender-row {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
            font-size: 13px;
        }

        .btn-row {
            margin-top: 18px;
            display: flex;
            gap: 10px;
        }

        .btn-warning {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: #fff;
            font-weight: 700;
            border-radius: 6px;
            font-size: 12px;
            padding: 7px 18px;
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #5d74e7 0%, #6d419a 100%);
            color: #fff;
            transform: translateY(-1px);
        }

        .seller-login-link {
            color: #667eea;
            font-size: 12px;
            text-decoration: none;
            font-weight: 700;
            margin-top: 10px;
            display: inline-block;
        }

        .seller-login-link:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .field-help {
            font-size: 11px;
            color: #6b6f8f;
            margin-top: 4px;
        }

        .strength-wrap {
            margin-top: 8px;
        }

        .strength-track {
            height: 6px;
            background: #ececff;
            border-radius: 999px;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            width: 0;
            transition: width 0.2s ease;
            background: #ef4444;
        }

        .strength-text {
            font-size: 11px;
            margin-top: 4px;
            color: #6b7280;
        }

        .invalid-feedback {
            display: block;
        }

        @media (max-width: 767px) {
            .register-wrap {
                padding: 16px;
            }

            .title {
                font-size: 20px;
            }
        }

        @media (max-width: 575.98px) {
            .register-wrap {
                padding: 14px 12px;
                border-radius: 8px;
            }
            .brand { font-size: 26px; }
            .title { font-size: 17px; }
            .section-title { font-size: 15px; }
            .btn-row { flex-direction: column; }
            .btn-row .btn-warning,
            .btn-row .seller-login-link { width: 100%; text-align: center; }
        }
    </style>
</head>

<body>
    <div class="register-wrap">
        <div class="brand">E - Commerce</div>
        <div class="title">Vendor Registration</div>

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

            <div class="section-title">Vendor Details</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label>Name <span class="req">*</span></label>
                    <input type="text" name="owner_name" class="form-control @error('owner_name') is-invalid @enderror" value="{{ old('owner_name') }}" placeholder="Vendor Name" required>
                    @error('owner_name')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-6">
                    <label>Mobile <span class="req">*</span></label>
                    <input type="text" name="mobile" maxlength="10" class="form-control @error('mobile') is-invalid @enderror" value="{{ old('mobile') }}" placeholder="Enter Mobile" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)" pattern="[6-9][0-9]{9}" required>
                    <div class="field-help">Use 10 digits, starting from 6-9.</div>
                    @error('mobile')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="col-md-6">
                    <label>Email <span class="req">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Enter Email" required>
                    @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-6">
                    <label>Password <span class="req">*</span></label>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Enter Password" required>
                    <div class="field-help">Minimum 8 chars with uppercase, lowercase, number, special character.</div>
                    <div class="strength-wrap">
                        <div class="strength-track"><div class="strength-fill" id="strengthFill"></div></div>
                        <div class="strength-text" id="strengthText">Password strength: -</div>
                    </div>
                    @error('password')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="col-md-6">
                    <label>Confirm Password <span class="req">*</span></label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Enter Confirm Password" required>
                    <div class="invalid-feedback" id="confirmPasswordError"></div>
                </div>

                <div class="col-md-6">
                    <label>DOB</label>
                    <input type="date" name="dob" class="form-control @error('dob') is-invalid @enderror" value="{{ old('dob') }}">
                    @error('dob')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="col-md-6">
                    <label>Gender</label>
                    <div class="gender-row mt-2">
                        <label><input type="radio" name="gender" value="male" {{ old('gender') === 'male' ? 'checked' : '' }}> Male</label>
                        <label><input type="radio" name="gender" value="female" {{ old('gender') === 'female' ? 'checked' : '' }}> Female</label>
                        <label><input type="radio" name="gender" value="others" {{ old('gender') === 'others' ? 'checked' : '' }}> Others</label>
                    </div>
                    @error('gender')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="col-md-12">
                    <label>Address <span class="req">*</span></label>
                    <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2" placeholder="Enter Address" required>{{ old('address') }}</textarea>
                    @error('address')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="col-md-6">
                    <label>Profile Picture</label>
                    <input type="file" name="profile_image" class="form-control @error('profile_image') is-invalid @enderror" accept="image/*">
                    @error('profile_image')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>

            <div class="section-title">Store Details</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label>Name <span class="req">*</span></label>
                    <input type="text" name="business_name" class="form-control @error('business_name') is-invalid @enderror" value="{{ old('business_name') }}" placeholder="Store Name" required>
                    @error('business_name')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="col-md-6">
                    <label>Logo</label>
                    <input type="file" name="business_logo" class="form-control @error('business_logo') is-invalid @enderror" accept="image/*">
                    @error('business_logo')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="col-md-12">
                    <label>Description <span class="req">*</span></label>
                    <textarea name="business_description" class="form-control @error('business_description') is-invalid @enderror" rows="2" placeholder="Store Description" required>{{ old('business_description') }}</textarea>
                    @error('business_description')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="col-md-6">
                    <label>Latitude</label>
                    <input type="text" name="latitude" class="form-control @error('latitude') is-invalid @enderror" value="{{ old('latitude') }}" placeholder="Latitude">
                    @error('latitude')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="col-md-6">
                    <label>Longitude</label>
                    <input type="text" name="longitude" class="form-control @error('longitude') is-invalid @enderror" value="{{ old('longitude') }}" placeholder="Longitude">
                    @error('longitude')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>

            <div class="section-title">KYC Documents</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label>GST File</label>
                    <input type="file" name="gst_file" class="form-control @error('gst_file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                    @error('gst_file')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="col-md-6">
                    <label>Gumasta / Other License</label>
                    <input type="file" name="food_license_file" class="form-control @error('food_license_file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                    @error('food_license_file')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="col-md-6">
                    <label>Bank Passbook</label>
                    <input type="file" name="bank_passbook_file" class="form-control @error('bank_passbook_file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                    @error('bank_passbook_file')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="col-md-6">
                    <label>Address Proof <span class="req">*</span></label>
                    <input type="file" name="address_proof_file" class="form-control @error('address_proof_file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                    @error('address_proof_file')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="col-md-6">
                    <label>National Identity Card</label>
                    <input type="file" name="national_identity_card_file" class="form-control @error('national_identity_card_file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                    @error('national_identity_card_file')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>

            <div class="section-title">Store Tax Details</div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label>Tax Name</label>
                    <input type="text" name="tax_name" class="form-control @error('tax_name') is-invalid @enderror" value="{{ old('tax_name') }}" placeholder="GST">
                    @error('tax_name')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-4">
                    <label>Tax Number</label>
                    <input type="text" name="tax_number" class="form-control @error('tax_number') is-invalid @enderror" value="{{ old('tax_number') }}" placeholder="GSTIN1234">
                    @error('tax_number')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-4">
                    <label>PAN Number</label>
                    <input type="text" name="pan_number" class="form-control @error('pan_number') is-invalid @enderror text-uppercase" value="{{ old('pan_number') }}" placeholder="ABCDE1234F" maxlength="10">
                    <div class="field-help">Format: ABCDE1234F</div>
                    @error('pan_number')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-12">
                    <label>GST Number</label>
                    <input type="text" name="gst_number" class="form-control @error('gst_number') is-invalid @enderror text-uppercase" value="{{ old('gst_number') }}" placeholder="22AAAAA0000A1Z5" maxlength="15">
                    <div class="field-help">Format: 15 characters GSTIN.</div>
                    @error('gst_number')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>

            <div class="section-title">Bank Details</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label>Account Number <span class="req">*</span></label>
                    <input type="text" name="bank_account" class="form-control @error('bank_account') is-invalid @enderror" value="{{ old('bank_account') }}" placeholder="Account Number" oninput="this.value=this.value.replace(/\D/g,'').slice(0,18)" required>
                    <div class="field-help">Use 9 to 18 digits.</div>
                    @error('bank_account')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-6">
                    <label>Account Holder Name <span class="req">*</span></label>
                    <input type="text" name="account_holder_name" class="form-control @error('account_holder_name') is-invalid @enderror" value="{{ old('account_holder_name') }}" placeholder="Account Holder Name" required>
                    @error('account_holder_name')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-6">
                    <label>IFSC Code <span class="req">*</span></label>
                    <input type="text" name="ifsc_code" class="form-control @error('ifsc_code') is-invalid @enderror text-uppercase" value="{{ old('ifsc_code') }}" placeholder="SBIN0001234" maxlength="11" required>
                    <div class="field-help">Format: 4 letters + 0 + 6 alphanumeric.</div>
                    @error('ifsc_code')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-6">
                    <label>Bank Name <span class="req">*</span></label>
                    <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror" value="{{ old('bank_name') }}" placeholder="Bank Name" required>
                    @error('bank_name')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>

            <div class="btn-row">
                <button type="reset" class="btn btn-warning">Reset</button>
                <button type="submit" class="btn btn-warning">Submit</button>
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

            form.addEventListener('submit', function (event) {
                validateConfirmPassword();

                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                form.classList.add('was-validated');
            });
        })();
    </script>
</body>

</html>
