@extends('layouts.admin-auth', [
    'pageTitle' => $pageTitle ?? 'Verify OTP - Moaahar Admin',
    'panelHeading' => 'Password Recovery',
    'panelText' => "Don't worry, we've got you covered. Follow the steps to regain access to your admin portal.",
])

@section('auth-content')
    <h2>Enter OTP</h2>
    <p class="form-lead">We've sent a 4-digit code to <strong>{{ $email }}</strong>.</p>

    @if(session('error'))
        <div class="admin-auth-alert admin-auth-alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="admin-auth-alert admin-auth-alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route($verifyOtpRoute ?? 'verify.otp') }}" method="POST" id="otpForm" novalidate>
        @csrf
        <input type="hidden" name="otp" id="otpHidden" value="{{ old('otp') }}">

        <div class="otp-boxes" id="otpBoxes">
            @for($i = 0; $i < 4; $i++)
                <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]" aria-label="OTP digit {{ $i + 1 }}" data-otp-index="{{ $i }}">
            @endfor
        </div>
        @error('otp')<span class="field-error d-block text-center mb-3">{{ $message }}</span>@enderror

        <button type="submit" class="admin-auth-btn">Verify &amp; Proceed</button>
    </form>

    <div class="admin-auth-resend">
        <form action="{{ route($resendOtpRoute ?? 'resend.otp') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit"><i class="ri-refresh-line"></i> Resend OTP</button>
        </form>
    </div>

    <a href="{{ $backToLoginUrl ?? url('/') }}" class="admin-auth-back">
        <i class="ri-arrow-left-line"></i> {{ $backToLoginText ?? 'Back to Login' }}
    </a>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var boxes = Array.from(document.querySelectorAll('.otp-box'));
    var hidden = document.getElementById('otpHidden');
    var form = document.getElementById('otpForm');

    function syncHidden() {
        hidden.value = boxes.map(function (b) { return b.value; }).join('');
    }

    var oldOtp = (hidden.value || '').replace(/\D/g, '').slice(0, 4);
    oldOtp.split('').forEach(function (digit, idx) {
        if (boxes[idx]) boxes[idx].value = digit;
    });

    boxes.forEach(function (box, index) {
        box.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(-1);
            syncHidden();
            if (this.value && boxes[index + 1]) {
                boxes[index + 1].focus();
            }
        });

        box.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !this.value && boxes[index - 1]) {
                boxes[index - 1].focus();
            }
        });

        box.addEventListener('paste', function (e) {
            e.preventDefault();
            var pasted = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 4);
            pasted.split('').forEach(function (digit, idx) {
                if (boxes[idx]) boxes[idx].value = digit;
            });
            syncHidden();
            if (boxes[Math.min(pasted.length, 3)]) {
                boxes[Math.min(pasted.length, 3)].focus();
            }
        });
    });

    if (form) {
        form.addEventListener('submit', function (e) {
            syncHidden();
            if (hidden.value.length !== 4) {
                e.preventDefault();
                boxes[0].focus();
            }
        });
    }

    if (boxes[0]) boxes[0].focus();
});
</script>
@endpush
