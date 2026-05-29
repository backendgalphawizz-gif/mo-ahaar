{{-- Admin layout + Figma UI (loads on every admin page via layouts.app) --}}
<style>
.admin-panel,
.vendor-panel {
    --moa-sidebar-w: 240px;
    --moa-header-h: 60px;
    --moa-red: #ed1c24;
    --moa-red-soft: #fef2f2;
    --moa-page-bg: #f4f5f7;
}

/* ── Fix compact-wrapper layout (theme default = 280px) ── */
.admin-panel .page-wrapper.compact-wrapper .page-header,
.vendor-panel .page-wrapper.compact-wrapper .page-header {
    margin-left: var(--moa-sidebar-w) !important;
    width: calc(100% - var(--moa-sidebar-w)) !important;
    position: fixed !important;
    top: 0 !important;
    left: auto !important;
    right: 0 !important;
    height: var(--moa-header-h) !important;
    z-index: 9 !important;
    background: #fff !important;
    border-bottom: 1px solid #eceef2;
    box-shadow: none !important;
}

.admin-panel .page-wrapper.compact-wrapper .page-body-wrapper,
.vendor-panel .page-wrapper.compact-wrapper .page-body-wrapper {
    margin-left: 0 !important;
    padding-top: 0 !important;
}

.admin-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper,
.vendor-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper {
    width: var(--moa-sidebar-w) !important;
    top: 0 !important;
    left: 0 !important;
    height: 100vh !important;
    min-height: 100vh !important;
    background: #fff !important;
    background-image: none !important;
    animation: none !important;
    -webkit-animation: none !important;
    box-shadow: none !important;
    border-right: 1px solid #eceef2;
    z-index: 10 !important;
    line-height: inherit;
}

.admin-panel .page-wrapper.compact-wrapper .page-body-wrapper .page-body,
.vendor-panel .page-wrapper.compact-wrapper .page-body-wrapper .page-body {
    margin-left: var(--moa-sidebar-w) !important;
    margin-top: var(--moa-header-h) !important;
    padding: 18px 16px 24px !important;
    min-height: calc(100vh - var(--moa-header-h)) !important;
    background: var(--moa-page-bg) !important;
}

.admin-panel .page-wrapper.compact-wrapper .page-header .header-wrapper,
.vendor-panel .page-wrapper.compact-wrapper .page-header .header-wrapper {
    min-height: var(--moa-header-h) !important;
    padding: 0 16px !important;
}

.admin-panel .page-wrapper.compact-wrapper .page-header .header-logo-wrapper,
.vendor-panel .page-wrapper.compact-wrapper .page-header .header-logo-wrapper {
    display: none !important;
}

/* ── MoA sidebar: active, hover (override Fastkart gold / dark theme) ── */
.admin-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar .sidebar-main .sidebar-links,
.vendor-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar .sidebar-main .sidebar-links {
    height: calc(100vh - 64px) !important;
}

.admin-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar .sidebar-link::before,
.admin-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar .sidebar-link.active::before,
.admin-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar .sidebar-link.sidebar-title.active::before,
.vendor-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar .sidebar-link::before,
.vendor-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar .sidebar-link.active::before {
    display: none !important;
    opacity: 0 !important;
    background: none !important;
    content: none !important;
}

.admin-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-link,
.vendor-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-link {
    color: #374151 !important;
    background: transparent !important;
    background-image: none !important;
    border-radius: 8px;
}

.admin-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-link > i:first-of-type,
.vendor-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-link > i:first-of-type {
    color: #4b5563 !important;
}

/* Hover — main menu */
.admin-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-link.link-nav:hover,
.admin-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-link.has-submenu:hover,
.admin-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar .sidebar-links .sidebar-link:hover,
.vendor-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-link.link-nav:hover,
.vendor-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-link.has-submenu:hover {
    background: #f3f4f6 !important;
    background-image: none !important;
    color: #111827 !important;
}

.admin-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-link.link-nav:hover > i:first-of-type,
.admin-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-link.has-submenu:hover > i:first-of-type,
.vendor-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-link.link-nav:hover > i:first-of-type {
    color: #374151 !important;
}

