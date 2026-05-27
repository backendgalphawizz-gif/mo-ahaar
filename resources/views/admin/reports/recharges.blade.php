@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title d-flex align-items-center mb-4">
            <h5><i class="ri-flashlight-line me-2"></i>Recharge Transactions Report</h5>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3"><div class="card border-0 report-metric report-metric-primary h-100"><div class="card-body"><small>Total Transactions</small><h3>{{ $summary['total'] }}</h3></div></div></div>
            <div class="col-md-3"><div class="card border-0 report-metric report-metric-success h-100"><div class="card-body"><small>Mobile Recharge</small><h3>{{ $summary['mobile'] }}</h3></div></div></div>
            <div class="col-md-3"><div class="card border-0 report-metric report-metric-warning h-100"><div class="card-body"><small>FASTag Recharge</small><h3>{{ $summary['fastag'] }}</h3></div></div></div>
            <div class="col-md-3"><div class="card border-0 report-metric report-metric-danger h-100"><div class="card-body"><small>Gas Bookings</small><h3>{{ $summary['gas'] }}</h3></div></div></div>
        </div>

        <div class="card card-table">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.reports.recharges') }}" class="report-filter-form mb-3">
                    <input type="date" name="start_date" value="{{ $startDate }}" class="form-control">
                    <input type="date" name="end_date" value="{{ $endDate }}" class="form-control">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="mobile" {{ $type === 'mobile' ? 'selected' : '' }}>Mobile Recharge</option>
                        <option value="fastag" {{ $type === 'fastag' ? 'selected' : '' }}>FASTag Recharge</option>
                        <option value="gas" {{ $type === 'gas' ? 'selected' : '' }}>Gas Booking</option>
                    </select>
                    <button type="submit" class="btn btn-theme btn-sm">Generate</button>
                    <a href="{{ route('admin.reports.recharges') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </form>

                <div class="table-responsive">
                    <table class="table all-package theme-table table-product align-middle text-start">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Type</th>
                                <th>Reference</th>
                                <th>Customer</th>
                                <th>Service</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $record)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $record->report_type }}</td>
                                    <td>{{ $record->reference ?: '-' }}</td>
                                    <td>{{ $record->customer ?: '-' }}</td>
                                    <td>{{ $record->service ?: '-' }}</td>
                                    <td>{{ $record->amount !== null ? '₹' . number_format((float) $record->amount, 2) : 'N/A' }}</td>
                                    <td>{{ ucfirst((string) $record->status) }}</td>
                                    <td>{{ !empty($record->transaction_date) ? \Carbon\Carbon::parse($record->transaction_date)->format('d M Y, h:i A') : '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-4">No recharge transaction records found.</td></tr>
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
.report-filter-form { display:grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap:12px; align-items:center; }
@media (max-width: 991px) { .report-filter-form { grid-template-columns: 1fr; } }
</style>
@endsection
