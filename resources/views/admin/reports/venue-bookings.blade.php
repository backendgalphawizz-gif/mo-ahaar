@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title d-flex align-items-center mb-4">
            <h5><i class="ri-store-2-line me-2"></i>Venue Bookings Report</h5>
        </div>

        @if(!empty($warning))
            <div class="alert alert-warning">{{ $warning }}</div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-md-3"><div class="card border-0 report-metric report-metric-primary h-100"><div class="card-body"><small>Total Bookings</small><h3>{{ $summary['total'] }}</h3></div></div></div>
            <div class="col-md-3"><div class="card border-0 report-metric report-metric-success h-100"><div class="card-body"><small>Confirmed</small><h3>{{ $summary['confirmed'] }}</h3></div></div></div>
            <div class="col-md-3"><div class="card border-0 report-metric report-metric-danger h-100"><div class="card-body"><small>Cancelled</small><h3>{{ $summary['cancelled'] }}</h3></div></div></div>
            <div class="col-md-3"><div class="card border-0 report-metric report-metric-warning h-100"><div class="card-body"><small>Revenue</small><h3>₹{{ number_format((float) $summary['revenue'], 2) }}</h3></div></div></div>
        </div>

        <div class="card card-table">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.reports.venue-bookings') }}" class="report-filter-form mb-3">
                    <input type="date" name="start_date" value="{{ $startDate }}" class="form-control">
                    <input type="date" name="end_date" value="{{ $endDate }}" class="form-control">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        @foreach(['pending','booked','confirmed','completed','cancel_requested','cancelled','rejected'] as $statusOption)
                            <option value="{{ $statusOption }}" {{ $status === $statusOption ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $statusOption)) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-theme btn-sm">Generate</button>
                    <a href="{{ route('admin.reports.venue-bookings') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </form>

                <div class="table-responsive">
                    <table class="table all-package theme-table table-product align-middle text-start">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Booking ID</th>
                                <th>Venue</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Booking Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $record)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>#{{ $record->booking_id }}</td>
                                    <td>{{ $record->display_venue_name ?: '-' }}</td>
                                    <td>{{ $record->customer_name ?: '-' }}</td>
                                    <td>₹{{ number_format((float) ($record->booking_amount ?? 0), 2) }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', (string) ($record->booking_status ?? 'pending'))) }}</td>
                                    <td>{{ !empty($record->booking_date) ? \Carbon\Carbon::parse($record->booking_date)->format('d M Y, h:i A') : '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">No venue booking records found.</td></tr>
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
