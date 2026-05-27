<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'Reset Password - E-Commerce Admin' }}</title>
    @include('layouts.favicon-dynamic')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f9d8a, #2fd4b9);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px 16px;
            font-family: Arial, Helvetica, sans-serif;
        }

        .reset-container {
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
            color: #0f9d8a;
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
            border-color: #0f9d8a;
            box-shadow: 0 0 0 0.2rem rgba(15, 157, 138, 0.25);
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
        }

        .password-toggle-icon:hover {
            color: #333;
        }

        .btn-submit {
            background: linear-gradient(135deg, #0f9d8a, #2fd4b9);
            border: none;
            border-radius: 6px;
            padding: 12px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(15, 157, 138, 0.4);
            color: white;
        }

        .alert {
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 15px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 4px;
            border-left: 4px solid #0f9d8a;
        }

        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
        }

        .password-requirements li {
            margin: 4px 0;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #999;
            text-decoration: none;
            font-size: 13px;
        }

        .back-link a:hover {
            color: #666;
        }

        .success-icon {
            text-align: center;
            margin-bottom: 15px;
        }

        .success-icon i {
            font-size: 24px;
            color: #0f9d8a;
        }

        @media (max-width: 575.98px) {
            .reset-container { padding: 24px 18px; border-radius: 8px; }
            .logo { font-size: 22px; }
            .subtitle { font-size: 15px; margin-bottom: 18px; }
        }
    </style>
</head>

<body>
    <div class="reset-container">
        <div class="success-icon">
            <i class="ri-check-double-line"></i>
        </div>

        <div class="logo">E - Commerce</div>
        <div class="subtitle">{{ $subtitle ?? 'Reset Your Password' }}</div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route($resetPasswordRoute ?? 'reset.password') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label">New Password</label>
                <div class="password-input-wrapper">
                    <input type="password" class="form-control" id="passwordField" name="password"
                        placeholder="Enter new password" required>
                    <span class="password-toggle-icon" id="passwordToggle">
                        <i class="ri-eye-line"></i>
                    </span>
                </div>
                @error('password')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <div class="password-input-wrapper">
                    <input type="password" class="form-control" id="confirmPasswordField" name="password_confirmation"
                        placeholder="Confirm your password" required>
                    <span class="password-toggle-icon" id="confirmPasswordToggle">
                        <i class="ri-eye-line"></i>
                    </span>
                </div>
                @error('password_confirmation')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="password-requirements">
                <strong>Password Requirements:</strong>
                <ul>
                    <li>At least 8 characters long</li>
                    <li>Passwords must match</li>
                </ul>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-submit">Reset Password</button>
            </div>
        </form>

        <div class="back-link">
            <a href="{{ $backToLoginUrl ?? url('/') }}">
                <i class="ri-arrow-left-line"></i> {{ $backToLoginText ?? 'Back to Admin Login' }}
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password toggle for new password
        document.getElementById('passwordToggle').addEventListener('click', function () {
            const field = document.getElementById('passwordField');
            if (field.type === 'password') {
                field.type = 'text';
                this.innerHTML = '<i class="ri-eye-off-line"></i>';
            } else {
                field.type = 'password';
                this.innerHTML = '<i class="ri-eye-line"></i>';
            }
        });

        // Password toggle for confirm password
        document.getElementById('confirmPasswordToggle').addEventListener('click', function () {
            const field = document.getElementById('confirmPasswordField');
            if (field.type === 'password') {
                field.type = 'text';
                this.innerHTML = '<i class="ri-eye-off-line"></i>';
            } else {
                field.type = 'password';
                this.innerHTML = '<i class="ri-eye-line"></i>';
            }
        });
    </script>
</body>

</html>
