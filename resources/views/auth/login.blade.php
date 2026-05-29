@extends('layouts.admin-auth', [
    'pageTitle' => $loginTitle ?? 'Sign In - Moaahar Admin',
    'panelHeading' => 'Welcome to Moaahar Admin',
    'panelText' => "Manage users, restaurants, deliveries, and monitor your platform's success from one centralized dashboard.",
])

@section('auth-content')
    <h2>Sign in</h2>
    <p class="form-lead">Welcome back! Please enter your details.</p>

    @if(session('error'))
        <div class="admin-auth-alert admin-auth-alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="admin-auth-alert admin-auth-alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('admin.login.submit') }}" method="POST" id="checkLoginForm" novalidate>
        @csrf

        <label class="admin-auth-label" for="email">Email Address</label>
        <div class="admin-auth-input-wrap">
            <i class="ri-mail-line input-icon"></i>
            <input type="email" id="email" name="email" class="admin-auth-input @error('email') is-invalid @enderror"
                value="{{ old('email') }}" placeholder="admin@example.com" required autocomplete="email">
        </div>
        @error('email')<span class="field-error">{{ $message }}</span>@enderror

        <label class="admin-auth-label" for="passwordField">Password</label>
        <div class="admin-auth-input-wrap has-toggle">
            <i class="ri-lock-line input-icon"></i>
            <input type="password" id="passwordField" name="password"
                class="admin-auth-input @error('password') is-invalid @enderror"
                placeholder="Enter your password" required autocomplete="current-password">
            <button type="button" class="password-toggle-btn" id="passwordToggle" aria-label="Show password">
                <i class="ri-eye-line" aria-hidden="true"></i>
            </button>
        </div>
        @error('password')<span class="field-error">{{ $message }}</span>@enderror

        <div class="admin-auth-forgot">
            <a href="{{ route('forgot.password') }}">Forgot password?</a>
        </div>

        <button type="submit" class="admin-auth-btn">Sign In to Dashboard</button>
    </form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var passwordField = document.getElementById('passwordField');
    var passwordToggle = document.getElementById('passwordToggle');
    if (passwordToggle && passwordField) {
        passwordField.dataset.passwordToggleInit = '1';
        passwordToggle.addEventListener('click', function () {
            var show = passwordField.type === 'password';
            passwordField.type = show ? 'text' : 'password';
            passwordToggle.innerHTML = show
                ? '<i class="ri-eye-off-line" aria-hidden="true"></i>'
                : '<i class="ri-eye-line" aria-hidden="true"></i>';
            passwordToggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    }
});
</script>
@endpush