/* Active — top-level single links (Dashboard, Orders, etc.) */
.admin-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-link.link-nav.active,
.admin-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar .sidebar-links .sidebar-list .sidebar-link.sidebar-title.link-nav.active,
.vendor-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-link.link-nav.active {
    background: #ed1c24 !important;
    background-image: none !important;
    color: #fff !important;
    font-weight: 600;
}

.admin-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-link.link-nav.active > i:first-of-type,
.admin-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-link.link-nav.active .submenu-arrow,
.vendor-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-link.link-nav.active > i:first-of-type {
    color: #fff !important;
}

.admin-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-link.link-nav.active:hover,
.vendor-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-link.link-nav.active:hover {
    background: #d9161d !important;
    color: #fff !important;
}

/* Active — open parent with submenu */
.admin-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-list.submenu-open > .sidebar-link.has-submenu.open,
.vendor-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-list.submenu-open > .sidebar-link.has-submenu.open {
    background: #fef2f2 !important;
    background-image: none !important;
    color: #ed1c24 !important;
    font-weight: 600;
}

.admin-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-list.submenu-open > .sidebar-link.has-submenu.open > i:first-of-type,
.vendor-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-list.submenu-open > .sidebar-link.has-submenu.open > i:first-of-type {
    color: #ed1c24 !important;
}

/* Submenu items */
.admin-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-submenu li a,
.vendor-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-submenu li a {
    color: #6b7280 !important;
    background: transparent !important;
}

.admin-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-submenu li a:hover,
.vendor-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-submenu li a:hover {
    background: #f9fafb !important;
    color: #111827 !important;
}

.admin-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-submenu li a.active,
.vendor-panel .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper.moa-sidebar #sidebar-menu .sidebar-submenu li a.active {
    color: #ed1c24 !important;
    background: #fef2f2 !important;
    font-weight: 600;
    border-left: 3px solid #ed1c24;
    padding-left: 9px;
}

/* Kill theme gold overrides at end of style.css */
.admin-panel .sidebar-links .sidebar-list .sidebar-link.sidebar-title.active,
.admin-panel #sidebar-menu .sidebar-link.sidebar-title.active,
.admin-panel #sidebar-menu .sidebar-link.link-nav.active,
.vendor-panel .sidebar-links .sidebar-list .sidebar-link.sidebar-title.active,
.vendor-panel #sidebar-menu .sidebar-link.link-nav.active {
    background-image: none !important;
}

/* Header */
.admin-panel .search-full,
.vendor-panel .search-full {
    max-width: 520px;
    margin: 0 auto;
    width: 100%;
}

.admin-panel .search-full .form-control-plaintext,
.admin-panel .search-full input,
.vendor-panel .search-full input {
    background: #f3f4f6 !important;
    border-radius: 8px !important;
    min-height: 38px;
    font-size: 13px;
    padding-left: 36px !important;
    border: 1px solid #e5e7eb !important;
}

.admin-panel .page-header .search-full .form-group:before {
    display: none !important;
    content: none !important;
}

.admin-panel .search-full .u-posRelative > .ri-search-line {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    z-index: 2;
}

