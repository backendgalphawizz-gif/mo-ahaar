@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
@php
    $isVendorPanel = (int) (session('role_type') ?? 0) === 3;
    $driversList = collect($availableDrivers ?? []);
    $ordersRoute = $isVendorPanel ? 'vendor.orders' : 'admin.orders';
    $activeFilter = request('status_filter');
    $figmaKpis = [
        'total' => ['label' => 'Total Orders', 'filter' => null],
        'accepted' => ['label' => 'Accepted Orders', 'filter' => 'accepted'],
        'picked_up' => ['label' => 'Picked Up Orders', 'filter' => 'picked_up'],
        'out_for_delivery' => ['label' => 'Out for Delivery', 'filter' => 'out_for_delivery'],
        'delivered' => ['label' => 'Delivered Orders', 'filter' => 'delivered'],
        'rejected' => ['label' => 'Rejected Orders', 'filter' => 'rejected'],
    ];
    $figmaTabs = [
        ['label' => 'All', 'filter' => null],
        ['label' => 'Pending', 'filter' => 'new'],
        ['label' => 'Accepted', 'filter' => 'accepted'],
        ['label' => 'Picked Up', 'filter' => 'picked_up'],
        ['label' => 'Out for Delivery', 'filter' => 'out_for_delivery'],
        ['label' => 'Delivered', 'filter' => 'delivered'],
        ['label' => 'Rejected', 'filter' => 'rejected'],
    ];
@endphp
<div class="page-body">
    <div class="container-fluid">
        @include('admin.partials.figma-page-header', [
            'title' => 'Order Management',
            'subtitle' => 'Manage and track all customer orders',
        ])

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        <div class="row g-3 mb-3 figma-kpi-grid">
            @foreach($figmaKpis as $key => $kpi)
                <div class="col-xxl-2 col-lg-4 col-md-6">
                    <a href="{{ route($ordersRoute, array_filter(['status_filter' => $kpi['filter'], 'search' => $search ?? null])) }}"
                       class="kpi-card text-decoration-none {{ $activeFilter === $kpi['filter'] || ($key === 'total' && empty($activeFilter)) ? 'border-dark' : '' }}">
                        <p>{{ $kpi['label'] }}</p>
                        <h3>{{ number_format($statusCounts[$key] ?? 0) }}</h3>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="card dashboard-card">
            <div class="card-body">
                <div class="figma-line-tabs">
                    @foreach($figmaTabs as $tab)
                        <a href="{{ route($ordersRoute, array_filter(['status_filter' => $tab['filter'], 'search' => $search ?? null])) }}"
                           class="tab-link {{ ($activeFilter === $tab['filter']) || (empty($activeFilter) && empty($tab['filter'])) ? 'active' : '' }}">
                            {{ $tab['label'] }}
                        </a>
                    @endforeach
                </div>

                <form method="GET" action="{{ route($ordersRoute) }}" class="figma-toolbar">
                    @if($activeFilter)<input type="hidden" name="status_filter" value="{{ $activeFilter }}">@endif
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Filter Date</button>
                    <span class="toolbar-spacer"></span>
                    <input type="text" name="search" class="form-control form-control-sm" style="max-width:220px;" placeholder="Search orders..." value="{{ $search ?? request('search') }}">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Search</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">Export</button>
                </form>

                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer Info</th>
                                <th>Restaurant Detail</th>
                                <th>Total Amount</th>
                                <th>Admin Commission</th>
                                <th>Payment Method</th>
                                <th>Delivery Boy</th>
                                <th>Status</th>
                                <th>Date &amp; Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allOrders as $order)
                                @php
                                    $customer = $order->customer?->user;
                                    $driver = $order->deliveryAssignment?->driver;
                                    $commission = $order->adminCommissionAmount();
                                    $customerInitials = collect(explode(' ', (string) ($customer->name ?? 'NA')))->filter()->take(2)->map(fn ($p) => strtoupper(substr($p, 0, 1)))->implode('');
                                    $statusBadge = match (strtolower((string) $order->order_status)) {
                                        'delivered', 'completed', 'success' => 'badge-soft-success',
                                        'cancelled', 'rejected' => 'badge-soft-danger',
                                        'out_for_delivery', 'shipped' => 'badge-soft-info',
                                        default => 'badge-soft-warning',
                                    };
                                @endphp
                                <tr>
                                    <td><a href="{{ route($isVendorPanel ? 'vendor.order-details' : 'admin.order-details', $order->order_id) }}" class="fw-semibold text-primary">{{ $order->order_number }}</a></td>
                                    <td>
                                        <div class="cell-with-avatar">
                                            <span class="user-avatar">{{ $customerInitials ?: 'NA' }}</span>
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
                                    <td class="text-success">₹{{ number_format($commission, 0) }}</td>
                                    <td>{{ strtoupper($order->payment_method ?? '—') }}</td>
                                    <td>
                                        @if($driver)
                                            <span class="fw-medium">{{ $driver->name }}</span>
                                        @elseif($isVendorPanel)
                                            <span class="text-muted small">Not Assigned</span>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-warning assign-driver-btn"
                                                data-bs-toggle="modal" data-bs-target="#assignDriverModal"
                                                data-order-id="{{ $order->order_id }}"
                                                data-order-number="{{ $order->order_number }}">Assign Driver</button>
                                        @endif
                                    </td>
                                    <td><span class="badge {{ $statusBadge }}">{{ \App\Models\Orders::statusLabel($order->order_status) }}</span></td>
                                    <td>
                                        <div>{{ optional($order->created_at)->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ optional($order->created_at)->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route($isVendorPanel ? 'vendor.order-details' : 'admin.order-details', $order->order_id) }}" class="btn btn-sm btn-outline-primary" title="View"><i class="ri-eye-line"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-center py-4 text-muted">No orders found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($allOrders->hasPages())
                    <div class="mt-3">{{ $allOrders->withQueryString()->links('pagination::bootstrap-5') }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

@if(!$isVendorPanel)
@include('admin.orders.partials.assign-driver-modal', ['driversList' => $driversList])
@endif
@endsection

@section('scripts')
<style>
.kpi-card { display: block; border: 1px solid #e8ebf0; border-radius: 10px; background: #fff; padding: 12px; color: inherit; }
.kpi-card p { margin: 0 0 4px; font-size: 10px; color: #6b7280; }
.kpi-card h3 { margin: 0; font-size: 22px; font-weight: 700; color: #111827; }
</style>
@if(!$isVendorPanel)
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.assign-driver-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var orderId = this.getAttribute('data-order-id');
            var orderNumber = this.getAttribute('data-order-number');
            var form = document.getElementById('assignDriverForm');
            var label = document.getElementById('assignDriverOrderLabel');
            if (form) form.action = '{{ url('/admin/orders') }}/' + orderId + '/assign-driver';
            if (label) label.textContent = orderNumber || ('#' + orderId);
        });
    });
});
</script>
@endif
@endsection
