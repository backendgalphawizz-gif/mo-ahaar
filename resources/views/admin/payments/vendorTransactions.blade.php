@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title d-flex align-items-center mb-4">
            <h5><i class="ri-exchange-dollar-line me-2"></i>{{ $title }}</h5>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 product-metric product-metric-primary h-100">
                    <div class="card-body">
                        <small>Paid Orders</small>
                        <h3>{{ $summary['paid_count'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 product-metric product-metric-success h-100">
                    <div class="card-body">
                        <small>Paid Total</small>
                        <h3>₹{{ number_format((float) ($summary['paid_total'] ?? 0), 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 product-metric product-metric-warning h-100">
                    <div class="card-body">
                        <small>Listed Transactions</small>
                        <h3>{{ $summary['all_count'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-table">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.payments.vendor-transactions') }}" class="row g-2 mb-3">
                    <div class="col-md-4">
                        <select name="vendor_id" class="form-select">
                            <option value="">All Vendors</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->vendor_id }}" {{ (string) ($vendorId ?? '') === (string) $vendor->vendor_id ? 'selected' : '' }}>
                                    {{ $vendor->business_name ?: $vendor->owner_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" value="{{ $search ?? '' }}" placeholder="Search order, payment method, or status">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-theme w-100">Filter</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Vendor</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $order)
                                <tr>
                                    <td>{{ $order->order_number ?? ('#' . $order->order_id) }}</td>
                                    <td>{{ optional($order->vendor)->business_name ?? optional($order->vendor)->owner_name ?? 'N/A' }}</td>
                                    <td>{{ optional(optional($order->customer)->user)->name ?? 'N/A' }}</td>
                                    <td>₹{{ number_format((float) ($order->total_amount ?? 0), 2) }}</td>
                                    <td>{{ ucfirst((string) ($order->payment_status ?? '')) }}</td>
                                    <td>{{ optional($order->created_at)->format('d-m-Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">No vendor transactions found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
