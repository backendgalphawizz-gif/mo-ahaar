@extends('layouts.admin-auth', [
    'pageTitle' => $pageTitle ?? 'Reset Password - Moaahar Admin',
    'panelHeading' => 'Set New Password',
    'panelText' => 'Your identity has been verified. Please choose a strong new password for your account.',
    'showCopyright' => false,
])

@section('auth-content')
    <h2>Create New Password</h2>
    <p class="form-lead">Enter your new password below.</p>

    @if(session('error'))
        <div class="admin-auth-alert admin-auth-alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route($resetPasswordRoute ?? 'reset.password') }}" method="POST" novalidate>
        @csrf

        <label class="admin-auth-label" for="passwordField">New Password</label>
        <div class="admin-auth-input-wrap has-toggle">
            <i class="ri-lock-line input-icon"></i>
            <input type="password" id="passwordField" name="password"
                class="admin-auth-input @error('password') is-invalid @enderror"
                placeholder="Enter new password" required autocomplete="new-password">
            <button type="button" class="password-toggle-btn" id="passwordToggle" aria-label="Show password">
                <i class="ri-eye-line" aria-hidden="true"></i>
            </button>
        </div>
        @error('password')<span class="field-error">{{ $message }}</span>@enderror

        <label class="admin-auth-label" for="confirmPasswordField">Confirm Password</label>
        <div class="admin-auth-input-wrap has-toggle">
            <i class="ri-lock-line input-icon"></i>
            <input type="password" id="confirmPasswordField" name="password_confirmation"
                class="admin-auth-input @error('password_confirmation') is-invalid @enderror"
                placeholder="Confirm your password" required autocomplete="new-password">
            <button type="button" class="password-toggle-btn" id="confirmPasswordToggle" aria-label="Show password">
                <i class="ri-eye-line" aria-hidden="true"></i>
            </button>
        </div>
        @error('password_confirmation')<span class="field-error">{{ $message }}</span>@enderror

        <button type="submit" class="admin-auth-btn mt-3">Reset Password</button>
    </form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function bindToggle(toggleId, fieldId) {
        var toggle = document.getElementById(toggleId);
        var field = document.getElementById(fieldId);
        if (!toggle || !field) return;
        field.dataset.passwordToggleInit = '1';
        toggle.addEventListener('click', function () {
            var show = field.type === 'password';
            field.type = show ? 'text' : 'password';
            toggle.innerHTML = show
                ? '<i class="ri-eye-off-line" aria-hidden="true"></i>'
                : '<i class="ri-eye-line" aria-hidden="true"></i>';
            toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    }
    bindToggle('passwordToggle', 'passwordField');
    bindToggle('confirmPasswordToggle', 'confirmPasswordField');
});
</script>
@endpush
