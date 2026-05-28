@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
@php
    $isVendorPanel = (int) (session('role_type') ?? 0) === 3;
    $driversList = collect($availableDrivers ?? []);
@endphp
<div class="page-body">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
            <h5 class="mb-0">Order Management</h5>
        </div>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        @php
            $kpis = [
                'new' => ['label' => 'New', 'icon' => 'ri-inbox-line', 'class' => 'kpi-new'],
                'accepted' => ['label' => 'Accepted', 'icon' => 'ri-checkbox-circle-line', 'class' => 'kpi-accepted'],
                'rejected' => ['label' => 'Rejected', 'icon' => 'ri-close-circle-line', 'class' => 'kpi-rejected'],
                'picked_up' => ['label' => 'Picked Up', 'icon' => 'ri-user-received-line', 'class' => 'kpi-picked'],
                'out_for_delivery' => ['label' => 'Out For Delivery', 'icon' => 'ri-truck-line', 'class' => 'kpi-delivery'],
                'delivered' => ['label' => 'Delivered', 'icon' => 'ri-checkbox-multiple-line', 'class' => 'kpi-delivered'],
                'cancelled' => ['label' => 'Cancelled', 'icon' => 'ri-forbid-line', 'class' => 'kpi-cancelled'],
            ];
            $activeFilter = request('status_filter');
        @endphp

        <div class="row g-3 mb-4">
            @foreach($kpis as $key => $kpi)
                <div class="col-xxl col-lg-3 col-md-4 col-6">
                    <a href="{{ route($isVendorPanel ? 'vendor.orders' : 'admin.orders', array_filter(['status_filter' => $key, 'search' => $search ?? null])) }}"
                       class="order-kpi-card {{ $activeFilter === $key ? 'active' : '' }} {{ $kpi['class'] }}">
                        <span class="kpi-icon"><i class="{{ $kpi['icon'] }}"></i></span>
                        <p class="kpi-count mb-0">{{ number_format($statusCounts[$key] ?? 0) }}</p>
                        <p class="kpi-label">{{ $kpi['label'] }}</p>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="card dashboard-card">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <form method="GET" action="{{ route($isVendorPanel ? 'vendor.orders' : 'admin.orders') }}" class="d-flex flex-wrap gap-2 flex-grow-1">
                        @if($activeFilter)<input type="hidden" name="status_filter" value="{{ $activeFilter }}">@endif
                        <div class="input-group" style="max-width:280px;">
                            <span class="input-group-text"><i class="ri-search-line"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search orders..." value="{{ $search ?? request('search') }}">
                        </div>
                        <button type="submit" class="btn btn-outline-secondary btn-sm">Search</button>
                        @if(request()->hasAny(['search', 'status_filter']))
                            <a href="{{ route($isVendorPanel ? 'vendor.orders' : 'admin.orders') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                        @endif
                    </form>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">Filter</button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route($isVendorPanel ? 'vendor.orders' : 'admin.orders') }}">All Orders</a></li>
                            @foreach($kpis as $key => $kpi)
                                <li><a class="dropdown-item" href="{{ route($isVendorPanel ? 'vendor.orders' : 'admin.orders', ['status_filter' => $key]) }}">{{ $kpi['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Product Info</th>
                                <th>Customer Info</th>
                                <th>Vendor Info</th>
                                <th>Amount & Comm.</th>
                                <th>Payment</th>
                                <th>Delivery Boy</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allOrders as $order)
                                @php
                                    $customer = $order->customer?->user;
                                    $driver = $order->deliveryAssignment?->driver;
                                    $commission = $order->adminCommissionAmount();
                                @endphp
                                <tr>
                                    <td><a href="{{ route($isVendorPanel ? 'vendor.order-details' : 'admin.order-details', $order->order_id) }}" class="fw-semibold text-primary">{{ $order->order_number }}</a></td>
                                    <td><small>{{ $order->productSummary() }}</small></td>
                                    <td>
                                        <div>{{ $customer->name ?? '—' }}</div>
                                        @if(!empty($customer?->mobile))<small class="text-muted">(+91 {{ $customer->mobile }})</small>@endif
                                    </td>
                                    <td>
                                        <div>{{ $order->vendor?->business_name ?? '—' }}</div>
                                        @if($order->vendor_id)<small class="text-muted">(V-{{ $order->vendor_id }})</small>@endif
                                    </td>
                                    <td>
                                        <div>Total: ₹{{ number_format((float) $order->total_amount, 0) }}</div>
                                        <small class="text-success">Comm: ₹{{ number_format($commission, 1) }}</small>
                                    </td>
                                    <td>{{ strtoupper($order->payment_method ?? '—') }}</td>
                                    <td>
                                        @if($driver)
                                            <span class="fw-medium">{{ $driver->name }}</span>
                                        @elseif($isVendorPanel)
                                            <span class="text-muted">Not Assigned</span>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-warning assign-driver-btn"
                                                data-bs-toggle="modal" data-bs-target="#assignDriverModal"
                                                data-order-id="{{ $order->order_id }}"
                                                data-order-number="{{ $order->order_number }}">Assign Driver</button>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusBadge = match (strtolower((string) $order->order_status)) {
                                                'delivered', 'completed', 'success' => 'badge-soft-success',
                                                'cancelled', 'rejected' => 'badge-soft-danger',
                                                'out_for_delivery', 'shipped' => 'badge-soft-info',
                                                default => 'badge-soft-warning',
                                            };
                                        @endphp
                                        <span class="badge {{ $statusBadge }}">{{ \App\Models\Orders::statusLabel($order->order_status) }}</span>
                                    </td>
                                    <td>{{ optional($order->created_at)->format('d-m-Y') }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route($isVendorPanel ? 'vendor.order-details' : 'admin.order-details', $order->order_id) }}" class="btn btn-sm btn-outline-primary" title="View"><i class="ri-eye-line"></i></a>
                                            @if(!$isVendorPanel)
                                            <a href="{{ route('admin.order-invoice-pdf', $order->order_id) }}" class="btn btn-sm btn-outline-secondary" title="Invoice"><i class="ri-download-line"></i></a>
                                            @endif
                                        </div>
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
<div class="modal fade" id="assignDriverModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="assignDriverForm" action="">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Assign Delivery Boy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Order: <strong id="assignOrderLabel">—</strong></p>
                    <label class="form-label">Select Driver</label>
                    <select name="driver_id" class="form-select" required>
                        <option value="">Choose driver...</option>
                        @foreach($driversList as $d)
                            <option value="{{ $d->user_id }}">{{ $d->name }} (+91 {{ $d->mobile }})</option>
                        @endforeach
                    </select>
                    @if($driversList->isEmpty())
                        <small class="text-danger d-block mt-2">No approved drivers available. Add drivers from Delivery Management.</small>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.assign-driver-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var orderId = btn.getAttribute('data-order-id');
            var orderNumber = btn.getAttribute('data-order-number');
            document.getElementById('assignDriverForm').action = '{{ url('admin/orders') }}/' + orderId + '/assign-driver';
            document.getElementById('assignOrderLabel').textContent = orderNumber;
        });
    });

});
</script>
@endsection
