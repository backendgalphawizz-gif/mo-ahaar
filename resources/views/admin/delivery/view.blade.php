@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
            <a href="{{ route('admin.delivery.index') }}" class="btn btn-outline-secondary btn-sm"><i class="ri-arrow-left-line"></i></a>
            <div>
                <h5 class="mb-0">{{ $profile->driver_code ?? 'Driver' }} - {{ $driver->name }}</h5>
                <small class="text-muted">Driver profile and delivery history</small>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('admin.delivery.edit', $driver->user_id) }}" class="btn btn-outline-warning btn-sm"><i class="ri-pencil-line me-1"></i>Edit</a>
                <a href="{{ route('admin.delivery.add') }}" class="btn btn-theme btn-sm"><i class="ri-add-line me-1"></i>Add Driver</a>
            </div>
        </div>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        <div class="row g-4 mb-4">
            <div class="col-lg-4">
                <div class="card dashboard-card h-100">
                    <div class="card-body">
                        <h6 class="mb-3">Driver Profile Information</h6>
                        <p><strong>Full Name:</strong> {{ $driver->name }}</p>
                        <p><strong>Mobile No.:</strong> +91 {{ $driver->mobile }}</p>
                        <p><strong>Email:</strong> {{ $driver->email }}</p>
                        <p><strong>Document Type:</strong> {{ strtoupper($profile->document_type ?? '—') }}</p>
                        <p><strong>Vehicle No.:</strong> {{ $profile->vehicle_number ?? '—' }}</p>
                        <p><strong>Driving License No.:</strong> {{ $profile->driving_license_number ?? '—' }}</p>
                        <p><strong>PUC No.:</strong> {{ $profile->puc_number ?? '—' }}{{ !empty($profile?->puc_expiry_date) ? ' (Exp: ' . $profile->puc_expiry_date->format('d-m-Y') . ')' : '' }}</p>
                        <p><strong>Wallet Balance:</strong> ₹{{ number_format((float) ($wallet->balance ?? 0), 0) }}</p>
                        <p class="mb-0"><strong>Status:</strong> <span class="badge badge-soft-warning">{{ ucfirst($driver->approval_status ?? 'pending') }}</span></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card dashboard-card h-100">
                    <div class="card-body">
                        <h6 class="mb-3">Bank Details</h6>
                        <p><strong>Account Name:</strong> {{ $profile->account_holder_name ?? '—' }}</p>
                        <p><strong>Bank Name:</strong> {{ $profile->bank_name ?? '—' }}</p>
                        <p><strong>Account No.:</strong> {{ $profile->account_number ?? '—' }}</p>
                        <p class="mb-0"><strong>IFSC Code:</strong> {{ $profile->ifsc_code ?? '—' }} ({{ ucfirst($profile->account_type ?? '') }})</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card dashboard-card h-100">
                    <div class="card-body">
                        <h6 class="mb-3">Documents</h6>
                        @if(($profile->document_type ?? '') === 'pan')
                            <p>
                                <strong>PAN Card:</strong>
                                @if(!empty($profile?->pan_card))
                                    <a href="{{ asset('public/uploads/drivers/' . $profile->pan_card) }}" target="_blank">View File</a>
                                @else — @endif
                            </p>
                        @else
                            <p>
                                <strong>Aadhaar Front:</strong>
                                @if(!empty($profile?->aadhar_card))
                                    <a href="{{ asset('public/uploads/drivers/' . $profile->aadhar_card) }}" target="_blank">View File</a>
                                @else — @endif
                            </p>
                            <p>
                                <strong>Aadhaar Back:</strong>
                                @if(!empty($profile?->aadhar_card_back))
                                    <a href="{{ asset('public/uploads/drivers/' . $profile->aadhar_card_back) }}" target="_blank">View File</a>
                                @else — @endif
                            </p>
                        @endif
                        <p>
                            <strong>RC Image:</strong>
                            @if(!empty($profile?->rc_image))
                                <a href="{{ asset('public/uploads/drivers/' . $profile->rc_image) }}" target="_blank">View File</a>
                            @else — @endif
                        </p>
                        <p>
                            <strong>Driving License Image:</strong>
                            @if(!empty($profile?->driving_license))
                                <a href="{{ asset('public/uploads/drivers/' . $profile->driving_license) }}" target="_blank">View File</a>
                            @else — @endif
                        </p>
                        <p class="mb-0">
                            <strong>PUC Image:</strong>
                            @if(!empty($profile?->puc_image))
                                <a href="{{ asset('public/uploads/drivers/' . $profile->puc_image) }}" target="_blank">View File</a>
                            @else — @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card dashboard-card">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                    <h6 class="mb-0">Delivery Details</h6>
                    <form method="GET" action="{{ route('admin.delivery.view', $driver->user_id) }}" class="ms-auto d-flex gap-2">
                        <div class="input-group" style="min-width:260px;">
                            <span class="input-group-text"><i class="ri-search-line"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search order ID or product..." value="{{ $search }}">
                        </div>
                        <button class="btn btn-outline-secondary">Filter</button>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Food Item</th>
                                <th>Customer Info</th>
                                <th>Vendor Info</th>
                                <th>Total Amount</th>
                                <th>Delivery Charge</th>
                                <th>Order Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deliveries as $assignment)
                                @php
                                    $order = $assignment->order;
                                    $items = $order?->orderItems ?? collect();
                                    $itemLabel = $items->map(fn ($item) => ($item->product_name ?? 'Item') . ' x' . ($item->quantity ?? 1))->implode(', ');
                                    $statusKey = strtolower((string) ($assignment->status ?? ''));
                                    $statusBadge = in_array($statusKey, ['delivered'], true) ? 'badge-soft-success' : 'badge-soft-warning';
                                    $statusLabel = \App\Models\DeliveryAssignment::statusLabel($assignment->status, true);
                                @endphp
                                <tr>
                                    <td>{{ $order->order_number ?? ('ORD-' . ($order->order_id ?? $assignment->order_id)) }}</td>
                                    <td>{{ $itemLabel ?: ($assignment->store_name ?? '—') }}</td>
                                    <td>{{ optional(optional(optional($order)->customer)->user)->name ?? 'N/A' }}</td>
                                    <td>{{ optional($order?->vendor)->business_name ?? ($assignment->store_name ?? 'N/A') }}</td>
                                    <td>₹{{ number_format((float) ($order->total_amount ?? 0), 0) }}</td>
                                    <td>₹{{ number_format((float) ($assignment->payout_amount ?? $order->shipping_amount ?? 0), 0) }}</td>
                                    <td>{{ optional($order?->created_at)->format('Y-m-d') ?: '—' }}</td>
                                    <td><span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-4">No delivery records found for this driver.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
