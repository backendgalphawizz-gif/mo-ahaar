@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        @include('admin.partials.figma-page-header', [
            'title' => 'Restaurant Management',
            'subtitle' => 'View and manage all restaurants',
            'actionUrl' => route('admin.add-vendor'),
            'actionLabel' => 'Add Restaurant',
        ])

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="card dashboard-card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.vendors') }}" class="figma-toolbar align-items-center">
                    <div class="toolbar-search">
                        <i class="ri-search-line"></i>
                        <input type="text" name="search" class="form-control" placeholder="Search Restaurants..." value="{{ $search }}">
                    </div>
                    <input type="hidden" name="status" value="{{ $status }}" id="vendorStatusInput">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control d-none" id="vendorDateFrom">
                    <button type="button" class="figma-btn-filter" onclick="document.getElementById('vendorDateFrom').showPicker?.() || document.getElementById('vendorDateFrom').focus()">
                        <i class="ri-filter-3-line"></i> Filter by Date
                    </button>
                    @if(request('date_from'))
                        <span class="small text-muted">{{ \Carbon\Carbon::parse(request('date_from'))->format('d/m/Y') }}</span>
                        <a href="{{ route('admin.vendors', request()->except('date_from')) }}" class="small text-danger">Clear date</a>
                    @endif
                    <span class="toolbar-spacer"></span>
                    <a href="{{ route('admin.vendors.export-excel', request()->query()) }}" class="figma-btn-export">
                        <i class="ri-download-line"></i> Export All
                    </a>
                </form>

                <div class="figma-line-tabs">
                    @foreach(['all' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'suspended' => 'Suspended'] as $key => $label)
                        <a class="tab-link {{ $status === $key ? 'active' : '' }}"
                           href="{{ route('admin.vendors', array_merge(request()->except('status'), ['status' => $key])) }}">{{ $label }}</a>
                    @endforeach
                </div>

                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Restaurant Info</th>
                                <th>Owner Info</th>
                                <th>Stats</th>
                                <th>Financials</th>
                                <th>Rating</th>
                                <th>Joined Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vendors as $vendor)
                                @php
                                    $statusKey = strtolower((string) ($vendor->approval_status ?? 'pending'));
                                    $statusLabel = strtoupper($statusKey === 'approved' ? 'APPROVED' : ($statusKey === 'pending' ? 'PENDING' : $statusKey));
                                    $statusLabelClass = match ($statusKey) {
                                        'approved' => 'on',
                                        'pending' => 'pending',
                                        'suspended' => 'suspended',
                                        default => 'off',
                                    };
                                    $resCode = 'RES-' . str_pad((string) $vendor->vendor_id, 3, '0', STR_PAD_LEFT);
                                    $isApproved = $statusKey === 'approved';
                                    $bizPhone = $vendor->business_phone ?: $vendor->mobile;
                                @endphp
                                <tr>
                                    <td class="fw-semibold text-nowrap">#{{ $resCode }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $vendor->business_name ?: '—' }}</div>
                                        <small class="text-muted">+91 {{ $bizPhone }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ $vendor->owner_name }}</div>
                                        <small class="text-muted">+91 {{ $vendor->mobile }}</small>
                                    </td>
                                    <td class="small text-nowrap">
                                        <div>Products: <strong>{{ $productCounts[$vendor->vendor_id] ?? 0 }}</strong></div>
                                        <div>Categories: <strong>{{ $categoryCounts[$vendor->vendor_id] ?? 0 }}</strong></div>
                                        <div>Orders: <strong>{{ $orderCounts[$vendor->vendor_id] ?? 0 }}</strong></div>
                                    </td>
                                    <td class="small text-nowrap">
                                        <div>Wallet: <strong>₹{{ number_format((float) ($vendor->wallet_balance ?? 0), 0) }}</strong></div>
                                        <div>Withdrawn: <strong>₹{{ number_format((float) ($vendor->withdrawal_amount ?? 0), 0) }}</strong></div>
                                        <div class="text-danger fw-semibold">Comm: {{ number_format((float) ($vendor->commission_percent ?? 0), 0) }}%</div>
                                    </td>
                                    <td class="text-nowrap">
                                        @php
                                            $rating = $vendorRatings[$vendor->vendor_id] ?? null;
                                            $reviewCount = $reviewCounts[$vendor->vendor_id] ?? 0;
                                        @endphp
                                        @if($rating !== null)
                                            <span class="text-warning fw-semibold">{{ number_format($rating, 1) }}</span>
                                            <i class="ri-star-fill text-warning"></i>
                                            <div class="small text-muted">({{ $reviewCount }} {{ $reviewCount === 1 ? 'review' : 'reviews' }})</div>
                                        @else
                                            <span class="text-muted small">No ratings yet</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">{{ optional($vendor->created_at)->format('d/m/Y') ?: '—' }}</td>
                                    <td class="text-nowrap">
                                        <form method="POST" action="{{ route('admin.vendors.approval-status', $vendor->vendor_id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="approval_status" value="{{ $isApproved ? 'suspended' : 'approved' }}">
                                            <label class="figma-switch d-block mb-1">
                                                <input type="checkbox" onchange="this.form.submit()" {{ $isApproved ? 'checked' : '' }}>
                                                <span class="slider"></span>
                                            </label>
                                        </form>
                                        <span class="status-label {{ $statusLabelClass }}">{{ $statusLabel }}</span>
                                    </td>
                                    <td>
                                        <div class="figma-icon-actions">
                                            <a href="{{ route('admin.view-vendor', $vendor->vendor_id) }}" class="figma-icon-btn view" title="View"><i class="ri-eye-line"></i></a>
                                            <a href="{{ route('admin.edit-vendor', $vendor->vendor_id) }}" class="figma-icon-btn edit" title="Edit"><i class="ri-pencil-line"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted py-5">No restaurants found.</td></tr>
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
<script>
document.getElementById('vendorDateFrom')?.addEventListener('change', function () {
    if (this.value) {
        this.closest('form').submit();
    }
});
</script>
@endsection
