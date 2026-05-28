<style>
.page-body { background: #f3f4f6; min-height: calc(100vh - 64px); padding: 14px 10px 18px; }
.container-fluid { max-width: 1240px; }
.dashboard-card { border: 1px solid #e7e9ee; border-radius: 9px; box-shadow: none; background: #fff; }
.dashboard-card .card-body { padding: 14px; }
.table-modern thead th { font-size: 10px; text-transform: none; letter-spacing: 0; color: #5f6470; border-bottom: 1px solid #eceef2; font-weight: 600; padding: 8px 10px; }
.table-modern tbody td { vertical-align: middle; font-size: 11px; color: #1f2937; border-bottom-color: #f1f3f5; padding: 8px 10px; }
.form-control, .form-select { border-color: #e5e7eb; font-size: 11px; min-height: 32px; border-radius: 6px; }
.form-control:focus, .form-select:focus { border-color: #d2d6dc; box-shadow: 0 0 0 0.12rem rgba(17, 24, 39, 0.06); }
.btn-theme { background: #f5dc00; border-color: #f5dc00; color: #111827; font-weight: 600; border-radius: 6px; font-size: 11px; min-height: 30px; padding: 6px 12px; }
.btn-theme:hover { background: #e7ce00; border-color: #e7ce00; color: #111827; }
.btn-danger { background: #ed1c24; border-color: #ed1c24; border-radius: 6px; font-size: 11px; min-height: 30px; padding: 6px 12px; }
.btn-danger:hover { background: #d9161d; border-color: #d9161d; }
.badge-soft-success { background: #dcfce7; color: #166534; }
.badge-soft-warning { background: #fef3c7; color: #92400e; }
.badge-soft-danger { background: #fee2e2; color: #991b1b; }
.badge-soft-info { background: #dbeafe; color: #1e40af; }
.badge-soft-secondary { background: #f1f5f9; color: #475569; }
.order-kpi-card { border: 0; border-radius: 10px; padding: 12px; background: #fff; box-shadow: 0 1px 3px rgba(15,23,42,.08); cursor: pointer; transition: transform .15s, box-shadow .15s; text-decoration: none; color: inherit; display: block; }
.order-kpi-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(15,23,42,.12); color: inherit; }
.order-kpi-card.active { outline: 2px solid #f7bf57; }
.order-kpi-card .kpi-icon { width: 34px; height: 34px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 16px; }
.order-kpi-card .kpi-count { font-size: 18px; font-weight: 700; margin: 6px 0 0; }
.order-kpi-card .kpi-label { font-size: 11px; color: #64748b; margin: 0; }
.kpi-new .kpi-icon { background: #dbeafe; color: #2563eb; }
.kpi-accepted .kpi-icon { background: #dcfce7; color: #16a34a; }
.kpi-rejected .kpi-icon { background: #fee2e2; color: #dc2626; }
.kpi-picked .kpi-icon { background: #ffedd5; color: #ea580c; }
.kpi-delivery .kpi-icon { background: #ede9fe; color: #7c3aed; }
.kpi-delivered .kpi-icon { background: #ccfbf1; color: #0d9488; }
.kpi-cancelled .kpi-icon { background: #f1f5f9; color: #64748b; }
.promo-code-badge { background: #fef3c7; color: #92400e; border: 1px dashed #f59e0b; padding: 4px 12px; border-radius: 6px; font-weight: 700; font-family: monospace; }
.admin-profile-tabs .nav-link { color: #64748b; border: none; border-bottom: 2px solid transparent; }
.admin-profile-tabs .nav-link.active { color: #2563eb; border-bottom-color: #2563eb; font-weight: 600; }
.form-section h6 { font-weight: 600; margin-bottom: 4px; }
.form-section p.text-muted { font-size: 13px; margin-bottom: 12px; }

/* Vendor panel pixel-tuning */
.vendor-panel .page-body { background: #ffffff; padding: 12px 8px 16px; }
.vendor-panel .container-fluid { max-width: 1220px; }
.vendor-panel .dashboard-card { border: 1px solid #eceff3; border-radius: 10px; box-shadow: none; }
.vendor-panel .dashboard-card .card-body { padding: 14px; }
.vendor-panel h5 { font-size: 22px; font-weight: 700; color: #0f172a; }
.vendor-panel h6 { font-size: 15px; font-weight: 700; color: #0f172a; }
.vendor-panel .table-modern thead th { font-size: 10px; padding: 9px 10px; color: #6b7280; }
.vendor-panel .table-modern tbody td { font-size: 12px; padding: 9px 10px; color: #111827; }
.vendor-panel .form-control, .vendor-panel .form-select { min-height: 38px; font-size: 12px; border-radius: 8px; }
.vendor-panel .btn { border-radius: 8px !important; min-height: 34px; font-size: 12px; font-weight: 600; padding: 6px 12px; }
.vendor-panel .btn-danger { background: #ed1c24; border-color: #ed1c24; }
.vendor-panel .btn-danger:hover { background: #d9161d; border-color: #d9161d; }
.vendor-panel .btn-brown { background: #8a3f00; border-color: #8a3f00; color: #fff; }
.vendor-panel .btn-brown:hover { background: #733400; border-color: #733400; color: #fff; }
.vendor-panel .btn-outline-secondary { border-color: #d1d5db; color: #475569; }
.vendor-panel .nav-tabs .nav-link { border: 0; color: #64748b; font-size: 12px; padding: 8px 10px; }
.vendor-panel .nav-tabs .nav-link.active { border: 0; border-bottom: 2px solid #ef4444; color: #111827; font-weight: 600; }
.vendor-panel .badge { border-radius: 999px; padding: 4px 8px; font-size: 10px; font-weight: 600; }
</style>
