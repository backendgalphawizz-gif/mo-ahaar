<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Vendor Login</title>
    @include('layouts.favicon-dynamic')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.min.css">
    @include('auth.partials.vendor-auth-styles')
</head>

<body>
@php
    $logoUrl = asset('public/uploads/settings/moaahar-logo.png');
    $mobile = (string) ($maskedMobile ?? session('vendor_login_mobile', ''));
    $resendSeconds = (int) ($resendAfterSeconds ?? 0);
@endphp
    <div class="vendor-auth-card">
        <img src="{{ $logoUrl }}" alt="moaahar" class="vendor-auth-logo">
        <h1 class="vendor-auth-title">Verify OTP</h1>
        <p class="vendor-auth-subtitle">Enter the OTP sent to your mobile</p>

        @if ($mobile !== '')
            <p class="mobile-display">OTP sent to <strong>{{ $mobile }}</strong></p>
        @endif

        @if (session('success'))
            <div class="session-banner success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="session-banner error">{{ session('error') }}</div>
        @endif
        @if (config('app.debug') && session('dev_otp'))
            <div class="session-banner info">Dev OTP: <strong>{{ session('dev_otp') }}</strong></div>
        @endif

        <form action="{{ route('vendor.login.verify.submit') }}" method="POST" novalidate>
            @csrf
            <input type="hidden" name="mobile" value="{{ session('vendor_login_mobile_raw') }}">

            <div class="mb-3">
                <label class="field-label" for="otp">Enter OTP</label>
                <input type="text" id="otp" name="otp" class="form-control @error('otp') is-invalid @enderror"
                    value="{{ old('otp') }}" placeholder="0000" maxlength="4" inputmode="numeric" pattern="[0-9]{4}"
                    autocomplete="one-time-code" autofocus
                    oninput="this.value=this.value.replace(/\D/g,'').slice(0,4)">
                @error('otp')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-vendor-primary">Verify &amp; Login</button>
        </form>

        <form action="{{ route('vendor.login.resend-otp') }}" method="POST" id="resendOtpForm" class="resend-wrap">
            @csrf
            <span id="resendTimerText">
                @if ($resendSeconds > 0)
                    @php
                        $mm = str_pad((string) intdiv($resendSeconds, 60), 2, '0', STR_PAD_LEFT);
                        $ss = str_pad((string) ($resendSeconds % 60), 2, '0', STR_PAD_LEFT);
                    @endphp
                    Resend OTP in <span id="resendCountdown">{{ $mm }}:{{ $ss }}</span>
                @else
                    <button type="submit" id="resendBtn">Resend OTP</button>
                @endif
            </span>
        </form>

        <div class="text-center">
            <a href="{{ route('vendor.login') }}" class="change-mobile">Change mobile number</a>
        </div>
    </div>

    <script>
        (function () {
            var seconds = {{ max(0, $resendSeconds) }};
            var countdownEl = document.getElementById('resendCountdown');
            var timerText = document.getElementById('resendTimerText');
            var form = document.getElementById('resendOtpForm');

            if (!seconds || !countdownEl) {
                return;
            }

            var btn = document.getElementById('resendBtn');
            if (btn) {
                btn.disabled = true;
            }

            var interval = setInterval(function () {
                seconds -= 1;
                if (seconds <= 0) {
                    clearInterval(interval);
                    timerText.innerHTML = '<button type="submit" id="resendBtn">Resend OTP</button>';
                    return;
                }
                var mm = String(Math.floor(seconds / 60)).padStart(2, '0');
                var ss = String(seconds % 60).padStart(2, '0');
                countdownEl.textContent = mm + ':' + ss;
            }, 1000);
        })();
    </script>
</body>

</html>
