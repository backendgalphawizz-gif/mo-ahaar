<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Login</title>
    @include('layouts.favicon-dynamic')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.min.css">
    @include('auth.partials.vendor-auth-styles')
</head>

<body>
@php
    $logoUrl = asset('public/uploads/settings/moaahar-logo.png');
@endphp
    <div class="vendor-auth-card">
        <img src="{{ $logoUrl }}" alt="moaahar" class="vendor-auth-logo">
        <h1 class="vendor-auth-title">Vendor Login</h1>
        <p class="vendor-auth-subtitle">Sign in to manage your venues</p>

        @if (session('success'))
            <div class="session-banner success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="session-banner error">{{ session('error') }}</div>
        @endif

        <form action="{{ route('vendor.login.send-otp') }}" method="POST" novalidate>
            @csrf
            <div class="mb-3">
                <label class="field-label" for="mobile">Mobile Number</label>
                <div class="input-with-icon">
                    <i class="ri-phone-line"></i>
                    <input type="text" id="mobile" name="mobile" class="form-control @error('mobile') is-invalid @enderror"
                        value="{{ old('mobile', session('vendor_login_mobile')) }}"
                        placeholder="+91 1234567890" maxlength="10" inputmode="numeric" autocomplete="tel"
                        oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)">
                </div>
                @error('mobile')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-vendor-primary">Send OTP</button>
        </form>

        <div class="vendor-auth-footer">
            Don't have an account? <a href="{{ route('vendor.register') }}">Register Now</a>
        </div>
    </div>
</body>

</html>
