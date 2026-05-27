@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title d-flex align-items-center mb-4">
            <h5><i class="ri-secure-payment-line me-2"></i>{{ $title }}</h5>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 product-metric product-metric-primary h-100">
                    <div class="card-body">
                        <small>Total Orders</small>
                        <h3>{{ $stats['total'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 product-metric product-metric-success h-100">
                    <div class="card-body">
                        <small>Paid</small>
                        <h3>{{ $stats['paid'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 product-metric product-metric-warning h-100">
                    <div class="card-body">
                        <small>Pending</small>
                        <h3>{{ $stats['pending'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 product-metric product-metric-danger h-100">
                    <div class="card-body">
                        <small>Failed/Refunded</small>
                        <h3>{{ $stats['failed'] + $stats['refunded'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-table">
            <div class="card-body">
                <div class="product-search-toolbar">
                    <form method="GET" action="{{ route('admin.payments.status') }}" class="product-search-form product-search-form-wide">
                        <div class="product-search-field">
                            <i class="ri-search-line product-search-icon"></i>
                            <input type="text" name="search" class="form-control" value="{{ $search ?? '' }}" placeholder="Search by order no., vendor, customer, payment method, or status">
                        </div>
                        <div class="product-filter-field">
                            <select name="status" class="form-select">
                                <option value="">All Payment Status</option>
                                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>Failed</option>
                                <option value="refunded" {{ $status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-theme btn-sm">Search</button>
                        <a href="{{ route('admin.payments.status') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table all-package theme-table table-product align-middle text-start">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Order ID</th>
                                <th>Vendor</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Payment Status</th>
                                <th>Transaction Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>#{{ $order->order_number }}</td>
                                    <td>{{ optional($order->vendor)->business_name ?? optional($order->vendor)->owner_name ?? 'N/A' }}</td>
                                    <td>{{ optional(optional($order->customer)->user)->name ?? 'N/A' }}</td>
                                    <td>₹{{ number_format((float)$order->total_amount, 2) }}</td>
                                    <td>{{ ucfirst($order->payment_method) }}</td>
                                    <td>{{ ucfirst($order->payment_status) }}</td>
                                    <td>{{ optional($order->created_at)->format('d M Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-4">No records found.</td></tr>
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
.product-metric { border-radius: 12px; color: #fff; }
.product-metric .card-body { padding: 16px 18px; }
.product-metric small { text-transform: uppercase; letter-spacing: .05em; opacity: .9; }
.product-metric h3 { margin: 8px 0 0; font-weight: 700; }
.product-metric-primary { background: linear-gradient(135deg, #0f4c75, #3282b8); }
.product-metric-success { background: linear-gradient(135deg, #198754, #146c43); }
.product-metric-warning { background: linear-gradient(135deg, #fd7e14, #d0620a); }
.product-metric-danger { background: linear-gradient(135deg, #dc3545, #a71d2a); }
.product-search-form-wide {
    display: grid;
    grid-template-columns: minmax(280px, 1.8fr) minmax(220px, 1fr) auto auto;
    gap: 12px;
    align-items: center;
}
.product-search-field {
    position: relative;
}
.product-search-field .form-control {
    padding-left: 40px;
    min-height: 42px;
}
.product-search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #7c8798;
    font-size: 18px;
}
.product-filter-field .form-select {
    min-height: 42px;
}
@media (max-width: 991px) {
    .product-search-form-wide {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection

