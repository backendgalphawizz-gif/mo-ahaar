@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        @include('admin.partials.figma-page-header', [
            'title' => 'User Transactions',
            'subtitle' => 'View and manage all user transactions',
        ])

        <div class="card dashboard-card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.customers.transactions') }}" class="figma-toolbar">
                    <input type="text" name="search" class="form-control form-control-sm" style="max-width:220px;" placeholder="Search Transaction ID..." value="{{ $search }}">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="paid" {{ ($status ?? '') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="failed" {{ ($status ?? '') === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Apply</button>
                    <span class="toolbar-spacer"></span>
                    <a href="{{ route('admin.orders.export-excel', array_filter(['search' => $search ?? null, 'payment_status' => $status ?? null])) }}" class="btn btn-outline-secondary btn-sm"><i class="ri-download-line me-1"></i>Export Orders (Excel)</a>
                </form>

                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Order ID</th>
                                <th>Customer Info</th>
                                <th>Restaurant Info</th>
                                <th>Total Amount</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th>Date &amp; Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                @php
                                    $customer = $order->customer?->user;
                                    $initials = collect(explode(' ', (string) ($customer->name ?? 'NA')))->filter()->take(2)->map(fn ($p) => strtoupper(substr($p, 0, 1)))->implode('');
                                    $payBadge = match (strtolower((string) $order->payment_status)) {
                                        'paid' => 'badge-soft-success',
                                        'failed', 'refunded' => 'badge-soft-danger',
                                        default => 'badge-soft-warning',
                                    };
                                @endphp
                                <tr>
                                    <td class="fw-semibold">#TRX-{{ $order->order_id }}</td>
                                    <td>{{ $order->order_number }}</td>
                                    <td>
                                        <div class="cell-with-avatar">
                                            <span class="user-avatar">{{ $initials ?: 'NA' }}</span>
                                            <div>
                                                <div>{{ $customer->name ?? '—' }}</div>
                                                @if(!empty($customer?->mobile))<small class="text-muted">+91 {{ $customer->mobile }}</small>@endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>{{ $order->vendor?->business_name ?? '—' }}</div>
                                        @if(!empty($order->vendor?->mobile))<small class="text-muted">+91 {{ $order->vendor->mobile }}</small>@endif
                                    </td>
                                    <td class="fw-semibold">₹{{ number_format((float) $order->total_amount, 0) }}</td>
                                    <td>{{ strtoupper($order->payment_method ?? '—') }}</td>
                                    <td><span class="badge {{ $payBadge }}">{{ ucfirst($order->payment_status ?? 'pending') }}</span></td>
                                    <td>
                                        <div>{{ optional($order->created_at)->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ optional($order->created_at)->format('h:i A') }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center py-4 text-muted">No transactions found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(method_exists($orders, 'hasPages') && $orders->hasPages())
                    <div class="mt-3">{{ $orders->links('pagination::bootstrap-5') }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
