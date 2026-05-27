<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'Forgot Password - E-Commerce Admin' }}</title>
    @include('layouts.favicon-dynamic')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.min.css">
    <style>
        body {
            background-image: url("public/assets/images/adminBg.png");
            min-height: 100vh;
            display: flex;
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
            justify-content: flex-end;
            align-items: center;
            padding: 20px 16px;
            font-family: 'Inter', sans-serif !important;
        }

        .reset-container {
            position: relative;
            background: linear-gradient(145deg, rgba(18, 18, 18, .96), rgba(6, 6, 6, .98));
            border: 1px solid rgba(217, 155, 47, .75);
            border-radius: 18px;
            padding: 42px 38px;
            width: 100%;
            max-width: 460px;
            box-shadow:
                0 0 0 1px rgba(217, 155, 47, .15),
                0 28px 80px rgba(0, 0, 0, .55),
                inset 0 0 35px rgba(255, 255, 255, .03);
            color: #fff;
            overflow: hidden;
            margin-right: 60px;
        }

        .reset-container::before {
            content: "";
            position: absolute;
            top: -90px;
            left: 50%;
            transform: translateX(-50%);
            width: 170px;
            height: 170px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(217, 155, 47, .35), transparent 70%);
            pointer-events: none;
        }

        .logo {
            text-align: center;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .logo img {
            max-height: 115px !important;
            filter: brightness(1.15);
        }

        .subtitle {
            text-align: center;
            font-size: 28px;
            color: #fff;
            margin-bottom: 26px;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }

        .form-label {
            font-weight: 700;
            color: #f5f5f5;
            margin-bottom: 9px;
            font-size: 14px;
        }

        .form-control {
            height: 52px;
            border-radius: 7px;
            border: 1px solid rgba(255, 255, 255, .18);
            padding: 12px 15px;
            font-size: 14px;
            background: rgba(255, 255, 255, .035);
            color: #fff;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, .55);
        }

        .form-control:focus {
            background: rgba(255, 255, 255, .055);
            border-color: #d99b2f;
            color: #fff;
            box-shadow: 0 0 0 .18rem rgba(217, 155, 47, .20);
        }

        .btn-submit {
            background: linear-gradient(135deg, #d99b2f 0%, #f3c45b 55%, #c78619 100%);
            color: #111 !important;
            border: none;
            border-radius: 10px;
            padding: 10px;
            font-weight: 700;
            transition: all .3s ease;
            box-shadow: 0 12px 28px rgba(217, 155, 47, .32);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 38px rgba(217, 155, 47, .45);
            background: linear-gradient(135deg, #d99b2f 0%, #f3c45b 55%, #c78619 100%);
            color: #111 !important;
        }

        .alert {
            border-radius: 8px;
            padding: 14px 18px;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(120, 20, 24, .48), rgba(70, 10, 12, .35));
            color: #ff9ca1;
            border: 1px solid rgba(255, 92, 100, .65);
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(24, 120, 70, .35), rgba(9, 64, 40, .35));
            color: #8ff0b7;
            border: 1px solid rgba(70, 220, 130, .55);
        }

        .alert .btn-close {
            filter: invert(1);
            opacity: .75;
        }

        .info-text {
            font-size: 13px;
            color: rgba(255, 255, 255, .72);
            margin-top: 15px;
            padding: 13px 14px;
            background: rgba(217, 155, 47, .08);
            border-radius: 8px;
            border-left: 4px solid #d99b2f;
        }

        .info-text i {
            color: #d99b2f;
        }

        .back-link {
            text-align: center;
            margin-top: 22px;
        }

        .back-link a {
            color: #d99b2f;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: color .3s ease;
        }

        .back-link a:hover {
            color: #f3c45b;
            text-decoration: none;
        }

        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        textarea:-webkit-autofill,
        select:-webkit-autofill {
            -webkit-text-fill-color: #fff !important;
            caret-color: #fff;
            transition: background-color 9999s ease-in-out 0s;
            -webkit-box-shadow: 0 0 0px 1000px rgba(255, 255, 255, .035) inset !important;
            box-shadow: 0 0 0px 1000px rgba(255, 255, 255, .035) inset !important;
            border: 1px solid rgba(255, 255, 255, .18) !important;
        }

        @media (max-width: 1199px) {
            body {
                padding-right: 5vw;
            }

            .reset-container {
                margin-right: 20px;
                max-width: 460px;
            }
        }

        @media (max-width: 991px) {
            body {
                justify-content: center;
                padding: 25px 16px;
            }

            .reset-container {
                margin-right: 0;
            }
        }

        @media (max-width: 575.98px) {
            .reset-container {
                padding: 30px 22px;
                border-radius: 16px;
                max-width: 100%;
            }

            .subtitle {
                font-size: 22px;
            }
        }
    </style>
</head>

<body>
    <div class="row m-0 w-100">
        <div class="col-md-6">

        </div>
        <div class="col-md-6">
            <div class="reset-container">
                <div class="logo">
                    @php
                        $logoUrl = !empty($globalStoreSetting) && !empty($globalStoreSetting->logo)
                            ? asset('public/uploads/settings/' . $globalStoreSetting->logo)
                            : asset('public/assets/images/logo/1.png');
                    @endphp
                    <img src="{{ $logoUrl }}" alt="logo" style="height:100px; object-fit:cover;">
                </div>
                <div class="subtitle">{{ $subtitle ?? 'Forgot Password' }}</div>

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ri-error-warning-line"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ri-check-line"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route($sendOtpRoute ?? 'send.otp') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email" value="{{ old('email') }}"
                            placeholder="{{ $emailPlaceholder ?? 'Enter your admin email' }}" required>
                        @error('email')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="info-text">
                        <i class="ri-mail-line"></i>
                        {{ $infoText ?? "We'll send you a 6-digit OTP to verify your identity" }}
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-submit">Send OTP</button>
                    </div>
                </form>

                <div class="back-link">
                    <a href="{{ $backToLoginUrl ?? url('/') }}">
                        <i class="ri-arrow-left-line"></i> {{ $backToLoginText ?? 'Back to Admin Login' }}
                    </a>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>