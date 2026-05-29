@extends('layouts.admin-auth', [
    'pageTitle' => $pageTitle ?? 'Forgot Password - Moaahar Admin',
    'panelHeading' => 'Password Recovery',
    'panelText' => "Don't worry, we've got you covered. Follow the steps to regain access to your admin portal.",
])

@section('auth-content')
    <h2>Forgot Password?</h2>
    <p class="form-lead">Enter your email address and we'll send you an OTP to reset your password.</p>

    @if(session('error'))
        <div class="admin-auth-alert admin-auth-alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="admin-auth-alert admin-auth-alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route($sendOtpRoute ?? 'send.otp') }}" method="POST" novalidate>
        @csrf

        <label class="admin-auth-label" for="email">Email Address</label>
        <div class="admin-auth-input-wrap">
            <i class="ri-mail-line input-icon"></i>
            <input type="email" id="email" name="email" class="admin-auth-input @error('email') is-invalid @enderror"
                value="{{ old('email') }}" placeholder="{{ $emailPlaceholder ?? 'admin@example.com' }}" required autocomplete="email">
        </div>
        @error('email')<span class="field-error">{{ $message }}</span>@enderror

        <button type="submit" class="admin-auth-btn mt-2">Send OTP</button>
    </form>

    <a href="{{ $backToLoginUrl ?? url('/') }}" class="admin-auth-back">
        <i class="ri-arrow-left-line"></i> {{ $backToLoginText ?? 'Back to Login' }}
    </a>
@endsection
