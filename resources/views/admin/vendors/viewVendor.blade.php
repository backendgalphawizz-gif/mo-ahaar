@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
@php
    $statusKey = strtolower((string) ($vendor->approval_status ?? 'pending'));
    $statusBadge = match ($statusKey) {
        'approved' => 'badge-soft-success',
        'suspended' => 'badge-soft-secondary',
        'rejected' => 'badge-soft-danger',
        default => 'badge-soft-warning',
    };
    $resCode = 'RES-' . str_pad((string) $vendor->vendor_id, 3, '0', STR_PAD_LEFT);
@endphp
<div class="page-body">
    <div class="container-fluid">
        <div class="vendor-wizard-head d-flex flex-wrap align-items-start gap-3 mb-4">
            <a href="{{ route('admin.vendors') }}" class="btn-back-figma" title="Back"><i class="ri-arrow-left-line"></i></a>
            <div class="flex-grow-1">
                <h4 class="figma-page-title mb-1">Vendor Details</h4>
                <div class="d-flex align-items-center flex-wrap gap-2 mt-1">
                    <span class="vendor-code-accent fw-semibold">#{{ $resCode }}</span>
                    <span class="badge {{ $statusBadge }}">{{ strtoupper($statusKey) }}</span>
                </div>
            </div>
            <div class="d-flex gap-2 ms-auto">
                @if($statusKey !== 'rejected')
                    <form method="POST" action="{{ route('admin.vendors.approval-status', $vendor->vendor_id) }}">
                        @csrf
                        <input type="hidden" name="approval_status" value="rejected">
                        <button type="submit" class="btn btn-figma-primary">Reject</button>
                    </form>
                @endif
                @if($statusKey !== 'approved')
                    <form method="POST" action="{{ route('admin.vendors.approval-status', $vendor->vendor_id) }}">
                        @csrf
                        <input type="hidden" name="approval_status" value="approved">
                        <button type="submit" class="btn btn-outline-success">Approve</button>
                    </form>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="row g-3 mb-4 vendor-detail-kpi">
            <div class="col-md-3">
                <div class="card dashboard-card h-100"><div class="card-body">
                    <small>Wallet Amount</small>
                    <h4>₹{{ number_format((float) ($vendor->wallet_balance ?? 0), 0) }}</h4>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card h-100"><div class="card-body">
                    <small>Withdrawal Amount</small>
                    <h4>₹{{ number_format((float) ($vendor->withdrawal_amount ?? 0), 0) }}</h4>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card h-100"><div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small>Total Commission</small>
                        <h4>{{ number_format((float) ($vendor->commission_percent ?? 0), 0) }}%</h4>
                    </div>
                    <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#commissionModal" title="Edit commission">
                        <i class="ri-pencil-line text-primary"></i>
                    </button>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card h-100"><div class="card-body">
                    <small>Refund Balance</small>
                    <h4>₹{{ number_format((float) ($vendor->refund_balance ?? 0), 0) }}</h4>
                </div></div>
            </div>
        </div>

        <div class="card dashboard-card">
            <div class="card-body p-4">
                <ul class="nav nav-tabs vendor-detail-tabs border-0">
                    @foreach(['details' => 'Details', 'food' => 'Food List', 'orders' => 'Order List', 'reviews' => 'Reviews'] as $key => $label)
                        <li class="nav-item">
                            <a class="nav-link {{ ($tab ?? 'details') === $key ? 'active' : '' }}"
                               href="{{ route('admin.view-vendor', ['id' => $vendor->vendor_id, 'tab' => $key]) }}">{{ $label }}</a>
                        </li>
                    @endforeach
                </ul>

                @if(($tab ?? 'details') === 'details')
                    <div class="row g-4">
                        <div class="col-md-6 vendor-detail-section">
                            <h6>Personal Information</h6>
                            <p><strong>Vendor Name:</strong> {{ $vendor->owner_name }}</p>
                            <p><strong>Mobile No.:</strong> +91 {{ $vendor->mobile }}</p>
                            <p><strong>Email Address:</strong> {{ $vendor->email ?: '—' }}</p>
                        </div>
                        <div class="col-md-6 vendor-detail-section">
                            <h6>Business Information</h6>
                            <p><strong>Business Name:</strong> {{ $vendor->business_name ?: '—' }}</p>
                            <p><strong>Business Phone:</strong> {{ $vendor->business_phone ?: '—' }}</p>
                            <p><strong>GSTIN:</strong> {{ $vendor->gst_number ?: '—' }}</p>
                            <p><strong>Business Address:</strong> {{ $vendor->address ?: '—' }}</p>
                        </div>
                        <div class="col-md-6 vendor-detail-section">
                            <h6>Bank Information</h6>
                            <p><strong>Bank Name:</strong> {{ $vendor->bank_name ?: '—' }}</p>
                            <p><strong>Branch Name:</strong> {{ $vendor->branch_name ?: '—' }}</p>
                            <p><strong>Account No.:</strong> {{ $vendor->bank_account ?: '—' }}</p>
                            <p><strong>IFSC Code & Acc Type:</strong> {{ $vendor->ifsc_code ?: '—' }}{{ $vendor->account_type ? ' (' . $vendor->account_type . ')' : '' }}</p>
                        </div>
                        <div class="col-md-6 vendor-detail-section">
                            <h6>Documents & Images</h6>
                            <div class="row g-2">
                                @foreach(['aadhaar_card' => 'Aadhaar', 'pan_card' => 'PAN', 'business_logo' => 'Logo'] as $field => $label)
                                    <div class="col-md-4">
                                        <div class="doc-box-figma">
                                            <div class="text-muted mb-2 fw-medium">{{ $label }}</div>
                                            @if(!empty($vendor->{$field}))
                                                @php
                                                    $docPath = in_array($field, ['aadhaar_card', 'pan_card'], true)
                                                        ? 'public/uploads/vendors/documents/' . $vendor->{$field}
                                                        : 'public/uploads/vendors/' . $vendor->{$field};
                                                @endphp
                                                <a href="{{ asset($docPath) }}" target="_blank" class="small">View File</a>
                                            @else
                                                <span class="text-muted small">No file</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @elseif(($tab ?? '') === 'food')
                    <div class="table-responsive">
                        <table class="table table-modern align-middle">
                            <thead><tr><th>Product Name</th><th>Price</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse($products as $product)
                                    <tr>
                                        <td>{{ $product->product_name }}</td>
                                        <td>₹{{ number_format((float) ($product->price ?? 0), 0) }}</td>
                                        <td>{{ ((int) ($product->status ?? 0) === 1) ? 'Active' : 'Inactive' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-4">No products found for this vendor.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @elseif(($tab ?? '') === 'orders')
                    <div class="table-responsive">
                        <table class="table table-modern align-middle">
                            <thead><tr><th>Order ID</th><th>Date / Time</th><th>Customer Info</th><th>Amount</th><th>Status</th><th>Action</th></tr></thead>
                            <tbody>
                                @forelse($orders as $order)
                                    @php
                                        $orderStatus = strtolower((string) ($order->order_status ?? 'pending'));
                                        $orderBadge = in_array($orderStatus, ['delivered', 'completed']) ? 'badge-soft-success' : 'badge-soft-warning';
                                    @endphp
                                    <tr>
                                        <td>{{ $order->order_number ?? ('ORD-' . $order->order_id) }}</td>
                                        <td>{{ optional($order->created_at)->format('Y-m-d H:i') }}</td>
                                        <td>{{ optional(optional($order->customer)->user)->name ?? 'N/A' }}</td>
                                        <td>₹{{ number_format((float) ($order->total_amount ?? 0), 0) }}</td>
                                        <td><span class="badge {{ $orderBadge }}">{{ ucfirst($orderStatus) }}</span></td>
                                        <td><a href="{{ route('admin.order-details', $order->order_id) }}">View</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">No orders found for this vendor.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-modern align-middle">
                            <thead><tr><th>Sl No.</th><th>Product Name</th><th>Customer Info</th><th>Review</th><th>Rating</th></tr></thead>
                            <tbody>
                                @forelse($reviews as $review)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $review->product_name ?? 'N/A' }}</td>
                                        <td>{{ $review->customer_name ?? 'N/A' }}</td>
                                        <td>{{ $review->review ?? '—' }}</td>
                                        <td>{{ $review->rating ?? 0 }} <i class="ri-star-fill text-warning"></i></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">No reviews found for this vendor.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="commissionModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.vendors.update-commission', $vendor->vendor_id) }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Update Commission</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label">Commission %</label>
                <input type="number" step="0.01" min="0" max="100" name="commission_percent" class="form-control" value="{{ $vendor->commission_percent ?? 15 }}" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-figma-primary">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
