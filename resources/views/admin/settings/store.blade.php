@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-fluid">
            <style>
                .web-settings-shell {
                    width: 100%;
                    min-height: 122px;
                    padding: 24px 28px;
                    margin-bottom: 25px;
                    border-radius: 14px;
                    position: relative;
                    overflow: hidden;
                    
                    color: #fff;
                    background: radial-gradient(circle at 78% 45%, rgba(184, 135, 43, 0.26) 0%, rgba(184, 135, 43, 0.08) 22%, transparent 45%), linear-gradient(135deg, #070707 0%, #111111 48%, #171717 100%);
                    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.12);
                }

                .web-settings-shell::before {
                    content: "";
                    position: absolute;
                    width: 380px;
                    height: 380px;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.1);
                    top: -170px;
                    right: -120px;
                }

                .web-settings-shell::after {
                    content: "";
                    position: absolute;
                    width: 260px;
                    height: 260px;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.08);
                    bottom: -120px;
                    left: -60px;
                }

                .web-settings-hero {
                    position: relative;
                    z-index: 1;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 16px;
                    color: #f0fdfa;
                }

                .web-settings-title {
                    margin: 0;
                    font-size: 24px;
                    font-weight: 700;
                    letter-spacing: 0.2px;
                }

                .web-settings-subtitle {
                    margin: 6px 0 0;
                    font-size: 13px;
                    opacity: 0.95;
                }

                .web-settings-badge {
                    background: rgba(255, 255, 255, 0.16);
                    border: 1px solid rgba(255, 255, 255, 0.35);
                    color: #ecfeff;
                    border-radius: 999px;
                    padding: 8px 14px;
                    font-size: 12px;
                    font-weight: 600;
                    white-space: nowrap;
                    backdrop-filter: blur(2px);
                }

                .settings-main-card {
                    border: 0;
                    border-radius: 16px;
                    /* box-shadow: 0 14px 42px rgba(15, 23, 42, 0.09); */
                    background: none;
                }

                .settings-section {
                    border: 1px solid #e7edf4;
                    border-left: 5px solid #c18f33;
                    border-radius: 12px;
                    padding: 18px;
                    background: #fbfdff;
                    margin-bottom: 14px;
                }

                .section-head {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    margin-bottom: 12px;
                }

                .section-icon {
                    width: 34px;
                    height: 34px;
                    border-radius: 10px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    color: #c18f33;
                    background: #fff5df;
                    font-size: 18px;
                }

                .section-title {
                    margin: 0;
                    font-size: 16px;
                    font-weight: 700;
                    color: #0f172a;
                }

                .section-meta {
                    margin: 0;
                    font-size: 12px;
                    color: #64748b;
                }

                .settings-main-card .form-label {
                    color: #334155;
                    font-weight: 600;
                    font-size: 13px;
                    margin-bottom: 7px;
                }

                .settings-main-card .form-control,
                .settings-main-card .form-select {
                    border-radius: 10px;
                    border: 1px solid #dbe5ee;
                    min-height: 44px;
                    font-size: 13px;
                }

                .settings-main-card textarea.form-control {
                    min-height: 92px;
                }

                .settings-main-card .form-control:focus,
                .settings-main-card .form-select:focus {
                    border-color: #0f9d8a;
                    box-shadow: 0 0 0 0.22rem rgba(15, 157, 138, 0.16);
                }

                .is-client-invalid {
                    border-color: #ef4444 !important;
                    box-shadow: 0 0 0 0.2rem rgba(239, 68, 68, 0.12) !important;
                    background: #fff9f9;
                }

                .is-client-valid {
                    border-color: #10b981 !important;
                    box-shadow: 0 0 0 0.18rem rgba(16, 185, 129, 0.12) !important;
                }

                .premium-field-error {
                    margin-top: 6px;
                    font-size: 12px;
                    color: #dc2626;
                    display: flex;
                    align-items: center;
                    gap: 6px;
                    font-weight: 500;
                }

                .validation-summary {
                    display: none;
                    border: 1px solid #fecaca;
                    background: linear-gradient(180deg, #fff5f5 0%, #fff 100%);
                    color: #991b1b;
                    border-radius: 12px;
                    padding: 12px 14px;
                    margin-bottom: 14px;
                }

                .validation-summary.show {
                    display: block;
                    animation: softDrop 0.25s ease;
                }

                .validation-summary h6 {
                    margin: 0 0 6px;
                    font-size: 14px;
                    font-weight: 700;
                }

                .validation-summary ul {
                    margin: 0;
                    padding-left: 18px;
                    font-size: 12px;
                }

                @keyframes softDrop {
                    from {
                        transform: translateY(-4px);
                        opacity: 0;
                    }

                    to {
                        transform: translateY(0);
                        opacity: 1;
                    }
                }

                .upload-preview-box {
                    display: inline-flex;
                    align-items: center;
                    gap: 10px;
                    padding: 8px 10px;
                    border: 1px dashed #c8d8e6;
                    border-radius: 10px;
                    background: #f8fbfd;
                    margin-top: 8px;
                }

                .upload-preview-box img {
                    border: 1px solid #dfe5ec;
                    border-radius: 8px;
                    background: #fff;
                }

                .settings-sticky-actions {
                    position: sticky;
                    bottom: 10px;
                    z-index: 4;
                    background: rgba(255, 255, 255, 0.96);
                    border: 1px solid #e5edf5;
                    border-radius: 12px;
                    padding: 10px 12px;
                    display: flex;
                    justify-content: flex-end;
                    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
                    margin-top: 8px;
                }

                .settings-save-btn {
                    border-radius: 10px;
                    padding: 10px 18px;
                    font-weight: 600;
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                }

                .app-download-disabled {
                    opacity: 0.55;
                }

                @media (max-width: 991.98px) {
                    .web-settings-hero {
                        flex-direction: column;
                        align-items: flex-start;
                    }
                }

                @media (max-width: 575.98px) {
                    .web-settings-shell {
                        border-radius: 14px;
                        padding: 16px;
                    }

                    .web-settings-title {
                        font-size: 20px;
                    }

                    .settings-section {
                        padding: 14px;
                        border-radius: 12px;
                    }

                    .settings-sticky-actions {
                        position: static;
                        justify-content: stretch;
                    }

                    .settings-save-btn {
                        width: 100%;
                        justify-content: center;
                    }
                }
            </style>

            <div class="web-settings-shell">
                <div class="web-settings-hero">
                    <div>
                        <h5 class="web-settings-title"><i class="ri-settings-3-line me-2"></i>{{ $title }}</h5>
                        <p class="web-settings-subtitle">Manage your website branding, SEO details, app download section,
                            and social channels from one place.</p>
                    </div>
                    <span class="web-settings-badge"><i class="ri-flashlight-line me-1"></i> Premium Configuration
                        Panel</span>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card settings-main-card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.store.update') }}" enctype="multipart/form-data"
                        class="row g-3" id="webSettingsForm" novalidate>
                        @csrf

                        <div id="validationSummary" class="validation-summary" aria-live="polite">
                            <h6><i class="ri-error-warning-line me-1"></i>Please fix the following fields</h6>
                            <ul id="validationSummaryList"></ul>
                        </div>

                        <div class="col-12 settings-section">
                            <div class="section-head">
                                <span class="section-icon"><i class="ri-global-line"></i></span>
                                <div>
                                    <h6 class="section-title">General Web Settings</h6>
                                    <p class="section-meta">Primary website identity and contact information</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Site Title <span class="text-danger">*</span></label>
                                    <input type="text" name="site_title"
                                        class="form-control @error('site_title') is-invalid @enderror"
                                        value="{{ old('site_title', $setting->site_title ?? '') }}" minlength="2"
                                        maxlength="150" required>
                                    @error('site_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">App Name <span class="text-danger">*</span></label>
                                    <input type="text" name="app_name"
                                        class="form-control @error('app_name') is-invalid @enderror"
                                        value="{{ old('app_name', $setting->app_name ?? '') }}" minlength="2"
                                        maxlength="150" required>
                                    @error('app_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Support Number <span class="text-danger">*</span></label>
                                    <input type="text" name="support_number" id="support_number"
                                        class="form-control @error('support_number') is-invalid @enderror"
                                        value="{{ old('support_number', $setting->support_number ?? '') }}"
                                        inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10" required>
                                    @error('support_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Support Email <span class="text-danger">*</span></label>
                                    <input type="email" name="support_email"
                                        class="form-control @error('support_email') is-invalid @enderror"
                                        value="{{ old('support_email', $setting->support_email ?? '') }}" maxlength="150"
                                        required>
                                    @error('support_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Address <span class="text-danger">*</span></label>
                                    <textarea name="address" rows="2"
                                        class="form-control @error('address') is-invalid @enderror" minlength="5"
                                        maxlength="1000" required>{{ old('address', $setting->address ?? '') }}</textarea>
                                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                            </div>
                        </div>

                        <div class="col-12 settings-section">
                            <div class="section-head">
                                <span class="section-icon"><i class="ri-image-edit-line"></i></span>
                                <div>
                                    <h6 class="section-title">Branding & SEO</h6>
                                    <p class="section-meta">Upload media assets and define search metadata</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Logo</label>
                                    <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror"
                                        accept=".jpg,.jpeg,.png,.webp,.svg,.ico">
                                    <small class="text-muted">Optional. Allowed: JPG, PNG, WEBP, SVG, ICO. Max 2MB.</small>
                                    @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    @if(!empty($setting->logo))
                                        <div class="upload-preview-box">
                                            <small class="text-muted mb-0">Current Logo</small>
                                            <img src="{{ asset('public/uploads/settings/' . $setting->logo) }}" alt="logo"
                                                style="width:130px;height:54px;object-fit:contain;">
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Favicon</label>
                                    <input type="file" name="favicon"
                                        class="form-control @error('favicon') is-invalid @enderror"
                                        accept=".jpg,.jpeg,.png,.webp,.svg,.ico">
                                    <small class="text-muted">Optional. Allowed: JPG, PNG, WEBP, SVG, ICO. Max 1MB. This
                                        icon is used in browser tabs and bookmarks.</small>
                                    @error('favicon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    @if(!empty($setting->favicon))
                                        <div class="upload-preview-box">
                                            <small class="text-muted mb-0">Current Favicon</small>
                                            <img src="{{ asset('public/uploads/settings/' . $setting->favicon) }}" alt="favicon"
                                                style="width:34px;height:34px;object-fit:contain;">
                                        </div>
                                    @endif
                                </div>

                            </div>
                        </div>

                        @if(\Illuminate\Support\Facades\Schema::hasColumn('store_settings', 'customer_home_sliders_enabled'))
                            <div class="col-12 settings-section">
                                <div class="section-head">
                                    <span class="section-icon"><i class="ri-layout-grid-line"></i></span>
                                    <div>
                                        <h6 class="section-title">Customer app — home screen</h6>
                                        <p class="section-meta">Choose which blocks appear in the customer API home
                                            (<code>/api/customer-app/home/*</code>): sliders, offers, promotions, announcements,
                                            and featured products.</p>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6 col-lg-4">
                                        <label class="form-label">Banners / sliders (hero) <span
                                                class="text-danger">*</span></label>
                                        <select name="customer_home_sliders_enabled"
                                            class="form-select @error('customer_home_sliders_enabled') is-invalid @enderror"
                                            required>
                                            <option value="1" {{ old('customer_home_sliders_enabled', (int) ($setting?->customer_home_sliders_enabled ?? 1)) === 1 ? 'selected' : '' }}>
                                                Show</option>
                                            <option value="0" {{ old('customer_home_sliders_enabled', (int) ($setting?->customer_home_sliders_enabled ?? 1)) === 0 ? 'selected' : '' }}>
                                                Hide</option>
                                        </select>
                                        @error('customer_home_sliders_enabled')<div class="invalid-feedback">{{ $message }}
                                        </div>@enderror
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <label class="form-label">Offers <span class="text-danger">*</span></label>
                                        <select name="customer_home_offers_enabled"
                                            class="form-select @error('customer_home_offers_enabled') is-invalid @enderror"
                                            required>
                                            <option value="1" {{ old('customer_home_offers_enabled', (int) ($setting?->customer_home_offers_enabled ?? 1)) === 1 ? 'selected' : '' }}>
                                                Show</option>
                                            <option value="0" {{ old('customer_home_offers_enabled', (int) ($setting?->customer_home_offers_enabled ?? 1)) === 0 ? 'selected' : '' }}>
                                                Hide</option>
                                        </select>
                                        @error('customer_home_offers_enabled')<div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <label class="form-label">Promotions <span class="text-danger">*</span></label>
                                        <select name="customer_home_promotions_enabled"
                                            class="form-select @error('customer_home_promotions_enabled') is-invalid @enderror"
                                            required>
                                            <option value="1" {{ old('customer_home_promotions_enabled', (int) ($setting?->customer_home_promotions_enabled ?? 1)) === 1 ? 'selected' : '' }}>Show</option>
                                            <option value="0" {{ old('customer_home_promotions_enabled', (int) ($setting?->customer_home_promotions_enabled ?? 1)) === 0 ? 'selected' : '' }}>Hide</option>
                                        </select>
                                        @error('customer_home_promotions_enabled')<div class="invalid-feedback">{{ $message }}
                                        </div>@enderror
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <label class="form-label">Announcements <span class="text-danger">*</span></label>
                                        <select name="customer_home_announcements_enabled"
                                            class="form-select @error('customer_home_announcements_enabled') is-invalid @enderror"
                                            required>
                                            <option value="1" {{ old('customer_home_announcements_enabled', (int) ($setting?->customer_home_announcements_enabled ?? 1)) === 1 ? 'selected' : '' }}>Show</option>
                                            <option value="0" {{ old('customer_home_announcements_enabled', (int) ($setting?->customer_home_announcements_enabled ?? 1)) === 0 ? 'selected' : '' }}>Hide</option>
                                        </select>
                                        @error('customer_home_announcements_enabled')<div class="invalid-feedback">
                                        {{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <label class="form-label">Featured products <span class="text-danger">*</span></label>
                                        <select name="customer_home_featured_products_enabled"
                                            class="form-select @error('customer_home_featured_products_enabled') is-invalid @enderror"
                                            required>
                                            <option value="1" {{ old('customer_home_featured_products_enabled', (int) ($setting?->customer_home_featured_products_enabled ?? 1)) === 1 ? 'selected' : '' }}>Show</option>
                                            <option value="0" {{ old('customer_home_featured_products_enabled', (int) ($setting?->customer_home_featured_products_enabled ?? 1)) === 0 ? 'selected' : '' }}>Hide</option>
                                        </select>
                                        @error('customer_home_featured_products_enabled')<div class="invalid-feedback">
                                        {{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(\Illuminate\Support\Facades\Schema::hasColumn('store_settings', 'customer_registration_privacy_policy_enabled'))
                            <div class="col-12 settings-section">
                                <div class="section-head">
                                    <span class="section-icon"><i class="ri-file-list-3-line"></i></span>
                                    <div>
                                        <h6 class="section-title">Customer app — registration legal pages</h6>
                                        <p class="section-meta">Control what appears in registration flow via
                                            <code>/api/customer-app/auth/registration-content</code>.</p>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6 col-lg-4">
                                        <label class="form-label">Privacy policy <span class="text-danger">*</span></label>
                                        <select name="customer_registration_privacy_policy_enabled"
                                            class="form-select @error('customer_registration_privacy_policy_enabled') is-invalid @enderror"
                                            required>
                                            <option value="1" {{ old('customer_registration_privacy_policy_enabled', (int) ($setting?->customer_registration_privacy_policy_enabled ?? 1)) === 1 ? 'selected' : '' }}>Show</option>
                                            <option value="0" {{ old('customer_registration_privacy_policy_enabled', (int) ($setting?->customer_registration_privacy_policy_enabled ?? 1)) === 0 ? 'selected' : '' }}>Hide</option>
                                        </select>
                                        @error('customer_registration_privacy_policy_enabled')<div class="invalid-feedback">
                                        {{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-6 col-lg-4">
                                        <label class="form-label">Terms and conditions <span
                                                class="text-danger">*</span></label>
                                        <select name="customer_registration_terms_enabled"
                                            class="form-select @error('customer_registration_terms_enabled') is-invalid @enderror"
                                            required>
                                            <option value="1" {{ old('customer_registration_terms_enabled', (int) ($setting?->customer_registration_terms_enabled ?? 1)) === 1 ? 'selected' : '' }}>Show</option>
                                            <option value="0" {{ old('customer_registration_terms_enabled', (int) ($setting?->customer_registration_terms_enabled ?? 1)) === 0 ? 'selected' : '' }}>Hide</option>
                                        </select>
                                        @error('customer_registration_terms_enabled')<div class="invalid-feedback">
                                        {{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-6 col-lg-4">
                                        <label class="form-label">FAQ <span class="text-danger">*</span></label>
                                        <select name="customer_registration_faq_enabled"
                                            class="form-select @error('customer_registration_faq_enabled') is-invalid @enderror"
                                            required>
                                            <option value="1" {{ old('customer_registration_faq_enabled', (int) ($setting?->customer_registration_faq_enabled ?? 1)) === 1 ? 'selected' : '' }}>Show</option>
                                            <option value="0" {{ old('customer_registration_faq_enabled', (int) ($setting?->customer_registration_faq_enabled ?? 1)) === 0 ? 'selected' : '' }}>Hide</option>
                                        </select>
                                        @error('customer_registration_faq_enabled')<div class="invalid-feedback">{{ $message }}
                                        </div>@enderror
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="col-12 settings-sticky-actions">
                            <button type="submit" class="btn btn-theme settings-save-btn">
                                <i class="ri-save-3-line"></i> Update Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('webSettingsForm');
            var supportNumberInput = document.getElementById('support_number');
            var validationSummary = document.getElementById('validationSummary');
            var validationSummaryList = document.getElementById('validationSummaryList');

            var requiredFields = [
                { name: 'site_title', label: 'Site Title' },
                { name: 'app_name', label: 'App Name' },
                { name: 'support_number', label: 'Support Number' },
                { name: 'support_email', label: 'Support Email' },
                { name: 'address', label: 'Address' }
            ];

            function getField(name) {
                return form ? form.querySelector('[name="' + name + '"]') : null;
            }

            function isValidUrl(value) {
                try {
                    var url = new URL(value);
                    return url.protocol === 'http:' || url.protocol === 'https:';
                } catch (error) {
                    return false;
                }
            }

            function getFieldLabel(field) {
                if (!field) {
                    return 'Field';
                }

                var group = field.closest('.col-md-6, .col-md-4, .col-12');
                var label = group ? group.querySelector('.form-label') : null;
                if (label) {
                    return label.textContent.replace('*', '').trim();
                }

                return (field.name || 'Field')
                    .replace(/_/g, ' ')
                    .replace(/\b\w/g, function (character) {
                        return character.toUpperCase();
                    });
            }

            function showFieldError(field, message) {
                clearFieldError(field);
                field.classList.add('is-client-invalid');
                field.classList.remove('is-client-valid');

                var small = document.createElement('small');
                small.className = 'premium-field-error';
                small.innerHTML = '<i class="ri-close-circle-line"></i>' + message;
                small.setAttribute('data-client-error', '1');
                field.insertAdjacentElement('afterend', small);
            }

            function markFieldValid(field) {
                clearFieldError(field);
                field.classList.remove('is-client-invalid');
                if (field.value.trim() !== '') {
                    field.classList.add('is-client-valid');
                } else {
                    field.classList.remove('is-client-valid');
                }
            }

            function clearFieldError(field) {
                if (!field) {
                    return;
                }
                field.classList.remove('is-client-invalid');
                var next = field.nextElementSibling;
                if (next && next.getAttribute('data-client-error') === '1') {
                    next.remove();
                }
            }

            function validateRequiredField(name, label, errors) {
                var field = getField(name);
                if (!field || field.disabled) {
                    return;
                }

                var value = (field.value || '').trim();
                if (!value) {
                    var message = label + ' is required.';
                    showFieldError(field, message);
                    errors.push(message);
                    return;
                }

                markFieldValid(field);
            }

            function validateFieldLength(field, errors) {
                if (!field || field.disabled) {
                    return;
                }

                var value = (field.value || '').trim();
                if (value === '') {
                    return;
                }

                var label = getFieldLabel(field);
                var minLength = parseInt(field.getAttribute('minlength'), 10);
                var maxLength = parseInt(field.getAttribute('maxlength'), 10);

                if (!Number.isNaN(minLength) && value.length < minLength) {
                    var minMessage = label + ' must be at least ' + minLength + ' characters.';
                    showFieldError(field, minMessage);
                    errors.push(minMessage);
                    return;
                }

                if (!Number.isNaN(maxLength) && value.length > maxLength) {
                    var maxMessage = label + ' must not exceed ' + maxLength + ' characters.';
                    showFieldError(field, maxMessage);
                    errors.push(maxMessage);
                    return;
                }

                markFieldValid(field);
            }

            function validateFileInput(name, maxSizeKb, errors) {
                var field = getField(name);
                if (!field || !field.files || field.files.length === 0) {
                    return;
                }

                var file = field.files[0];
                var label = getFieldLabel(field);
                var extension = file.name.indexOf('.') !== -1 ? file.name.split('.').pop().toLowerCase() : '';
                var acceptedExtensions = (field.getAttribute('accept') || '')
                    .split(',')
                    .map(function (entry) {
                        return entry.replace('.', '').trim().toLowerCase();
                    })
                    .filter(Boolean);

                if (acceptedExtensions.length > 0 && extension !== '' && acceptedExtensions.indexOf(extension) === -1) {
                    var typeMessage = label + ' must be one of: ' + acceptedExtensions.join(', ').toUpperCase() + '.';
                    showFieldError(field, typeMessage);
                    errors.push(typeMessage);
                    return;
                }

                if (file.size > maxSizeKb * 1024) {
                    var sizeMessage = label + ' must not exceed ' + Math.floor(maxSizeKb / 1024) + 'MB.';
                    showFieldError(field, sizeMessage);
                    errors.push(sizeMessage);
                    return;
                }

                markFieldValid(field);
            }

            function validateForm() {
                if (!form) {
                    return [];
                }

                var errors = [];
                var allFields = form.querySelectorAll('input, textarea, select');
                allFields.forEach(function (field) {
                    clearFieldError(field);
                });

                requiredFields.forEach(function (entry) {
                    validateRequiredField(entry.name, entry.label, errors);
                });

                form.querySelectorAll('input[type="text"], input[type="email"], input[type="url"], textarea').forEach(function (field) {
                    validateFieldLength(field, errors);
                });

                var supportNumber = getField('support_number');
                if (supportNumber && !supportNumber.disabled && supportNumber.value.trim() !== '') {
                    var supportValue = supportNumber.value.trim();
                    if (!/^[0-9]{10}$/.test(supportValue)) {
                        var supportMessage = 'Support Number must be exactly 10 digits.';
                        showFieldError(supportNumber, supportMessage);
                        errors.push(supportMessage);
                    }
                }

                var supportEmail = getField('support_email');
                if (supportEmail && supportEmail.value.trim() !== '') {
                    var emailValue = supportEmail.value.trim();
                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailValue)) {
                        var emailMessage = 'Support Email must be a valid email address.';
                        showFieldError(supportEmail, emailMessage);
                        errors.push(emailMessage);
                    }
                }

                validateFileInput('logo', 2048, errors);
                validateFileInput('favicon', 1024, errors);

                return errors;
            }

            function renderValidationSummary(errors) {
                if (!validationSummary || !validationSummaryList) {
                    return;
                }

                if (errors.length === 0) {
                    validationSummary.classList.remove('show');
                    validationSummaryList.innerHTML = '';
                    return;
                }

                validationSummaryList.innerHTML = errors.map(function (error) {
                    return '<li>' + error + '</li>';
                }).join('');
                validationSummary.classList.add('show');
            }

            if (supportNumberInput) {
                supportNumberInput.addEventListener('input', function () {
                    this.value = this.value.replace(/\D/g, '');
                });
            }

            if (form) {
                form.addEventListener('submit', function (event) {
                    var errors = validateForm();
                    renderValidationSummary(errors);

                    if (errors.length > 0) {
                        event.preventDefault();
                        var firstInvalid = form.querySelector('.is-client-invalid');
                        if (firstInvalid) {
                            firstInvalid.focus({ preventScroll: true });
                            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        } else if (validationSummary) {
                            validationSummary.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                });

                form.querySelectorAll('input, textarea, select').forEach(function (field) {
                    field.addEventListener('input', function () {
                        if (field.classList.contains('is-client-invalid') || field.classList.contains('is-client-valid')) {
                            var errors = validateForm();
                            renderValidationSummary(errors);
                        }
                    });

                    field.addEventListener('change', function () {
                        if (field.classList.contains('is-client-invalid') || field.classList.contains('is-client-valid')) {
                            var errors = validateForm();
                            renderValidationSummary(errors);
                        }
                    });
                });
            }
        });
    </script>
@endsection