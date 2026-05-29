@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
@php
    $driverCode = $profile->driver_code ?? ('DB-' . str_pad((string) $driver->user_id, 3, '0', STR_PAD_LEFT));
    $accountType = strtolower((string) ($profile->account_type ?? 'savings'));
    if ($accountType === 'saving') {
        $accountType = 'savings';
    }
    $approval = strtolower((string) ($driver->approval_status ?? 'pending'));
    $approvalBadge = match ($approval) {
        'approved' => 'badge-soft-success',
        'rejected' => 'badge-soft-danger',
        default => 'badge-soft-warning',
    };
    $isActive = (int) ($driver->status ?? 0) === 1;
    $docUrl = function (?string $file) {
        return $file ? asset('public/uploads/drivers/' . $file) : null;
    };
    $profilePhotoUrl = !empty($driver->profile_image)
        ? asset('public/uploads/drivers/' . $driver->profile_image)
        : null;
@endphp
<div class="page-body">
    <div class="container-fluid">
        <div class="vendor-wizard-head d-flex align-items-start gap-3 mb-4">
            <a href="{{ route('admin.delivery.index') }}" class="btn-back-figma" title="Back"><i class="ri-arrow-left-line"></i></a>
            <div class="flex-grow-1">
                <h4 class="figma-page-title mb-1">Delivery Boy Details <span class="vendor-code-accent">#{{ $driverCode }}</span></h4>
                <p class="figma-page-subtitle mb-0">View complete information for this driver</p>
            </div>
            <a href="{{ route('admin.delivery.edit', $driver->user_id) }}" class="btn btn-outline-warning btn-sm"><i class="ri-pencil-line me-1"></i>Edit</a>
        </div>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        <div class="card dashboard-card mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap align-items-start gap-4 mb-4 pb-3 border-bottom">
                    <div class="flex-shrink-0">
                        @if($profilePhotoUrl)
                            <img src="{{ $profilePhotoUrl }}" alt="{{ $driver->name }}" class="rounded-circle border" width="88" height="88" style="object-fit:cover;">
                        @else
                            <span class="rounded-circle bg-light border d-inline-flex align-items-center justify-content-center" style="width:88px;height:88px;">
                                <i class="ri-user-line fs-2 text-muted"></i>
                            </span>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <h5 class="mb-0 fw-semibold">{{ $driver->name }}</h5>
                            <span class="badge bg-light text-dark border">#{{ $driverCode }}</span>
                            <span class="badge {{ $approvalBadge }}">{{ ucfirst($approval) }}</span>
                            <span class="badge {{ $isActive ? 'badge-soft-success' : 'badge-soft-secondary' }}">{{ $isActive ? 'Active' : 'Inactive' }}</span>
                        </div>
                        <div class="text-muted small">Registered on {{ optional($driver->created_at)->format('d M Y') ?: '—' }}</div>
                    </div>
                    <div class="text-end">
                        <div class="text-muted small">Wallet Balance</div>
                        <div class="fs-4 fw-bold text-success">₹{{ number_format((float) ($wallet->balance ?? 0), 0) }}</div>
                    </div>
                </div>

                <div class="figma-form-block mb-4">
                    <h6 class="mb-3">Personal Information</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="text-muted small">Full Name</div>
                            <div class="fw-semibold">{{ $driver->name }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Mobile Number</div>
                            <div class="fw-semibold">+91 {{ $driver->mobile }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Email Address</div>
                            <div class="fw-semibold">{{ $driver->email ?: '—' }}</div>
                        </div>
                        <div class="col-md-8">
                            <div class="text-muted small">Full Address</div>
                            <div class="fw-semibold">{{ $profile->address ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">City</div>
                            <div class="fw-semibold">{{ $profile->city ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Document Type</div>
                            <div class="fw-semibold">{{ strtoupper($profile->document_type ?? '—') }}</div>
                        </div>
                    </div>
                </div>

                <div class="figma-form-block mb-4">
                    <h6 class="mb-3">Identity Documents</h6>
                    <div class="row g-3">
                        @if(($profile->document_type ?? '') === 'pan')
                            <div class="col-md-6">
                                <div class="driver-doc-preview">
                                    <div class="text-muted small mb-2">PAN Card</div>
                                    @if($docUrl($profile->pan_card ?? null))
                                        <a href="{{ $docUrl($profile->pan_card) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">View Document</a>
                                    @else
                                        <span class="text-muted">Not uploaded</span>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="col-md-6">
                                <div class="driver-doc-preview">
                                    <div class="text-muted small mb-2">Aadhaar Front</div>
                                    @if($docUrl($profile->aadhar_card ?? null))
                                        <a href="{{ $docUrl($profile->aadhar_card) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">View Document</a>
                                    @else
                                        <span class="text-muted">Not uploaded</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="driver-doc-preview">
                                    <div class="text-muted small mb-2">Aadhaar Back</div>
                                    @if($docUrl($profile->aadhar_card_back ?? null))
                                        <a href="{{ $docUrl($profile->aadhar_card_back) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">View Document</a>
                                    @else
                                        <span class="text-muted">Not uploaded</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="figma-form-block mb-4">
                    <h6 class="mb-3">Vehicle Information</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Vehicle Registration Number</div>
                            <div class="fw-semibold">{{ $profile->vehicle_number ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Driving License Number</div>
                            <div class="fw-semibold">{{ $profile->driving_license_number ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">PUC Number</div>
                            <div class="fw-semibold">{{ $profile->puc_number ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">PUC Expiry</div>
                            <div class="fw-semibold">{{ !empty($profile?->puc_expiry_date) ? $profile->puc_expiry_date->format('d-m-Y') : '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">PUC Certificate</div>
                            <div class="fw-semibold">
                                @if($docUrl($profile->puc_image ?? null))
                                    <a href="{{ $docUrl($profile->puc_image) }}" target="_blank" rel="noopener">View Document</a>
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="driver-doc-preview">
                                <div class="text-muted small mb-2">Driving License</div>
                                @if($docUrl($profile->driving_license ?? null))
                                    <a href="{{ $docUrl($profile->driving_license) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">View Document</a>
                                @else
                                    <span class="text-muted">Not uploaded</span>
                                @endif
                            </div>
                        </div>
                        @if($docUrl($profile->rc_image ?? null))
                            <div class="col-md-6">
                                <div class="driver-doc-preview">
                                    <div class="text-muted small mb-2">RC Document</div>
                                    <a href="{{ $docUrl($profile->rc_image) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">View Document</a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="figma-form-block">
                    <h6 class="mb-3">Bank Details</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="text-muted small">Account Holder Name</div>
                            <div class="fw-semibold">{{ $profile->account_holder_name ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Bank Name</div>
                            <div class="fw-semibold">{{ $profile->bank_name ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Branch Name</div>
                            <div class="fw-semibold">{{ $profile->branch_name ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Account Number</div>
                            <div class="fw-semibold">{{ $profile->account_number ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">IFSC Code</div>
                            <div class="fw-semibold">{{ $profile->ifsc_code ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Account Type</div>
                            <div class="fw-semibold">{{ $accountType === 'current' ? 'Current' : 'Saving' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card dashboard-card">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                    <h6 class="mb-0">Delivery History</h6>
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

@section('scripts')
<style>
.driver-doc-preview {
    border: 1px solid #eceef2;
    border-radius: 10px;
    background: #f9fafb;
    min-height: 100px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}
</style>
@endsection
