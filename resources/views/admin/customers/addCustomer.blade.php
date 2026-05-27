@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="title-header option-title d-flex align-items-center mb-4">
                    <h5><i class="ri-user-add-line me-2"></i>{{ $title }}</h5>
                    <a href="{{ route('admin.customers') }}" class="btn btn-outline-secondary btn-sm ms-auto">
                        <i class="ri-arrow-left-line me-1"></i>Back to Customers
                    </a>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ri-error-warning-line me-2"></i>Please fix the highlighted fields and try again.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card customer-form-card">
                    <div class="card-body">
                        <form class="theme-form theme-form-2 mega-form" action="{{ route('admin.store-customer') }}" method="post" id="addCustomerForm" novalidate>
                            @csrf

                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <div class="field-box">
                                        <label class="form-label-title mb-2">Customer Name <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-user-line"></i></span>
                                            <input class="form-control @error('customer_name') is-invalid @enderror" name="customer_name" type="text" value="{{ old('customer_name') }}" placeholder="Enter full customer name" maxlength="40" pattern="[A-Za-z ]+" data-alpha-name data-label="Customer Name" autocomplete="name">
                                        </div>
                                        @error('customer_name')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="field-box">
                                        <label class="form-label-title mb-2">Email <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-mail-line"></i></span>
                                            <input class="form-control @error('customer_email') is-invalid @enderror" name="customer_email" type="email" value="{{ old('customer_email') }}" placeholder="customer@email.com">
                                        </div>
                                        @error('customer_email')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="field-box">
                                        <label class="form-label-title mb-2">Phone Number <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-phone-line"></i></span>
                                            <input class="form-control @error('customer_phone') is-invalid @enderror" name="customer_phone" type="text" maxlength="10" inputmode="numeric" pattern="[0-9]{10}" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)" value="{{ old('customer_phone') }}" placeholder="10-digit phone">
                                        </div>
                                        @error('customer_phone')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="field-box">
                                        <label class="form-label-title mb-2">Date of Birth</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-cake-2-line"></i></span>
                                            <input class="form-control @error('customer_dob') is-invalid @enderror" name="customer_dob" type="date" value="{{ old('customer_dob') }}">
                                        </div>
                                        @error('customer_dob')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="field-box">
                                        <label class="form-label-title mb-2">Gender</label>
                                        <select class="form-select @error('gender') is-invalid @enderror" name="gender">
                                            <option value="">Select gender</option>
                                            <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                                            <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                                            <option value="Other" {{ old('gender') === 'Other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                        @error('gender')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="field-box">
                                        <label class="form-label-title mb-2">Status</label>
                                        <div class="d-flex align-items-center status-toggle-wrap">
                                            <label class="switch mb-0 me-2">
                                                <input type="checkbox" name="customer_status" value="1" {{ old('customer_status', '1') ? 'checked' : '' }}>
                                                <span class="switch-state"></span>
                                            </label>
                                            <span class="text-muted">Active customer account</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="field-box">
                                        <label class="form-label-title mb-2">Address <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-map-pin-line"></i></span>
                                            <textarea class="form-control @error('customer_address') is-invalid @enderror" name="customer_address" rows="3" maxlength="255" placeholder="Complete customer address" data-address-field>{{ old('customer_address') }}</textarea>
                                        </div>
                                        @error('customer_address')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="field-box">
                                        <label class="form-label-title mb-2">Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-lock-line"></i></span>
                                            <input class="form-control @error('customer_password') is-invalid @enderror" id="customerPassword" name="customer_password" type="password" placeholder="Minimum 8 characters with mixed complexity">
                                            <button class="btn btn-outline-secondary" type="button" id="toggleCustomerPassword"><i class="ri-eye-line"></i></button>
                                        </div>
                                        @error('customer_password')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="field-box">
                                        <label class="form-label-title mb-2">Confirm Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-lock-password-line"></i></span>
                                            <input class="form-control" id="customerPasswordConfirmation" name="customer_password_confirmation" type="password" placeholder="Re-enter password">
                                            <button class="btn btn-outline-secondary" type="button" id="toggleCustomerPasswordConfirm"><i class="ri-eye-line"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('admin.customers') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button class="btn btn-theme px-4" type="submit"><i class="ri-save-line me-1"></i>Create Customer</button>
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
.customer-form-card {
    border: none;
    border-radius: 14px;
    background: linear-gradient(180deg, rgba(13,164,135,.06), #fff 40%);
}
.field-box {
    background: #f8fafb;
    border: 1px solid #eef3f4;
    border-radius: 10px;
    padding: 14px;
    height: 100%;
}
.status-toggle-wrap {
    min-height: 42px;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-alpha-name]').forEach(function (input) {
        input.addEventListener('input', function () {
            var cleanedValue = this.value.replace(/[^A-Za-z ]+/g, '').replace(/\s{2,}/g, ' ');
            if (cleanedValue !== this.value) {
                this.value = cleanedValue;
            }
        });
    });

    document.querySelectorAll('[data-address-field]').forEach(function (input) {
        input.addEventListener('input', function () {
            var cleanedValue = this.value.replace(/(.)\1{5,}/g, '$1$1$1$1$1');
            if (cleanedValue !== this.value) {
                this.value = cleanedValue;
            }
        });
    });

    function bindToggle(buttonId, inputId) {
        var button = document.getElementById(buttonId);
        if (!button) {
            return;
        }

        button.addEventListener('click', function () {
            var input = document.getElementById(inputId);
            var icon = button.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'ri-eye-off-line';
            } else {
                input.type = 'password';
                icon.className = 'ri-eye-line';
            }
        });
    }

    bindToggle('toggleCustomerPassword', 'customerPassword');
    bindToggle('toggleCustomerPasswordConfirm', 'customerPasswordConfirmation');
});
</script>
@endsection