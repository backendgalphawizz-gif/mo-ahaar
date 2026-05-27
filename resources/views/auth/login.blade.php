<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $loginTitle ?? 'Ecommerce Login' }}</title>
    @include('layouts.favicon-dynamic')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('public/assets/css/customeCss.css') }}" rel="stylesheet">

</head>

<body>
    <div class="row m-0 w-100">
        <div class="col-md-6">

        </div>
        <div class="col-md-6">
            <div class="login-container">
                <div class="logo">
                    @php
                        $logoUrl = !empty($globalStoreSetting) && !empty($globalStoreSetting->logo)
                            ? asset('public/uploads/settings/' . $globalStoreSetting->logo)
                            : asset('public/assets/images/logo/1.png');
                    @endphp
                    <img src="{{ $logoUrl }}" alt="logo" style="height:100px;object-fit:cover;">
                </div>
                <div class="subtitle">{{ $loginTitle ?? 'Admin Login' }}</div>
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                <form action="{{ route('admin.login.submit') }}" method="POST" id="checkLoginForm"
                    class="w-100 mt-4 pt-2">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email" value="{{ old('email') }}"
                            placeholder="Enter your email" required>
                        @error('email')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="password-input-wrapper">
                            <input type="password" class="form-control" id="passwordField" name="password"
                                placeholder="Enter your password" required>
                            <span class="password-toggle-icon" id="passwordToggle">
                                <i class="ri-eye-line"></i>
                            </span>
                        </div>
                        @error('password')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="rememberMe" name="remember">
                        <label class="form-check-label" for="rememberMe">
                            Remember Me
                        </label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn login-btn text-white">Login</button>
                    </div>
                    <div class="extra-links">
                        <a href="{{ route('forgot.password') }}"><i class="ri-lock-password-line"></i> Forgot
                            Password?</a>

                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        body {
            background-image: url("{{ asset('public/assets/images/adminBg.png') }}");
            min-height: 100vh;
            display: flex;
            background-repeat: no-repeat;
            background-size: cover;
            justify-content: center;
            align-items: center;
            padding: 20px 16px;
            font-family: 'Inter', sans-serif !important;
        }

        .login-container {
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
        }

        .login-container::before {
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
            /* margin-bottom: 18px; */
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
        }

        /* .subtitle::before {
            content: "\eb7b";
            font-family: "remixicon";
            width: 66px;
            height: 66px;
            border-radius: 50%;
            background: radial-gradient(circle, #d99b2f, #6b4a12);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            font-size: 30px;
            box-shadow: 0 0 0 12px rgba(217, 155, 47, .08), 0 0 30px rgba(217, 155, 47, .45);
        } */

        .alert-danger {
            background: linear-gradient(135deg, rgba(120, 20, 24, .48), rgba(70, 10, 12, .35));
            color: #ff9ca1;
            border: 1px solid rgba(255, 92, 100, .65);
            border-radius: 8px;
            padding: 14px 18px;
            font-size: 14px;
        }

        .alert-danger .btn-close {
            filter: invert(1);
            opacity: .75;
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

        .password-input-wrapper .form-control {
            padding-right: 48px;
        }

        .password-input-wrapper {
            position: relative;
        }

        .password-toggle-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translate(0, -50%);
            right: 15px;
            color: #fff;
            font-size: 18px;

        }

        .password-toggle-icon:hover {
            color: #d99b2f;
        }

        .form-check {
            margin-top: 6px;
        }

        .form-check-input {
            background-color: transparent;
            border: 1px solid rgba(255, 255, 255, .45);
        }

        .form-check-input:checked {
            background-color: #d99b2f;
            border-color: #d99b2f;
        }

        .form-check-label {
            font-size: 13px;
            color: rgba(255, 255, 255, .70);
        }

        .login-btn {
            background: linear-gradient(135deg, #d99b2f 0%, #f3c45b 55%, #c78619 100%);
            color: #111 !important;
            border: none;
            border-radius: 10px;
            padding: 8px;
            font-weight: 600;
            transition: all .3s ease;
            box-shadow: 0 12px 28px rgba(217, 155, 47, .32);
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 38px rgba(217, 155, 47, .45);
            background: linear-gradient(135deg, #d99b2f 0%, #f3c45b 55%, #c78619 100%);

        }

        .extra-links {
            margin-top: 22px;
            text-align: center;
            display: flex;
            justify-content: center;
        }

        .extra-links a {
            color: #d99b2f;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        .extra-links a:hover {
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

        @media (max-width: 575.98px) {
            .login-container {
                padding: 30px 22px;
                border-radius: 16px;
                max-width: 100%;
            }

            .subtitle {
                font-size: 22px;
            }
        }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.min.css">

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordField = document.getElementById('passwordField');
            const passwordToggle = document.getElementById('passwordToggle');

            if (passwordToggle) {
                passwordToggle.addEventListener('click', function () {
                    if (passwordField.type === 'password') {
                        passwordField.type = 'text';
                        passwordToggle.innerHTML = '<i class="ri-eye-off-line"></i>';
                    } else {
                        passwordField.type = 'password';
                        passwordToggle.innerHTML = '<i class="ri-eye-line"></i>';
                    }
                });
            }
        });
    </script>
</body>

</html>