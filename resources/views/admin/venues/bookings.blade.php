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
                                    <h5>Venue / Banquet Booking Management</h5>
                                    <a href="{{ route('admin.venues.listings') }}" class="btn btn-outline-secondary ms-auto">Manage Listings</a>
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

                                <div class="table-responsive table-product">
                                    <table class="table all-package theme-table" id="table_id">
                                        <thead>
                                            <tr>
                                                <th>S. No.</th>
                                                <th>Booking ID</th>
                                                <th>Venue</th>
                                                <th>Customer Name</th>
                                                <th>Mobile No.</th>
                                                <th>Booking Date</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Update Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($bookings as $booking)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>#{{ $booking->booking_id }}</td>
                                                    <td>{{ $booking->display_venue_name ?: 'N/A' }}</td>
                                                    <td>{{ $booking->customer_name ?: 'N/A' }}</td>
                                                    <td>{{ $booking->customer_phone ?: 'N/A' }}</td>
                                                    <td>{{ !empty($booking->booking_date) ? \Carbon\Carbon::parse($booking->booking_date)->format('d M Y, h:i A') : 'N/A' }}</td>
                                                    <td>₹{{ number_format((float) ($booking->booking_amount ?? 0), 2) }}</td>
                                                    <td>
                                                        @php
                                                            $status = strtolower((string) ($booking->booking_status ?? 'pending'));
                                                        @endphp
                                                        <span class="badge booking-status badge-{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                                                    </td>
                                                    <td>
                                                        <form method="POST" action="{{ route('admin.venues.bookings.status', $booking->booking_id) }}" class="d-flex gap-2 align-items-center">
                                                            @csrf
                                                            <select name="status" class="form-select form-select-sm" style="min-width: 150px;">
                                                                @foreach(['pending', 'booked', 'confirmed', 'in_progress', 'completed', 'cancelled', 'rejected'] as $statusOption)
                                                                    <option value="{{ $statusOption }}" {{ $status === $statusOption ? 'selected' : '' }}>
                                                                        {{ ucfirst(str_replace('_', ' ', $statusOption)) }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <button type="submit" class="btn btn-sm btn-theme">Save</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center text-muted py-4">No venue bookings found.</td>
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
.booking-status {
    padding: 7px 10px;
    font-size: 11px;
    border-radius: 14px;
    text-transform: capitalize;
}
.badge-pending, .badge-booked { background: #fff3cd; color: #664d03; }
.badge-confirmed, .badge-completed { background: #d1e7dd; color: #0f5132; }
.badge-in_progress { background: #cfe2ff; color: #084298; }
.badge-cancelled, .badge-rejected { background: #f8d7da; color: #842029; }
</style>
@endsection
