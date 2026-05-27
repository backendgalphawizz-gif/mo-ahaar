<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle ?? 'Verify OTP - E-Commerce Admin'); ?></title>
    <?php echo $__env->make('layouts.favicon-dynamic', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.min.css">
    <style>
        body {
            background-image: url("<?php echo e(asset('public/assets/images/adminBg.png')); ?>");
            min-height: 100vh;
            display: flex;
            background-repeat: no-repeat;
            background-size: cover;
            justify-content: center;
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

        .email-display {
            background: rgba(217, 155, 47, .08);
            border: 1px solid rgba(217, 155, 47, .30);
            padding: 12px 14px;
            border-radius: 8px;
            text-align: center;
            font-size: 13px;
            color: rgba(255, 255, 255, .78);
            margin-bottom: 18px;
        }

        .email-display i,
        .email-display strong {
            color: #d99b2f;
        }

        .info-text {
            font-size: 13px;
            color: rgba(255, 255, 255, .72);
            margin-bottom: 15px;
            padding: 13px 14px;
            background: rgba(217, 155, 47, .08);
            border-radius: 8px;
            border-left: 4px solid #d99b2f;
        }

        .info-text i {
            color: #d99b2f;
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

        .otp-input {
            text-align: center;
            letter-spacing: 8px;
            font-size: 22px;
            font-weight: 700;
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
            padding: 8px;
            font-weight: 600;
            transition: all .3s ease;
            box-shadow: 0 12px 28px rgba(217, 155, 47, .32);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 38px rgba(217, 155, 47, .45);
            background: linear-gradient(135deg, #d99b2f 0%, #f3c45b 55%, #c78619 100%);
            color: #111 !important;
        }

        .resend-link,
        .back-link {
            text-align: center;
            margin-top: 18px;
        }

        .resend-link .btn-link,
        .back-link a {
            color: #d99b2f;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            border: none;
            background: none;
            cursor: pointer;
            padding: 0;
        }

        .resend-link .btn-link:hover,
        .back-link a:hover {
            color: #f3c45b;
            text-decoration: none;
        }

        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus {
            -webkit-text-fill-color: #fff !important;
            caret-color: #fff;
            transition: background-color 9999s ease-in-out 0s;
            -webkit-box-shadow: 0 0 0px 1000px rgba(255, 255, 255, .035) inset !important;
            box-shadow: 0 0 0px 1000px rgba(255, 255, 255, .035) inset !important;
            border: 1px solid rgba(255, 255, 255, .18) !important;
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

            .email-display {
                font-size: 12px;
                word-break: break-all;
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
                    <?php
                        $logoUrl = !empty($globalStoreSetting) && !empty($globalStoreSetting->logo)
                            ? asset('public/uploads/settings/' . $globalStoreSetting->logo)
                            : asset('public/assets/images/logo/1.png');
                    ?>
                    <img src="<?php echo e($logoUrl); ?>" alt="logo" style="height:100px;object-fit:cover;">
                </div>
                <div class="subtitle"><?php echo e($subtitle ?? 'Verify OTP'); ?></div>

                <?php if(session('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ri-error-warning-line"></i> <?php echo e(session('error')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if(session('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ri-check-line"></i> <?php echo e(session('success')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="email-display">
                    <i class="ri-mail-line"></i> Verification code sent to: <strong><?php echo e($email); ?></strong>
                </div>

                <form action="<?php echo e(route($verifyOtpRoute ?? 'verify.otp')); ?>" method="POST">
                    <?php echo csrf_field(); ?>

                    <div class="info-text">
                        <i class="ri-information-line"></i> Enter the 6-digit OTP sent to your email
                    </div>

                    <div class="mb-3">
                        <label class="form-label">OTP Code</label>
                        <input type="text" class="form-control" name="otp" placeholder="000000" maxlength="6"
                            pattern="[0-9]{6}" required autofocus value="<?php echo e(old('otp')); ?>">
                        <?php $__errorArgs = ['otp'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <small class="text-danger"><?php echo e($message); ?></small>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-submit">Verify OTP</button>
                    </div>
                </form>

                <div class="resend-link">
                    <form action="<?php echo e(route($resendOtpRoute ?? 'resend.otp')); ?>" method="POST" style="display: inline;">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn-link"
                            style="border: none; background: none; cursor: pointer; padding: 0;">
                            <i class="ri-refresh-line"></i> Resend OTP
                        </button>
                    </form>
                </div>

                <div class="back-link">
                    <a href="<?php echo e($backToLoginUrl ?? url('/')); ?>">
                        <i class="ri-arrow-left-line"></i> <?php echo e($backToLoginText ?? 'Back to Admin Login'); ?>

                    </a>
                </div>
            </div>

        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Allow only numbers in OTP input
        document.querySelector('[name="otp"]').addEventListener('input', function (e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>

</html><?php /**PATH /home/developmentalpha/public_html/swastik-food-machinery.developmentalphawizz.com/resources/views/auth/verify-otp.blade.php ENDPATH**/ ?>