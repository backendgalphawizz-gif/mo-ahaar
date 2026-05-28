@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
            <h5 class="mb-0">Payment Management</h5>
        </div>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        <div class="card dashboard-card">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <form method="GET" action="{{ route('admin.payments.status') }}" class="d-flex flex-wrap gap-2 flex-grow-1">
                        <div class="input-group" style="max-width:280px;">
                            <span class="input-group-text"><i class="ri-search-line"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search payments..." value="{{ $search ?? '' }}">
                        </div>
                        <select name="status" class="form-select" style="max-width:160px;">
                            <option value="">All Status</option>
                            <option value="paid" {{ ($status ?? '') === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="failed" {{ ($status ?? '') === 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="refunded" {{ ($status ?? '') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                        <button type="submit" class="btn btn-outline-secondary btn-sm">Filter</button>
                    </form>
                    <a href="{{ route('admin.orders.export-excel') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="ri-download-line me-1"></i>Export
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Vendor</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                @php
                                    $payBadge = match (strtolower((string) $order->payment_status)) {
                                        'paid' => 'badge-soft-success',
                                        'failed', 'refunded' => 'badge-soft-danger',
                                        default => 'badge-soft-warning',
                                    };
                                @endphp
                                <tr>
                                    <td>TXN-{{ str_pad((string) $order->order_id, 3, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $order->order_number }}</td>
                                    <td>{{ optional(optional($order->customer)->user)->name ?? 'N/A' }}</td>
                                    <td class="text-warning fw-medium">{{ optional($order->vendor)->business_name ?? 'N/A' }}</td>
                                    <td>₹{{ number_format((float) $order->total_amount, 0) }}</td>
                                    <td>{{ strtoupper($order->payment_method ?? '—') }}</td>
                                    <td>{{ optional($order->created_at)->format('d-m-Y') }}</td>
                                    <td><span class="badge {{ $payBadge }}">{{ ucfirst($order->payment_status ?? 'pending') }}</span></td>
                                    <td>
                                        <a href="{{ route('admin.order-details', $order->order_id) }}" class="btn btn-sm btn-outline-secondary" title="View"><i class="ri-eye-line"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center py-4 text-muted">No payment records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
