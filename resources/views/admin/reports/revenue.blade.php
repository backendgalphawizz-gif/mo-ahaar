@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title d-flex align-items-center mb-4">
            <h5><i class="ri-line-chart-line me-2"></i>Revenue Reports</h5>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3"><div class="card border-0 report-metric report-metric-primary h-100"><div class="card-body"><small>Total Revenue</small><h3>₹{{ number_format((float) $summary['total_revenue'], 2) }}</h3></div></div></div>
            <div class="col-md-3"><div class="card border-0 report-metric report-metric-success h-100"><div class="card-body"><small>Orders Revenue</small><h3>₹{{ number_format((float) $summary['order_revenue'], 2) }}</h3></div></div></div>
            <div class="col-md-3"><div class="card border-0 report-metric report-metric-warning h-100"><div class="card-body"><small>Recharge Revenue</small><h3>₹{{ number_format((float) $summary['recharge_revenue'], 2) }}</h3></div></div></div>
            <div class="col-md-3"><div class="card border-0 report-metric report-metric-danger h-100"><div class="card-body"><small>Venue Revenue</small><h3>₹{{ number_format((float) $summary['venue_revenue'], 2) }}</h3></div></div></div>
        </div>

        <div class="card card-table">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.reports.revenue') }}" class="report-filter-form mb-3 revenue-filter-form">
                    <input type="date" name="start_date" value="{{ $startDate }}" class="form-control">
                    <input type="date" name="end_date" value="{{ $endDate }}" class="form-control">
                    <button type="submit" class="btn btn-theme btn-sm">Generate</button>
                    <a href="{{ route('admin.reports.revenue') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </form>

                <div class="table-responsive">
                    <table class="table all-package theme-table table-product align-middle text-start">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Revenue Source</th>
                                <th>Transaction Count</th>
                                <th>Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sources as $source)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $source['label'] }}</td>
                                    <td>{{ $source['count'] }}</td>
                                    <td>₹{{ number_format((float) $source['amount'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No revenue data found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
.report-metric { border-radius: 12px; color: #fff; }
.report-metric .card-body { padding: 16px 18px; }
.report-metric small { text-transform: uppercase; letter-spacing: .05em; opacity: .9; }
.report-metric h3 { margin: 8px 0 0; font-weight: 700; }
.report-metric-primary { background: linear-gradient(135deg, #0f4c75, #3282b8); }
.report-metric-success { background: linear-gradient(135deg, #198754, #146c43); }
.report-metric-warning { background: linear-gradient(135deg, #fd7e14, #d0620a); }
.report-metric-danger { background: linear-gradient(135deg, #dc3545, #a71d2a); }
.report-filter-form { display:grid; gap:12px; align-items:center; }
.revenue-filter-form { grid-template-columns: repeat(4, minmax(0, auto)); justify-content:start; }
@media (max-width: 991px) { .revenue-filter-form { grid-template-columns: 1fr; } }
</style>
@endsection
