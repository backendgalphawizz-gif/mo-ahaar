@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        <h4 class="mb-1">Payment Requests</h4>
        <p class="text-muted mb-4">Manage withdrawal requests from vendors and delivery personnel.</p>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card dashboard-card">
            <div class="card-body">
                <h6 class="mb-3">All Requests</h6>
                <div class="d-flex justify-content-end gap-2 mb-3 flex-wrap">
                    <input type="text" class="form-control form-control-sm" style="max-width:240px;" placeholder="Search by ID, Name or Acc No...">
                    <select class="form-select form-select-sm" style="max-width:120px;">
                        <option>All Types</option>
                    </select>
                    <select class="form-select form-select-sm" style="max-width:120px;">
                        <option>All Status</option>
                    </select>
                </div>
                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>ID & Date</th>
                                <th>User</th>
                                <th>Payment Address</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Remark</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($settlements as $item)
                                @php
                                    $vendor = optional($item->vendor);
                                    $statusClass = match(strtolower((string) $item->status)) {
                                        'approved', 'paid' => 'badge-soft-success',
                                        'rejected' => 'badge-soft-danger',
                                        default => 'badge-soft-warning',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <strong>PRQ-{{ $item->settlement_id }}</strong>
                                        <div class="small text-muted">{{ optional($item->requested_at)->format('d/m/Y') ?: '-' }}</div>
                                    </td>
                                    <td>
                                        {{ $vendor->owner_name ?: $vendor->business_name ?: 'N/A' }}
                                        <div class="small text-muted">Vendor</div>
                                    </td>
                                    <td class="small">
                                        {{ $vendor->business_name ?: '-' }}<br>
                                        A/C: {{ $vendor->bank_account_number ?? 'N/A' }}<br>
                                        IFSC: {{ $vendor->ifsc_code ?? 'N/A' }}
                                    </td>
                                    <td><strong>₹ {{ number_format((float) $item->payout_amount, 2) }}</strong></td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.payments.settlements.update-status', $item->settlement_id) }}">
                                            @csrf
                                            <select name="status" class="form-select form-select-sm {{ $statusClass }}" onchange="this.form.submit()">
                                                @foreach(['approved', 'pending', 'rejected', 'processing', 'paid'] as $status)
                                                    <option value="{{ $status }}" {{ strtolower((string) $item->status) === $status ? 'selected' : '' }}>
                                                        {{ ucfirst($status) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </td>
                                    <td class="small text-muted">{{ $item->admin_note ?: ($item->request_note ?: '-') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.payments.settlements.show', $item->settlement_id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="ri-edit-2-line me-1"></i>Edit / View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">No payment requests found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
