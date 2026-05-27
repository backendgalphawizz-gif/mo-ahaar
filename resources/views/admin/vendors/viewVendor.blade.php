@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <div class="title-header option-title d-flex align-items-center mb-4">
                    <h5><i class="ri-store-2-line me-2"></i>Vendor Detail</h5>
                    <div class="ms-auto d-flex gap-2">
                        <a href="{{ route('admin.edit-vendor', $vendor->vendor_id) }}" class="btn btn-theme btn-sm">
                            <i class="ri-pencil-line me-1"></i>Edit Vendor
                        </a>
                        <!-- Vendor back link removed -->
                    </div>
                </div>

                <div class="row g-4">

                    {{-- Profile Card --}}
                    <div class="col-xl-4 col-lg-5">
                        <div class="card h-100">
                            <div class="card-body text-center pb-4">
                                <div class="vendor-profile-img mb-3">
                                    <img src="{{ asset('public/uploads/vendors/vendor.png') }}" class="rounded-circle" width="100" height="100" style="object-fit:cover;border:3px solid var(--theme-color,#0da487)" alt="{{ $vendor->owner_name }}">
                                </div>
                                <h5 class="mb-1">{{ $vendor->owner_name }}</h5>
                                <p class="text-muted mb-2">{{ $vendor->business_name }}</p>
                                @php
                                    $isActive = in_array(strtolower((string) ($vendor->status ?? 'inactive')), ['1', 'active'], true);
                                    $status = $isActive ? 'active' : 'inactive';
                                    $statusClass = $isActive ? 'badge-light-success' : 'badge-light-secondary';
                                @endphp
                                <span class="badge {{ $statusClass }} rounded-pill px-3 py-2 text-capitalize">{{ $status }}</span>

                                <hr class="my-3">

                                <div class="vendor-contact-list text-start">
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="vendor-icon-box me-3"><i class="ri-mail-line"></i></span>
                                        <div>
                                            <small class="text-muted d-block">Email</small>
                                            <span class="fw-500">{{ $vendor->email ?? '—' }}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="vendor-icon-box me-3"><i class="ri-phone-line"></i></span>
                                        <div>
                                            <small class="text-muted d-block">Mobile</small>
                                            <span class="fw-500">{{ $vendor->mobile ? '+91 '.$vendor->mobile : '—' }}</span>
                                        </div>
                                    </div>
                                    @if(!empty($vendor->alternate_mobile))
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="vendor-icon-box me-3"><i class="ri-phone-2-line"></i></span>
                                        <div>
                                            <small class="text-muted d-block">Alternate</small>
                                            <span class="fw-500">+91 {{ $vendor->alternate_mobile }}</span>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="d-flex align-items-start mb-3">
                                        <span class="vendor-icon-box me-3"><i class="ri-map-pin-line"></i></span>
                                        <div>
                                            <small class="text-muted d-block">Address</small>
                                            <span class="fw-500">{{ $vendor->address ?? '—' }}</span>
                                        </div>
                                    </div>
                                    @if(!empty($vendor->commission_percent))
                                    <div class="d-flex align-items-center">
                                        <span class="vendor-icon-box me-3"><i class="ri-percent-line"></i></span>
                                        <div>
                                            <small class="text-muted d-block">Commission</small>
                                            <span class="fw-500">{{ $vendor->commission_percent }}%</span>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Detail Cards --}}
                    <div class="col-xl-8 col-lg-7">
                        <div class="row g-3">

                            {{-- Business Info --}}
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header card-header-2">
                                        <h5><i class="ri-building-line me-2"></i>Business Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="vendor-detail-item">
                                                    <label>Business Type</label>
                                                    <span>{{ $vendor->business_type ?? '—' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="vendor-detail-item">
                                                    <label>GST Number</label>
                                                    <span>{{ $vendor->gst_number ?? '—' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="vendor-detail-item">
                                                    <label>PAN Number</label>
                                                    <span>{{ $vendor->pan_number ?? '—' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="vendor-detail-item">
                                                    <label>Business Email</label>
                                                    <span>{{ data_get($vendor, 'business_email') ?: '—' }}</span>
                                                </div>
                                            </div>
                                            @if(!empty($vendor->business_description))
                                            <div class="col-12">
                                                <div class="vendor-detail-item">
                                                    <label>Business Description</label>
                                                    <span>{{ $vendor->business_description }}</span>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Banking Info --}}
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header card-header-2">
                                        <h5><i class="ri-bank-line me-2"></i>Banking Details</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="vendor-detail-item">
                                                    <label>Bank Name</label>
                                                    <span>{{ $vendor->bank_name ?? '—' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="vendor-detail-item">
                                                    <label>Account Holder</label>
                                                    <span>{{ $vendor->account_holder_name ?? '—' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="vendor-detail-item">
                                                    <label>Account Number</label>
                                                    <span>
                                                        @if(!empty($vendor->bank_account))
                                                            {{ $vendor->bank_account }}
                                                        @else
                                                            —
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="vendor-detail-item">
                                                    <label>IFSC Code</label>
                                                    <span>{{ $vendor->ifsc_code ?? '—' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="vendor-detail-item">
                                                    <label>UPI ID</label>
                                                    <span>{{ $vendor->upi_id ?? '—' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header card-header-2">
                                        <h5><i class="ri-building-4-line me-2"></i>Venue Details</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>S.No.</th>
                                                        <th>Venue Name</th>
                                                        <th>City</th>
                                                        <th>Capacity</th>
                                                        <th>Price / Booking</th>
                                                        <th>Status</th>
                                                        <th>Created Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($venues as $venue)
                                                        @php
                                                            $isVenueActive = in_array(strtolower((string) ($venue->status ?? '0')), ['1', 'active', 'approved', 'enabled'], true);
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>{{ $venue->venue_name ?: '-' }}</td>
                                                            <td>{{ $venue->city ?: '-' }}</td>
                                                            <td>{{ $venue->capacity ?: '-' }}</td>
                                                            <td>₹{{ number_format((float) ($venue->price_per_booking ?? 0), 2) }}</td>
                                                            <td>
                                                                <span class="badge {{ $isVenueActive ? 'bg-success' : 'bg-danger' }}">
                                                                    {{ $isVenueActive ? 'Active' : 'Inactive' }}
                                                                </span>
                                                            </td>
                                                            <td>{{ !empty($venue->created_at) ? \Carbon\Carbon::parse($venue->created_at)->format('d M Y') : '-' }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="7" class="text-center text-muted py-3">No venues found for this vendor.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header card-header-2">
                                        <h5><i class="ri-calendar-check-line me-2"></i>Booking History</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>S.No.</th>
                                                        <th>Booking ID</th>
                                                        <th>Venue</th>
                                                        <th>Customer Name</th>
                                                        <th>Mobile No.</th>
                                                        <th>Booking Date</th>
                                                        <th>Amount</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($bookingHistory as $booking)
                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>#{{ $booking->booking_id }}</td>
                                                            <td>{{ $booking->venue_name ?? 'N/A' }}</td>
                                                            <td>{{ $booking->customer_name ?? 'N/A' }}</td>
                                                            <td>{{ $booking->customer_phone ?? 'N/A' }}</td>
                                                            <td>{{ !empty($booking->booking_date) ? \Carbon\Carbon::parse($booking->booking_date)->format('d M Y, h:i A') : 'N/A' }}</td>
                                                            <td>₹{{ number_format((float) ($booking->booking_amount ?? 0), 2) }}</td>
                                                            <td>{{ ucfirst(str_replace('_', ' ', (string) ($booking->booking_status ?? 'pending'))) }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="8" class="text-center text-muted py-3">No booking history available.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header card-header-2">
                                        <h5><i class="ri-wallet-3-line me-2"></i>Payment Transactions</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-warning py-2 mb-3">
                                            Payment will be discussed manually over a call before proceeding with the payment.
                                        </div>

                                        <div class="table-responsive mb-3">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>S.No.</th>
                                                        <th>Order No.</th>
                                                        <th>Amount</th>
                                                        <th>Payment Method</th>
                                                        <th>Payment Status</th>
                                                        <th>Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($paymentTransactions as $transaction)
                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>{{ $transaction->order_number ?: ('#' . $transaction->order_id) }}</td>
                                                            <td>₹{{ number_format((float) ($transaction->total_amount ?? 0), 2) }}</td>
                                                            <td>{{ strtoupper((string) ($transaction->payment_method ?? 'N/A')) }}</td>
                                                            <td>{{ ucfirst((string) ($transaction->payment_status ?? 'pending')) }}</td>
                                                            <td>{{ !empty($transaction->created_at) ? \Carbon\Carbon::parse($transaction->created_at)->format('d M Y, h:i A') : '-' }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="6" class="text-center text-muted py-3">No payment transactions available.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>

                                        <h6 class="mb-2">Commission Settlement Records</h6>
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>S.No.</th>
                                                        <th>Settlement ID</th>
                                                        <th>Period</th>
                                                        <th>Payout Amount</th>
                                                        <th>Status</th>
                                                        <th>Processed On</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($commissionSettlements as $settlement)
                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>#{{ $settlement->settlement_id }}</td>
                                                            <td>
                                                                {{ !empty($settlement->period_start) ? \Carbon\Carbon::parse($settlement->period_start)->format('d M Y') : '-' }}
                                                                to
                                                                {{ !empty($settlement->period_end) ? \Carbon\Carbon::parse($settlement->period_end)->format('d M Y') : '-' }}
                                                            </td>
                                                            <td>₹{{ number_format((float) ($settlement->payout_amount ?? 0), 2) }}</td>
                                                            <td>{{ ucfirst((string) ($settlement->status ?? 'pending')) }}</td>
                                                            <td>{{ !empty($settlement->processed_at) ? \Carbon\Carbon::parse($settlement->processed_at)->format('d M Y, h:i A') : '-' }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="6" class="text-center text-muted py-3">No commission settlement records available.</td>
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
    </div>
</div>

@section('scripts')
<style>
.vendor-icon-box {
    width:38px;height:38px;border-radius:8px;background:rgba(13,164,135,.1);
    display:flex;align-items:center;justify-content:center;
    color:var(--theme-color,#0da487);font-size:18px;flex-shrink:0;
}
.vendor-detail-item { padding:10px 14px;background:#f8f9fa;border-radius:8px;height:100%; }
.vendor-detail-item label { font-size:11px;font-weight:600;text-transform:uppercase;color:#9197a3;display:block;margin-bottom:4px; }
.vendor-detail-item span, .vendor-detail-item a {
    font-size:14px;
    color:#222;
    font-weight:500;
    display:block;
    white-space:normal;
    overflow-wrap:anywhere;
    word-break:break-word;
}
</style>
@endsection
@endsection
