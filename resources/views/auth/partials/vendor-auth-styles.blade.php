<style>
    body {
        background: #f3f4f6;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px 16px;
        font-family: Arial, Helvetica, sans-serif;
    }

    .vendor-auth-card {
        width: 100%;
        max-width: 420px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 32px 28px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
    }

    .vendor-auth-logo {
        display: block;
        height: 42px;
        margin: 0 auto 20px;
        object-fit: contain;
    }

    .vendor-auth-title {
        text-align: center;
        font-size: 24px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 6px;
    }

    .vendor-auth-subtitle {
        text-align: center;
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 24px;
    }

    .field-label {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
        display: block;
    }

    .input-with-icon {
        position: relative;
    }

    .input-with-icon i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 18px;
    }

    .input-with-icon .form-control {
        padding-left: 40px;
        min-height: 44px;
        border-radius: 8px;
        border: 1px solid #d1d5db;
        font-size: 14px;
    }

    .input-with-icon .form-control:focus {
        border-color: #ef4444;
        box-shadow: 0 0 0 0.15rem rgba(239, 68, 68, 0.15);
    }

    .field-error {
        display: block;
        color: #dc2626;
        font-size: 12px;
        margin-top: 4px;
        line-height: 1.3;
    }

    .btn-vendor-primary {
        width: 100%;
        min-height: 44px;
        border: none;
        border-radius: 8px;
        background: #ef4444;
        color: #fff;
        font-size: 15px;
        font-weight: 700;
    }

    .btn-vendor-primary:hover {
        background: #dc2626;
        color: #fff;
    }

    .btn-vendor-primary:disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }

    .vendor-auth-footer {
        text-align: center;
        margin-top: 18px;
        font-size: 13px;
        color: #6b7280;
    }

    .vendor-auth-footer a {
        color: #111827;
        font-weight: 700;
        text-decoration: none;
    }

    .vendor-auth-footer a:hover {
        text-decoration: underline;
    }

    .session-banner {
        font-size: 13px;
        border-radius: 8px;
        padding: 10px 12px;
        margin-bottom: 14px;
    }

    .session-banner.success {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }

    .session-banner.error {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .session-banner.info {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }

    .mobile-display {
        text-align: center;
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 16px;
    }

    .mobile-display strong {
        color: #111827;
    }

    .resend-wrap {
        text-align: center;
        margin-top: 14px;
        font-size: 13px;
        color: #6b7280;
    }

    .resend-wrap button {
        border: none;
        background: none;
        color: #ef4444;
        font-weight: 600;
        padding: 0;
    }

    .resend-wrap button:disabled {
        color: #9ca3af;
        cursor: not-allowed;
    }

    .change-mobile {
        display: inline-block;
        margin-top: 10px;
        font-size: 13px;
        color: #6b7280;
        text-decoration: none;
    }

    .change-mobile:hover {
        color: #111827;
    }
</style>
