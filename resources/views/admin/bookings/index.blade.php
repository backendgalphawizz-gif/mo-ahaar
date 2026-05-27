@extends('layouts.app')

@section('content')
<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <div class="page-body-wrapper">
        <div class="page-body">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card card-table">
                            <div class="card-body">
                                <div class="title-header option-title d-flex align-items-center">
                                    <h5>Booking Management</h5>
                                    <form method="GET" action="{{ route('admin.bookings.index') }}" class="ms-auto d-flex gap-2">
                                        <select name="status" class="form-select form-select-sm" style="min-width:180px;">
                                            <option value="">All Status</option>
                                            @foreach(['pending', 'booked', 'confirmed', 'in_progress', 'completed', 'cancel_requested', 'cancelled', 'rejected'] as $status)
                                                <option value="{{ $status }}" {{ ($selectedStatus ?? '') === $status ? 'selected' : '' }}>
                                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-theme btn-sm">Filter</button>
                                        <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                                    </form>
                                </div>

                                @if(!empty($warning))
                                    <div class="alert alert-warning">{{ $warning }}</div>
                                @endif

                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        {{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        {{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                <div class="row g-3 mb-4">
                                    <div class="col-md-3">
                                        <div class="card border-0 metric-card metric-card-primary h-100">
                                            <div class="card-body">
                                                <small>Total Bookings</small>
                                                <h3>{{ (int) ($summary['total'] ?? 0) }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card border-0 metric-card metric-card-warning h-100">
                                            <div class="card-body">
                                                <small>Pending</small>
                                                <h3>{{ (int) ($summary['pending'] ?? 0) }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card border-0 metric-card metric-card-success h-100">
                                            <div class="card-body">
                                                <small>Confirmed</small>
                                                <h3>{{ (int) ($summary['confirmed'] ?? 0) }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card border-0 metric-card metric-card-danger h-100">
                                            <div class="card-body">
                                                <small>Cancelled</small>
                                                <h3>{{ (int) ($summary['cancelled'] ?? 0) }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive table-product">
                                    <table class="table all-package theme-table" id="table_id">
                                        <thead>
                                            <tr>
                                                <th>S.No.</th>
                                                <th>Booking ID</th>
                                                <th>Venue</th>
                                                <th>Customer Name</th>
                                                <th>Mobile No.</th>
                                                <th>Booking Date</th>
                                                <th>Amount</th>
                                                <th>Track Booking Status</th>
                                                <th>Handle Cancellation</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($bookings as $booking)
                                                @php
                                                    $status = strtolower((string) ($booking->booking_status ?? 'pending'));
                                                    $isCancelRequested = in_array($status, ['cancel_requested', 'cancellation_requested'], true);
                                                @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>#{{ $booking->booking_id }}</td>
                                                    <td>{{ $booking->display_venue_name ?: 'N/A' }}</td>
                                                    <td>{{ $booking->customer_name ?: 'N/A' }}</td>
                                                    <td>{{ $booking->customer_phone ?: 'N/A' }}</td>
                                                    <td>{{ !empty($booking->booking_date) ? \Carbon\Carbon::parse($booking->booking_date)->format('d M Y, h:i A') : 'N/A' }}</td>
                                                    <td>₹{{ number_format((float) ($booking->booking_amount ?? 0), 2) }}</td>

                                                    <td>
                                                        <form method="POST" action="{{ route('admin.bookings.status', $booking->booking_id) }}" class="d-flex gap-2 align-items-center">
                                                            @csrf
                                                            <select name="status" class="form-select form-select-sm" style="min-width: 150px;">
                                                                @foreach(['pending', 'booked', 'confirmed', 'in_progress', 'completed', 'cancel_requested', 'cancelled', 'rejected'] as $statusOption)
                                                                    <option value="{{ $statusOption }}" {{ $status === $statusOption ? 'selected' : '' }}>
                                                                        {{ ucfirst(str_replace('_', ' ', $statusOption)) }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <button type="submit" class="btn btn-sm btn-theme">Update</button>
                                                        </form>
                                                    </td>

                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            @if($isCancelRequested)
                                                                <form method="POST" action="{{ route('admin.bookings.cancellation', $booking->booking_id) }}">
                                                                    @csrf
                                                                    <input type="hidden" name="action" value="approve">
                                                                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                                                </form>
                                                                <form method="POST" action="{{ route('admin.bookings.cancellation', $booking->booking_id) }}">
                                                                    @csrf
                                                                    <input type="hidden" name="action" value="reject">
                                                                    <button type="submit" class="btn btn-outline-danger btn-sm">Reject</button>
                                                                </form>
                                                            @else
                                                                <form method="POST" action="{{ route('admin.bookings.cancellation', $booking->booking_id) }}">
                                                                    @csrf
                                                                    <input type="hidden" name="action" value="force_cancel">
                                                                    <button type="submit" class="btn btn-warning btn-sm">Cancel Booking</button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center text-muted py-4">No bookings found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
.metric-card { border-radius: 12px; color: #fff; }
.metric-card .card-body { padding: 16px 18px; }
.metric-card small { text-transform: uppercase; letter-spacing: .05em; opacity: .9; }
.metric-card h3 { margin: 8px 0 0; font-weight: 700; }
.metric-card-primary { background: linear-gradient(135deg, #0f4c75, #3282b8); }
.metric-card-warning { background: linear-gradient(135deg, #f59f00, #d97706); }
.metric-card-success { background: linear-gradient(135deg, #198754, #146c43); }
.metric-card-danger { background: linear-gradient(135deg, #dc3545, #a71d2a); }
</style>
@endsection
