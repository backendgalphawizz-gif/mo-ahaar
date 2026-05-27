@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                    <h5 class="mb-0">Delivery Partner Management</h5>
                    <form method="GET" action="{{ route('admin.delivery.index') }}" class="ms-auto d-flex flex-wrap gap-2">
                        <div class="input-group" style="min-width:260px;">
                            <span class="input-group-text"><i class="ri-search-line"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search partners..." value="{{ $search }}">
                        </div>
                        <input type="hidden" name="status" value="{{ $status }}">
                        <a href="{{ route('admin.delivery.export-excel', request()->query()) }}" class="btn btn-outline-secondary">
                            <i class="ri-download-line me-1"></i>Export Data
                        </a>
                        <a href="{{ route('admin.delivery.add') }}" class="btn btn-theme">
                            <i class="ri-add-line me-1"></i>Add Driver
                        </a>
                    </form>
                </div>

                @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Sl No.</th>
                                <th>Profile & Name</th>
                                <th>Contact Info</th>
                                <th>Address</th>
                                <th>Wallet Bal.</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Active</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($drivers as $driver)
                                @php
                                    $profile = $profiles[$driver->user_id] ?? null;
                                    $wallet = $wallets[$driver->user_id] ?? null;
                                    $approval = strtolower((string) ($driver->approval_status ?? 'pending'));
                                    $badgeClass = match ($approval) {
                                        'approved' => 'badge-soft-success',
                                        'rejected' => 'badge-soft-danger',
                                        default => 'badge-soft-warning',
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if(!empty($driver->profile_image))
                                                <img src="{{ asset('public/uploads/drivers/' . $driver->profile_image) }}" class="rounded-circle" width="36" height="36" style="object-fit:cover;" alt="">
                                            @else
                                                <span class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px;"><i class="ri-user-line"></i></span>
                                            @endif
                                            <div>
                                                <div class="fw-semibold">{{ $driver->name }}</div>
                                                <small class="text-muted">{{ $profile->driver_code ?? ('DP-' . str_pad((string) $driver->user_id, 3, '0', STR_PAD_LEFT)) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>{{ $driver->email }}</div>
                                        <small class="text-muted">+91 {{ $driver->mobile }}</small>
                                    </td>
                                    <td>{{ $profile->address ?? '—' }}{{ !empty($profile?->city) ? ', ' . $profile->city : '' }}</td>
                                    <td class="text-success fw-semibold">₹{{ number_format((float) ($wallet->balance ?? 0), 0) }}</td>
                                    <td>{{ optional($driver->created_at)->format('d-m-Y') ?: '—' }}</td>
                                    <td><span class="badge {{ $badgeClass }}">{{ ucfirst($approval) }}</span></td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.delivery.toggle-status', $driver->user_id) }}">
                                            @csrf
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input" type="checkbox" onchange="this.form.submit()" {{ (int) $driver->status === 1 ? 'checked' : '' }} {{ $approval !== 'approved' ? 'disabled' : '' }}>
                                            </div>
                                        </form>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            @if($approval === 'pending')
                                                <form method="POST" action="{{ route('admin.delivery.approval-status', $driver->user_id) }}">
                                                    @csrf
                                                    <input type="hidden" name="approval_status" value="approved">
                                                    <button class="btn btn-sm btn-outline-success" title="Approve"><i class="ri-check-line"></i></button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.delivery.approval-status', $driver->user_id) }}">
                                                    @csrf
                                                    <input type="hidden" name="approval_status" value="rejected">
                                                    <button class="btn btn-sm btn-outline-danger" title="Reject"><i class="ri-close-line"></i></button>
                                                </form>
                                            @endif
                                            <a href="{{ route('admin.delivery.view', $driver->user_id) }}" class="btn btn-sm btn-outline-primary" title="View"><i class="ri-eye-line"></i></a>
                                            <a href="{{ route('admin.delivery.edit', $driver->user_id) }}" class="btn btn-sm btn-outline-warning" title="Edit"><i class="ri-pencil-line"></i></a>
                                            <form method="POST" action="{{ route('admin.delivery.delete', $driver->user_id) }}" onsubmit="return confirm('Delete this driver?');">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="ri-delete-bin-line"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted py-4">No delivery partners found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
