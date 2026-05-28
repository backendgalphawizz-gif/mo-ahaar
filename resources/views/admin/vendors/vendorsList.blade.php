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
                <form method="GET" action="{{ route('admin.vendors') }}" class="figma-toolbar">
                    <div class="toolbar-search">
                        <i class="ri-search-line"></i>
                        <input type="text" name="search" class="form-control" placeholder="Search Restaurants..." value="{{ $search }}">
                    </div>
                    <input type="hidden" name="status" value="{{ $status }}" id="vendorStatusInput">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" style="max-width:150px;">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Filter</button>
                    <span class="toolbar-spacer"></span>
                    <a href="{{ route('admin.vendors.export-excel', request()->query()) }}" class="btn btn-outline-secondary btn-sm"><i class="ri-download-line me-1"></i>Export All</a>
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
                                <th>Status &amp; Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vendors as $vendor)
                                @php
                                    $statusKey = strtolower((string) ($vendor->approval_status ?? 'pending'));
                                    $statusLabel = strtoupper($statusKey === 'approved' ? 'APPROVED' : ($statusKey === 'pending' ? 'PENDING' : $statusKey));
                                    $statusClass = match ($statusKey) {
                                        'approved' => 'on',
                                        'pending' => 'text-warning',
                                        default => 'off',
                                    };
                                    $resCode = $vendor->vendor_code ?: ('RES-' . str_pad((string) $vendor->vendor_id, 3, '0', STR_PAD_LEFT));
                                    $isApproved = $statusKey === 'approved';
                                @endphp
                                <tr>
                                    <td class="fw-semibold">#{{ $resCode }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $vendor->business_name }}</div>
                                        <small class="text-muted">+91 {{ $vendor->mobile }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $vendor->owner_name }}</div>
                                        <small class="text-muted">+91 {{ $vendor->mobile }}</small>
                                    </td>
                                    <td class="small">
                                        <div>Products: <strong>{{ $productCounts[$vendor->vendor_id] ?? 0 }}</strong></div>
                                        <div>Orders: <strong>{{ $orderCounts[$vendor->vendor_id] ?? 0 }}</strong></div>
                                    </td>
                                    <td class="small">
                                        <div>Wallet: <strong>₹{{ number_format((float) ($vendor->wallet_balance ?? 0), 0) }}</strong></div>
                                        <div class="text-danger">Comm: {{ number_format((float) ($vendor->commission_percent ?? 0), 0) }}%</div>
                                    </td>
                                    <td><span class="text-warning fw-semibold">{{ number_format((float) ($vendor->avg_rating ?? 4.5), 1) }}</span> <i class="ri-star-fill text-warning"></i></td>
                                    <td>{{ optional($vendor->created_at)->format('d/m/Y') ?: '—' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.vendors.approval-status', $vendor->vendor_id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="approval_status" value="{{ $isApproved ? 'suspended' : 'approved' }}">
                                            <label class="figma-switch">
                                                <input type="checkbox" onchange="this.form.submit()" {{ $isApproved ? 'checked' : '' }}>
                                                <span class="slider"></span>
                                            </label>
                                        </form>
                                        <span class="status-label {{ $isApproved ? 'on' : 'off' }}">{{ $statusLabel }}</span>
                                        <div class="d-flex gap-2 table-action-icons mt-2">
                                            <a href="{{ route('admin.view-vendor', $vendor->vendor_id) }}" title="View"><i class="ri-eye-line"></i></a>
                                            <a href="{{ route('admin.edit-vendor', $vendor->vendor_id) }}" title="Edit"><i class="ri-pencil-line"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-5">No restaurants found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
