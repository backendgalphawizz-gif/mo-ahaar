<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Login - E-Commerce Dashboard</title>
    @include('layouts.favicon-dynamic')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('public/assets/css/customeCss.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px 16px;
            font-family: Arial, Helvetica, sans-serif;
        }

        .login-container {
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }

        .logo {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
            font-weight: 500;
        }

        .form-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 6px;
            border: 1px solid #ddd;
            padding: 12px;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .login-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 6px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .password-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-input-wrapper .form-control {
            padding-right: 45px;
        }

        .password-toggle-icon {
            position: absolute;
            right: 12px;
            cursor: pointer;
            font-size: 18px;
            color: #666;
            user-select: none;
            display: flex;
            align-items: center;
        }

        .password-toggle-icon:hover {
            color: #333;
        }

        .form-check-label {
            font-size: 14px;
            color: #666;
        }

        .error-message {
            color: #dc3545;
            font-size: 14px;
            margin-bottom: 15px;
            padding: 10px;
            background: #f8d7da;
            border-radius: 4px;
            border-left: 4px solid #dc3545;
        }

        .vendor-links {
            margin-top: 25px;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .vendor-links a {
            display: block;
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            margin: 10px 0;
            transition: color 0.3s ease;
        }

        .vendor-links a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .vendor-links .separator {
            color: #ccc;
            margin: 5px 0;
        }

        .go-to-website {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            margin-top: 15px;
        }

        .go-to-website a {
            color: #28a745;
            font-weight: 600;
        }

        .go-to-website a:hover {
            color: #218838;
        }

        @media (max-width: 575.98px) {
            .login-container { padding: 24px 18px; border-radius: 8px; }
            .logo { font-size: 22px; }
            .subtitle { font-size: 15px; margin-bottom: 18px; }
            .vendor-links { padding-top: 12px; margin-top: 14px; }
            .vendor-links a { font-size: 13px; margin: 7px 0; }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="logo">E - Commerce</div>
        <div class="subtitle">Vendor Dashboard Login</div>

        @if(session('error'))
            <div class="error-message">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success py-2">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('vendor.login.submit') }}" method="POST" id="vendorLoginForm" class="w-100">
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

            <div class="vendor-links">
                @if(Route::has('vendor.register'))
                    <a href="{{ route('vendor.register') }}">
                        <i class="ri-user-add-line"></i> Don't have any account?
                    </a>
                @else
                    <a href="#" onclick="alert('Vendor registration coming soon!'); return false;" style="opacity: 0.6;">
                        <i class="ri-user-add-line"></i> Don't have any account?
                    </a>
                @endif

                @if(Route::has('vendor.forgot-password'))
                    <a href="{{ route('vendor.forgot-password') }}">
                        <i class="ri-lock-password-line"></i> Forgot Password
                    </a>
                @else
                    <a href="#" onclick="alert('Password reset feature coming soon!'); return false;" style="opacity: 0.6;">
                        <i class="ri-lock-password-line"></i> Forgot Password
                    </a>
                @endif

                <div class="go-to-website">
                    <a href="#">
                        <i class="ri-home-4-line"></i> Go To Website
                    </a>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordField = document.getElementById('passwordField');
            const passwordToggle = document.getElementById('passwordToggle');

            if (passwordToggle) {
                passwordToggle.addEventListener('click', function() {
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
