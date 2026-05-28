@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        <div class="card dashboard-card">
            <div class="card-body">
                @include('admin.partials.figma-page-header', [
                    'title' => 'Vendor Management',
                    'subtitle' => 'Manage restaurants and vendor accounts',
                ])
                <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                    <form method="GET" action="{{ route('admin.vendors') }}" class="ms-auto d-flex flex-wrap gap-2">
                        <div class="input-group" style="min-width:260px;">
                            <span class="input-group-text"><i class="ri-search-line"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search shop or vendor..." value="{{ $search }}">
                        </div>
                        <input type="hidden" name="status" value="{{ $status }}">
                        <a href="{{ route('admin.vendors.export-excel', request()->query()) }}" class="btn btn-outline-secondary">
                            <i class="ri-download-line me-1"></i>Export
                        </a>
                        <a href="{{ route('admin.add-vendor') }}" class="btn btn-theme">
                            <i class="ri-add-line me-1"></i>Add Vendor
                        </a>
                    </form>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <ul class="nav nav-tabs vendor-status-tabs mb-3">
                    @foreach(['all' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'suspended' => 'Suspended'] as $key => $label)
                        <li class="nav-item">
                            <a class="nav-link {{ $status === $key ? 'active' : '' }}"
                               href="{{ route('admin.vendors', array_merge(request()->except('status'), ['status' => $key])) }}">{{ $label }}</a>
                        </li>
                    @endforeach
                </ul>

                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Sl No.</th>
                                <th>Shop Name</th>
                                <th>Vendor Name</th>
                                <th>Contact Info</th>
                                <th>Products</th>
                                <th>Orders</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vendors as $vendor)
                                @php
                                    $statusKey = strtolower((string) ($vendor->approval_status ?? 'pending'));
                                    $badgeClass = match ($statusKey) {
                                        'approved' => 'badge-soft-success',
                                        'suspended' => 'badge-soft-danger',
                                        default => 'badge-soft-warning',
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $vendor->business_name }}</td>
                                    <td>{{ $vendor->owner_name }}</td>
                                    <td>
                                        <div>{{ $vendor->email }}</div>
                                        <small class="text-muted">+91 {{ $vendor->mobile }}</small>
                                    </td>
                                    <td>{{ $productCounts[$vendor->vendor_id] ?? 0 }}</td>
                                    <td>{{ $orderCounts[$vendor->vendor_id] ?? 0 }}</td>
                                    <td><span class="badge {{ $badgeClass }}">{{ ucfirst($statusKey) }}</span></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.view-vendor', $vendor->vendor_id) }}" class="btn btn-sm btn-outline-primary" title="View"><i class="ri-eye-line"></i></a>
                                            <a href="{{ route('admin.edit-vendor', $vendor->vendor_id) }}" class="btn btn-sm btn-outline-warning" title="Edit"><i class="ri-pencil-line"></i></a>
                                            <form method="POST" action="{{ route('admin.vendors.toggle-block', $vendor->vendor_id) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Block/Unblock"><i class="ri-forbid-line"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No vendors found.</td>
                                </tr>
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
.vendor-status-tabs .nav-link { color: #64748b; border: none; background: transparent; }
.vendor-status-tabs .nav-link.active { color: #111827; font-weight: 600; border-bottom: 2px solid #c9973a; }
</style>
@endsection
