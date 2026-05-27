@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title d-flex align-items-center mb-4">
            <h5><i class="ri-fire-line me-2"></i>{{ $title }}</h5>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-header card-header-2"><h5>Book Gas Cylinder</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.gas-booking.store') }}" class="row g-3">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label-title">Customer Name</label>
                        <input type="text" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name') }}" data-alpha-name>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-title">Mobile Number</label>
                        <input type="text" name="mobile_number" class="form-control @error('mobile_number') is-invalid @enderror" maxlength="10" inputmode="numeric" pattern="[0-9]{10}" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)" value="{{ old('mobile_number') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label-title">LPG Provider</label>
                        <select name="provider" class="form-select @error('provider') is-invalid @enderror">
                            <option value="">Provider</option>
                            @foreach($providers as $provider)
                                <option value="{{ $provider }}">{{ $provider }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label-title">Consumer Number</label>
                        <input type="text" name="consumer_number" class="form-control @error('consumer_number') is-invalid @enderror" value="{{ old('consumer_number') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label-title">Delivery ETA</label>
                        <input type="date" name="delivery_eta" class="form-control @error('delivery_eta') is-invalid @enderror" value="{{ old('delivery_eta') }}">
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-theme">Book Cylinder</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header card-header-2"><h5>Booking Status Tracker</h5></div>
            <div class="card-body table-responsive">
                <table class="table all-package theme-table" id="table_id">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Booking Ref</th>
                            <th>Customer</th>
                            <th>Mobile</th>
                            <th>Provider</th>
                            <th>Consumer No.</th>
                            <th>Booked At</th>
                            <th>ETA</th>
                            <th>Status</th>
                            <th>Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $booking->booking_ref }}</td>
                                <td>{{ $booking->customer_name }}</td>
                                <td>{{ $booking->mobile_number }}</td>
                                <td>{{ $booking->provider }}</td>
                                <td>{{ $booking->consumer_number }}</td>
                                <td>{{ optional($booking->booked_at)->format('d-m-Y H:i') }}</td>
                                <td>{{ optional($booking->delivery_eta)->format('d-m-Y') ?: '-' }}</td>
                                <td><span class="badge badge-light-info text-capitalize">{{ str_replace('_', ' ', $booking->status) }}</span></td>
                                <td>
                                    <form method="POST" action="{{ route('admin.gas-booking.status', $booking->gas_booking_id) }}" class="d-flex gap-2">
                                        @csrf
                                        <select name="status" class="form-select form-select-sm" style="min-width:140px;">
                                            @foreach(['booked', 'confirmed', 'in_transit', 'delivered', 'cancelled'] as $status)
                                                <option value="{{ $status }}" {{ $booking->status === $status ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-theme">Save</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center text-muted">No gas bookings yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
