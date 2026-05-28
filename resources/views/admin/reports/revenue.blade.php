@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        <div class="d-flex align-items-center mb-4">
            <h5 class="mb-0">Reports & Analytics</h5>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-3 col-md-6"><div class="card dashboard-card h-100"><div class="card-body"><small class="text-muted">Total Revenue</small><h3>₹{{ number_format((float) $summary['total_revenue'], 0) }}</h3><small class="text-success">+12.5%</small></div></div></div>
            <div class="col-lg-3 col-md-6"><div class="card dashboard-card h-100"><div class="card-body"><small class="text-muted">Total Orders</small><h3>{{ number_format((int) $summary['total_orders']) }}</h3><small class="text-success">+8.3%</small></div></div></div>
            <div class="col-lg-3 col-md-6"><div class="card dashboard-card h-100"><div class="card-body"><small class="text-muted">Active Vendors</small><h3>{{ number_format((int) $summary['active_vendors']) }}</h3><small class="text-success">+5.2%</small></div></div></div>
            <div class="col-lg-3 col-md-6"><div class="card dashboard-card h-100"><div class="card-body"><small class="text-muted">Total Users</small><h3>{{ number_format((int) $summary['total_users']) }}</h3><small class="text-success">+15.8%</small></div></div></div>
        </div>

        <div class="card dashboard-card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.reports.revenue') }}" class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="fw-semibold">Generate Reports</div>
                    <div class="d-flex gap-2">
                        <input type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm">
                        <input type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm">
                        <button type="submit" class="btn btn-theme btn-sm"><i class="ri-download-2-line me-1"></i>Export Report</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card dashboard-card">
            <div class="card-body">
                <h6 class="mb-3">Sales Overview</h6>
                <div class="sales-bars">
                    @php $maxValue = max($chartData->max() ?: 1, 1); @endphp
                    @forelse($chartData as $idx => $value)
                        <div class="bar-col">
                            <div class="bar-track">
                                <div class="bar-fill" style="height: {{ max(6, (int) (($value / $maxValue) * 100)) }}%;"></div>
                            </div>
                            <small>{{ $chartLabels[$idx] ?? '-' }}</small>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No monthly sales data available.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
.sales-bars { min-height: 260px; border: 1px solid #e5e7eb; border-radius: 8px; padding: 18px; display: flex; align-items: end; gap: 16px; overflow-x: auto; }
.bar-col { min-width: 80px; text-align: center; }
.bar-track { height: 180px; background: #f3f4f6; border-radius: 6px; display: flex; align-items: end; overflow: hidden; }
.bar-fill { width: 100%; background: #22c55e; border-radius: 6px 6px 0 0; }
</style>
@endsection
