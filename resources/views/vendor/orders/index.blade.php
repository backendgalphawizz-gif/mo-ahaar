@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        <h5 class="mb-3">Orders</h5>

        <div class="row g-3 mb-3">
            @foreach([
                ['label' => 'NEW ORDERS', 'value' => $stats['new_orders'] ?? 0, 'icon' => 'ri-inbox-line'],
                ['label' => 'TOTAL ORDERS', 'value' => $stats['total_orders'] ?? 0, 'icon' => 'ri-file-list-3-line'],
                ['label' => 'PREPARING', 'value' => $stats['preparing'] ?? 0, 'icon' => 'ri-time-line'],
                ['label' => 'PICKED UP', 'value' => $stats['picked_up'] ?? 0, 'icon' => 'ri-truck-line'],
                ['label' => 'DELIVERED', 'value' => $stats['delivered'] ?? 0, 'icon' => 'ri-checkbox-circle-line'],
                ['label' => 'CANCELLED', 'value' => $stats['cancelled'] ?? 0, 'icon' => 'ri-close-circle-line'],
            ] as $card)
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="kpi-card kpi-mini">
                        <div class="kpi-icon-mini"><i class="{{ $card['icon'] }}"></i></div>
                        <h4>{{ $card['value'] }}</h4>
                        <small>{{ $card['label'] }}</small>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card dashboard-card">
            <div class="card-body">
                <h6 class="mb-3">Recent Orders</h6>
                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Order Info</th>
                            <th>Customer</th>
                            <th>Items & Price</th>
                            <th>Driver Info</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($orders as $order)
                            @php
                                $customer = $order->customer?->user;
                                $driver = $order->deliveryAssignment?->driver;
                                $status = \App\Models\Orders::statusLabel($order->order_status);
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $order->order_number }}</strong><br>
                                    <small class="text-muted">{{ optional($order->created_at)->format('Y-m-d H:i') }}</small><br>
                                    <small class="text-muted">{{ ucfirst((string) $order->payment_method) }}</small>
                                </td>
                                <td>
                                    {{ $customer->name ?? '—' }}<br>
                                    <small class="text-muted">{{ $customer->mobile ?? '-' }}</small>
                                </td>
                                <td>
                                    {{ $order->productSummary() }}<br>
                                    <strong>₹{{ number_format((float)$order->total_amount, 2) }}</strong>
                                </td>
                                <td>
                                    {{ $driver->name ?? 'Not assigned' }}<br>
                                    <small class="text-muted">{{ $driver->mobile ?? '-' }}</small>
                                </td>
                                <td><span class="badge badge-soft-info">{{ $status }}</span></td>
                                <td>
                                    <a href="{{ route('vendor.order-details', $order->order_id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No orders found.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $orders->withQueryString()->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
.kpi-mini{background:#fff;border:1px solid #eceef2;border-radius:10px;padding:10px 12px;min-height:84px}
.kpi-icon-mini{width:24px;height:24px;border-radius:999px;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;margin-bottom:6px}
.kpi-mini i{font-size:13px}
.kpi-mini h4{margin:0;font-size:24px;line-height:1.1}
.kpi-mini small{font-size:10px;color:#6b7280}
</style>
@endsection

