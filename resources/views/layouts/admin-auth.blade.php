<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'Moaahar Admin' }}</title>
    @include('layouts.favicon-dynamic')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --moa-red: #ed1c24;
            --moa-red-dark: #c9161d;
            --moa-text: #111827;
            --moa-muted: #6b7280;
            --moa-border: #e5e7eb;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #fff;
            color: var(--moa-text);
        }

        .admin-auth-page {
            display: flex;
            min-height: 100vh;
        }

        .admin-auth-brand {
            flex: 1 1 50%;
            background: var(--moa-red);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 32px;
            position: relative;
            overflow: hidden;
        }

        .admin-auth-brand-inner {
            max-width: 420px;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .admin-auth-blob {
            width: min(340px, 72vw);
            aspect-ratio: 1;
            margin: 0 auto 28px;
            border-radius: 48% 52% 58% 42% / 52% 48% 55% 45%;
            background: rgba(255, 255, 255, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 36px;
        }

        .admin-auth-logo {
            max-width: 180px;
            max-height: 72px;
            width: auto;
            height: auto;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        .admin-auth-brand h1 {
            font-size: clamp(1.75rem, 3vw, 2.25rem);
            font-weight: 700;
            line-height: 1.2;
            margin: 0 0 14px;
        }

        .admin-auth-brand p {
            font-size: 15px;
            line-height: 1.65;
            color: rgba(255, 255, 255, 0.92);
            margin: 0;
        }

        .admin-auth-panel {
            flex: 1 1 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 32px 24px;
            background: #fff;
        }

        .admin-auth-form-wrap {
            width: 100%;
            max-width: 420px;
        }

        .admin-auth-form-wrap h2 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0 0 8px;
            color: var(--moa-text);
        }

        .admin-auth-form-wrap .form-lead {
            color: var(--moa-muted);
            font-size: 14px;
            margin-bottom: 28px;
            line-height: 1.5;
        }

        .admin-auth-label {
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        .admin-auth-input-wrap {
            position: relative;
            margin-bottom: 18px;
        }

        .admin-auth-input-wrap .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 18px;
            pointer-events: none;
            z-index: 2;
        }

        .admin-auth-input {
            width: 100%;
            min-height: 48px;
            border: 1px solid var(--moa-border);
            border-radius: 10px;
            padding: 12px 14px 12px 44px;
            font-size: 14px;
            color: var(--moa-text);
            background: #fff;
            transition: border-color .15s, box-shadow .15s;
        }

        .admin-auth-input.no-icon {
            padding-left: 14px;
        }

        .admin-auth-input:focus {
            outline: none;
            border-color: #fca5a5;
            box-shadow: 0 0 0 3px rgba(237, 28, 36, 0.12);
        }

        .admin-auth-input.is-invalid {
            border-color: #dc2626;
        }

        .admin-auth-input-wrap .password-toggle-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #9ca3af;
            padding: 6px 8px;
            cursor: pointer;
            z-index: 3;
            font-size: 18px;
            line-height: 1;
        }

        .admin-auth-input-wrap .password-toggle-btn:hover { color: #374151; }

        .admin-auth-input-wrap.has-toggle .admin-auth-input {
            padding-right: 44px;
        }

        .admin-auth-forgot {
            text-align: right;
            margin: -6px 0 20px;
        }

        .admin-auth-forgot a {
            color: var(--moa-red);
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
        }

        .admin-auth-forgot a:hover { text-decoration: underline; }

        .admin-auth-btn {
            width: 100%;
            min-height: 48px;
            border: none;
            border-radius: 10px;
            background: var(--moa-red);
            color: #fff !important;
            font-size: 15px;
            font-weight: 600;
            transition: background .15s, transform .15s;
        }

        .admin-auth-btn:hover {
            background: var(--moa-red-dark);
            transform: translateY(-1px);
        }

        .admin-auth-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 24px;
            color: #374151;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
        }

        .admin-auth-back:hover { color: var(--moa-red); }

        .admin-auth-footer {
            margin-top: auto;
            padding-top: 32px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            width: 100%;
            max-width: 420px;
        }

        .admin-auth-alert {
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 13px;
            margin-bottom: 18px;
        }

        .admin-auth-alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }

        .admin-auth-alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #15803d;
        }

        .otp-boxes {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin: 24px 0 28px;
        }

        .otp-box {
            width: 56px;
            height: 56px;
            border: 1px solid var(--moa-border);
            border-radius: 10px;
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            color: var(--moa-text);
        }

        .otp-box:focus {
            outline: none;
            border-color: #fca5a5;
            box-shadow: 0 0 0 3px rgba(237, 28, 36, 0.12);
        }

        .admin-auth-resend {
            text-align: center;
            margin-top: 16px;
        }

        .admin-auth-resend button {
            border: none;
            background: none;
            color: var(--moa-red);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            padding: 0;
        }

        .field-error {
            display: block;
            color: #dc2626;
            font-size: 12px;
            margin-top: 4px;
        }

        @media (max-width: 991.98px) {
            .admin-auth-page { flex-direction: column; }
            .admin-auth-brand {
                flex: none;
                min-height: 280px;
                padding: 36px 24px;
            }
            .admin-auth-blob {
                width: 200px;
                margin-bottom: 18px;
                padding: 24px;
            }
            .admin-auth-brand h1 { font-size: 1.5rem; }
            .admin-auth-panel { padding: 32px 20px 20px; }
        }
    </style>
    @stack('styles')
</head>
<body>
@php
    $logoUrl = !empty($globalStoreSetting) && !empty($globalStoreSetting->logo)
        ? asset('public/uploads/settings/' . $globalStoreSetting->logo)
        : asset('public/uploads/settings/moaahar-logo.png');
    $panelHeading = $panelHeading ?? 'Welcome to Moaahar Admin';
    $panelText = $panelText ?? "Manage users, restaurants, deliveries, and monitor your platform's success from one centralized dashboard.";
@endphp
<div class="admin-auth-page">
    <aside class="admin-auth-brand">
        <div class="admin-auth-brand-inner">
            <div class="admin-auth-blob">
                <img src="{{ $logoUrl }}" alt="Moaahar" class="admin-auth-logo">
            </div>
            <h1>{{ $panelHeading }}</h1>
            <p>{{ $panelText }}</p>
        </div>
    </aside>

    <main class="admin-auth-panel">
        <div class="admin-auth-form-wrap">
            @yield('auth-content')
        </div>
        @if($showCopyright ?? true)
            <div class="admin-auth-footer">&copy; {{ date('Y') }} Moaahar Food Management. All rights reserved.</div>
        @endif
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@include('partials.password-toggle-init')
@stack('scripts')
</body>
</html>