.admin-panel .nav-menus .notification-box { position: relative; list-style: none; }
.admin-panel .nav-menus .notification-box a { color: #374151; font-size: 20px; text-decoration: none; }
.admin-panel .nav-menus .notification-box .dot {
    position: absolute; top: 2px; right: 2px; width: 8px; height: 8px;
    background: var(--moa-red); border-radius: 50%; border: 2px solid #fff;
}

.admin-panel .profile-media .user-profile,
.vendor-panel .profile-media .user-profile {
    width: 36px; height: 36px; object-fit: cover;
}

.admin-panel .profile-media .user-name-hide,
.vendor-panel .profile-media .user-name-hide {
    display: block !important;
}

.admin-panel .profile-media .user-name-hide span {
    font-size: 13px; font-weight: 600; color: #111827; display: block; line-height: 1.2;
}

.admin-panel .profile-media .user-name-hide p {
    font-size: 11px; color: #6b7280; margin: 0;
}

.admin-panel .container-fluid,
.vendor-panel .container-fluid {
    max-width: 100%;
    padding: 0;
}

/* Page chrome */
.admin-panel .figma-page-title { font-size: 24px; font-weight: 700; color: #111827; }
.admin-panel .figma-page-subtitle { font-size: 13px; color: #6b7280; margin-top: 2px; }
.admin-panel .figma-page-header { margin-bottom: 16px; }
.admin-panel .btn-figma-primary {
    background: var(--moa-red); border-color: var(--moa-red); color: #fff;
    font-weight: 600; border-radius: 8px; padding: 8px 16px; font-size: 13px;
}
.admin-panel .btn-figma-primary:hover { background: #d9161d; border-color: #d9161d; color: #fff; }

.admin-panel .dashboard-card {
    border: 1px solid #e8eaee; border-radius: 12px; background: #fff;
    box-shadow: 0 1px 2px rgba(15,23,42,.04);
}
.admin-panel .dashboard-card .card-body { padding: 16px 18px; }

.admin-panel .figma-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-bottom: 14px; }
.admin-panel .figma-toolbar .form-control,
.admin-panel .figma-toolbar .form-select {
    border-radius: 8px; min-height: 36px; font-size: 12px; border-color: #e5e7eb; background: #fff;
}
.admin-panel .figma-toolbar .toolbar-search { flex: 1 1 220px; max-width: 320px; position: relative; }
.admin-panel .figma-toolbar .toolbar-search i {
    position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af;
}
.admin-panel .figma-toolbar .toolbar-search input { padding-left: 34px; width: 100%; }

.admin-panel .figma-line-tabs {
    display: flex; flex-wrap: wrap; gap: 4px; border-bottom: 1px solid #eceef2; margin-bottom: 14px;
}
.admin-panel .figma-line-tabs .tab-link {
    padding: 10px 14px; font-size: 13px; font-weight: 500; color: #6b7280;
    text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -1px;
}
.admin-panel .figma-line-tabs .tab-link.active {
    color: var(--moa-red); border-bottom-color: var(--moa-red); font-weight: 600;
}

.admin-panel .table-modern thead th {
    font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase;
    letter-spacing: .02em; padding: 10px 12px; border-bottom: 1px solid #eceef2; background: #fafbfc;
}
.admin-panel .table-modern tbody td {
    font-size: 12px; padding: 12px; vertical-align: middle;
    border-bottom: 1px solid #f1f3f5; color: #1f2937;
}
.admin-panel .table-modern tbody tr:hover { background: #fafafa; }

.admin-panel .user-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; flex-shrink: 0;
}
.admin-panel .user-avatar.avatar-blue { background: #dbeafe; color: #1d4ed8; }
.admin-panel .user-avatar.avatar-green { background: #dcfce7; color: #15803d; }
.admin-panel .user-avatar.avatar-orange { background: #ffedd5; color: #c2410c; }

.admin-panel .figma-switch { position: relative; display: inline-block; width: 40px; height: 22px; }
.admin-panel .figma-switch input { opacity: 0; width: 0; height: 0; }
.admin-panel .figma-switch .slider {
    position: absolute; cursor: pointer; inset: 0; background: #d1d5db; border-radius: 999px; transition: .2s;
}
.admin-panel .figma-switch .slider:before {
    position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px;
    background: #fff; border-radius: 50%; transition: .2s;
}
.admin-panel .figma-switch input:checked + .slider { background: var(--moa-red); }
.admin-panel .figma-switch input:checked + .slider:before { transform: translateX(18px); }
.admin-panel .status-label { font-size: 10px; font-weight: 700; letter-spacing: .04em; margin-top: 4px; display: block; }
.admin-panel .status-label.on { color: #16a34a; }
.admin-panel .status-label.off { color: #9ca3af; }

.admin-panel .figma-pagination {
    display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
    gap: 12px; margin-top: 16px; padding-top: 12px; border-top: 1px solid #eceef2;
    font-size: 12px; color: #6b7280;
}
.admin-panel .figma-pagination .page-link { border-radius: 6px; font-size: 12px; min-width: 32px; text-align: center; }
.admin-panel .figma-pagination .page-item.active .page-link { background: var(--moa-red); border-color: var(--moa-red); }

.admin-panel .figma-stepper {
    display: flex; align-items: flex-start; justify-content: space-between;
    margin-bottom: 24px; position: relative;
}
.admin-panel .figma-stepper::before {
    content: ""; position: absolute; top: 16px; left: 8%; right: 8%;
    height: 2px; background: #e5e7eb; z-index: 0;
}
.admin-panel .figma-step { flex: 1; text-align: center; position: relative; z-index: 1; color: inherit; }
.admin-panel a.figma-step:hover .step-label { color: #ed1c24; }
.admin-panel .figma-step .step-circle {
    width: 32px; height: 32px; border-radius: 50%; background: #fff; border: 2px solid #d1d5db;
    color: #6b7280; display: inline-flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; margin-bottom: 6px;
}
.admin-panel .figma-step.active .step-circle { border-color: var(--moa-red); background: var(--moa-red); color: #fff; }
.admin-panel .figma-step.done .step-circle { border-color: var(--moa-red); color: var(--moa-red); background: #fff; }
.admin-panel .figma-step .step-label { font-size: 11px; color: #6b7280; font-weight: 500; display: block; }
.admin-panel .figma-step.active .step-label { color: var(--moa-red); font-weight: 600; }

.admin-panel .form-section-figma { background: #fff; border: 1px solid #eceef2; border-radius: 12px; padding: 20px; }
.admin-panel .form-section-figma h6 { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
.admin-panel .form-control, .admin-panel .form-select {
    border-radius: 8px; min-height: 40px; font-size: 13px; border-color: #e5e7eb;
}
.admin-panel .invalid-feedback { font-size: 11px; margin-top: 4px; color: #dc3545; }
.admin-panel .invalid-feedback.d-block { display: block !important; }
.admin-panel .form-control.is-invalid,
.admin-panel .form-select.is-invalid { border-color: #dc3545; }

.admin-panel .kpi-card {
    border: 1px solid #e8eaee; border-radius: 12px; background: #fff;
    padding: 14px 48px 12px 14px; position: relative; min-height: 88px;
}
.admin-panel .kpi-card p { font-size: 11px; color: #6b7280; margin: 0 0 6px; }
.admin-panel .kpi-card h3 { font-size: 26px; font-weight: 700; margin: 0; color: #111827; }
.admin-panel .kpi-icon {
    position: absolute; right: 12px; top: 12px; width: 32px; height: 32px;
    border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 15px;
}

/* User add/edit modal (Figma) */
.admin-panel .moa-user-modal .modal-content {
    border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 20px 40px rgba(15, 23, 42, 0.12);
}
.admin-panel .moa-user-modal .modal-title { font-size: 18px; color: #111827; }
.admin-panel .moa-user-modal .form-label { font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px; }
.admin-panel .moa-user-modal .form-control {
    border-radius: 8px; border-color: #e5e7eb; font-size: 13px; min-height: 40px;
}
.admin-panel .moa-user-modal .form-control:focus {
    border-color: #d1d5db; box-shadow: 0 0 0 0.15rem rgba(237, 28, 36, 0.08);
}
.admin-panel .moa-user-modal textarea.form-control { min-height: 88px; resize: vertical; }
.admin-panel .moa-user-modal .modal-footer .btn-light {
    background: #f3f4f6; border-color: #e5e7eb; color: #374151; font-weight: 500; border-radius: 8px; padding: 8px 16px;
}
.admin-panel .moa-user-modal .modal-footer .btn-figma-primary { min-width: 100px; }

/* Restaurant / vendor (Figma) */
.admin-panel .figma-btn-filter {
    border: 1px solid #e5e7eb; background: #fff; color: #374151;
    border-radius: 8px; font-size: 12px; font-weight: 500; padding: 7px 12px;
    display: inline-flex; align-items: center; gap: 6px; min-height: 36px;
}
.admin-panel .figma-btn-filter:hover { background: #f9fafb; color: #111827; }
.admin-panel .figma-btn-export {
    border: 1px solid #e5e7eb; background: #fff; color: #374151;
    border-radius: 8px; font-size: 12px; font-weight: 500; padding: 7px 14px;
    display: inline-flex; align-items: center; gap: 6px; min-height: 36px; text-decoration: none;
}
.admin-panel .figma-btn-export:hover { background: #f9fafb; color: #111827; }

.admin-panel .status-label.pending { color: #ea580c !important; }
.admin-panel .status-label.suspended { color: #6b7280 !important; }
.admin-panel .status-label.rejected { color: #dc2626 !important; }

.admin-panel .figma-icon-actions { display: flex; align-items: center; gap: 8px; }
.admin-panel .figma-icon-btn {
    width: 32px; height: 32px; border-radius: 50%; display: inline-flex;
    align-items: center; justify-content: center; font-size: 15px;
    text-decoration: none; border: none; padding: 0; cursor: pointer;
}
.admin-panel .figma-icon-btn.view { background: #dbeafe; color: #2563eb; }
.admin-panel .figma-icon-btn.edit { background: #ffedd5; color: #ea580c; }
.admin-panel .figma-icon-btn.delete { background: #fee2e2; color: #dc2626; }
.admin-panel .figma-icon-btn:hover { filter: brightness(0.95); }

.admin-panel .vendor-wizard-head { margin-bottom: 20px; }
.admin-panel .vendor-wizard-head .btn-back-figma {
    width: 36px; height: 36px; border: 1px solid #e5e7eb; border-radius: 8px;
    display: inline-flex; align-items: center; justify-content: center;
    color: #374151; background: #fff; text-decoration: none; flex-shrink: 0;
}
.admin-panel .vendor-wizard-head .btn-back-figma:hover { background: #f9fafb; }
.admin-panel .vendor-wizard-head .vendor-code-accent { color: var(--moa-red); font-weight: 700; }

.admin-panel .figma-form-block { margin-bottom: 0; }
.admin-panel .figma-form-block h6 {
    font-size: 15px; font-weight: 700; color: #111827; margin-bottom: 10px;
}
.admin-panel .figma-section-rule {
    border: 0; border-top: 1px solid #eceef2; margin: 0 0 20px; opacity: 1;
}
.admin-panel .figma-form-block .form-label {
    font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;
}
.admin-panel .figma-form-block .form-control,
.admin-panel .figma-form-block .form-select {
    min-height: 42px; font-size: 13px; border-color: #e5e7eb; border-radius: 8px;
}
.admin-panel .figma-form-block .form-control::placeholder { color: #9ca3af; }
.admin-panel .figma-form-extra { margin-top: 8px; padding-top: 20px; border-top: 1px dashed #e5e7eb; }

.admin-panel .vendor-detail-kpi .card-body { padding: 16px 18px; }
.admin-panel .vendor-detail-kpi small { font-size: 12px; color: #6b7280; display: block; margin-bottom: 6px; }
.admin-panel .vendor-detail-kpi h4 { font-size: 22px; font-weight: 700; color: #111827; margin: 0; }
.admin-panel .vendor-detail-tabs {
    border-bottom: 1px solid #eceef2; gap: 0; margin-bottom: 20px;
}
.admin-panel .vendor-detail-tabs .nav-link {
    border: none; border-bottom: 2px solid transparent; color: #6b7280;
    font-size: 13px; font-weight: 500; padding: 10px 16px; margin-bottom: -1px;
}
.admin-panel .vendor-detail-tabs .nav-link.active {
    color: var(--moa-red); border-bottom-color: var(--moa-red); font-weight: 600; background: transparent;
}
.admin-panel .vendor-detail-section h6 {
    font-size: 14px; font-weight: 700; color: #111827; margin-bottom: 12px;
    padding-bottom: 8px; border-bottom: 1px solid #f1f3f5;
}
.admin-panel .vendor-detail-section p { font-size: 13px; margin-bottom: 8px; color: #374151; }
.admin-panel .doc-box-figma {
    border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; text-align: center;
    min-height: 100px; background: #fafbfc; font-size: 12px;
}

@media (max-width: 991px) {
    .admin-panel .page-wrapper.compact-wrapper .page-header,
    .vendor-panel .page-wrapper.compact-wrapper .page-header {
        margin-left: 0 !important;
        width: 100% !important;
    }
    .admin-panel .page-wrapper.compact-wrapper .page-body-wrapper .page-body,
    .vendor-panel .page-wrapper.compact-wrapper .page-body-wrapper .page-body {
        margin-left: 0 !important;
    }
}
</style>
