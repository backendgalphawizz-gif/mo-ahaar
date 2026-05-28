@extends('layouts.app')

@section('content')
    @include('admin.partials.dashboard-ui')
    @php
        $dashboardKpis = [
            ['label' => 'Total Users', 'value' => $totalUsers ?? 0, 'icon' => 'ri-group-line', 'class' => 'kpi-users'],
            ['label' => 'Total Restaurants', 'value' => $totalVendors ?? 0, 'icon' => 'ri-store-2-line', 'class' => 'kpi-vendors'],
            ['label' => 'Total Delivery Boy', 'value' => $totalDeliveryBoys ?? 0, 'icon' => 'ri-truck-line', 'class' => 'kpi-delivery'],
            ['label' => 'Total Orders', 'value' => $totalOrders ?? 0, 'icon' => 'ri-shopping-bag-3-line', 'class' => 'kpi-orders'],
            ['label' => 'Cancelled Orders', 'value' => $cancelledOrders ?? 0, 'icon' => 'ri-close-circle-line', 'class' => 'kpi-cancelled'],
            ['label' => 'Completed Orders', 'value' => $completedOrders ?? 0, 'icon' => 'ri-checkbox-circle-line', 'class' => 'kpi-completed'],
            ['label' => 'Total Revenue', 'value' => '₹' . number_format((float) ($totalRevenue ?? 0), 0), 'icon' => 'ri-coins-line', 'class' => 'kpi-revenue', 'raw' => true],
            ['label' => 'Admin Earnings', 'value' => '₹' . number_format((float) ($adminEarnings ?? 0), 0), 'icon' => 'ri-wallet-3-line', 'class' => 'kpi-earnings', 'raw' => true],
        ];
    @endphp
    <div class="page-body">
        <div class="container-fluid">
            @include('admin.partials.figma-page-header', [
                'title' => 'Dashboard Overview',
                'subtitle' => 'Welcome back, Admin',
            ])

            <div class="row g-3 mb-4 figma-kpi-grid">
                @foreach($dashboardKpis as $kpi)
                    <div class="col-xxl-3 col-lg-4 col-sm-6">
                        <div class="kpi-card {{ $kpi['class'] }}">
                            <div class="kpi-icon"><i class="{{ $kpi['icon'] }}"></i></div>
                            <p>{{ $kpi['label'] }}</p>
                            <h3>
                                @if(!empty($kpi['raw']))
                                    {{ $kpi['value'] }}
                                @else
                                    {{ number_format((int) $kpi['value']) }}
                                @endif
                            </h3>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="card dashboard-card">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h6 class="figma-section-title mb-0">Today's Orders</h6>
                        <a href="{{ route('admin.orders') }}" class="btn btn-outline-secondary btn-sm">Filter</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-modern align-middle">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Restaurant</th>
                                    <th>Amount</th>
                                    <th>Payment</th>
                                    <th>Delivery Boy</th>
                                    <th>Status</th>
                                    <th>Date &amp; Time</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($todayOrders as $order)
                                    @php
                                        $customer = $order->customer?->user;
                                        $driver = $order->deliveryAssignment?->driver;
                                        $customerInitials = collect(explode(' ', (string) ($customer->name ?? 'NA')))->filter()->take(2)->map(fn ($p) => strtoupper(substr($p, 0, 1)))->implode('');
                                        $status = strtolower((string) ($order->order_status ?? 'pending'));
                                        $statusClass = match ($status) {
                                            'delivered', 'completed', 'success' => 'badge-soft-success',
                                            'cancelled', 'canceled', 'rejected' => 'badge-soft-danger',
                                            default => 'badge-soft-warning',
                                        };
                                    @endphp
                                    <tr>
                                        <td><a href="{{ route('admin.order-details', $order->order_id) }}" class="fw-semibold text-primary">{{ $order->order_number }}</a></td>
                                        <td>
                                            <div class="cell-with-avatar">
                                                <span class="user-avatar">{{ $customerInitials ?: 'NA' }}</span>
                                                <div>
                                                    <div class="fw-semibold">{{ $customer->name ?? '—' }}</div>
                                                    @if(!empty($customer?->mobile))<small class="text-muted">+91 {{ $customer->mobile }}</small>@endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>{{ $order->vendor?->business_name ?? '—' }}</div>
                                            @if(!empty($order->vendor?->mobile))<small class="text-muted">+91 {{ $order->vendor->mobile }}</small>@endif
                                        </td>
                                        <td>
                                            <div>₹{{ number_format((float) $order->total_amount, 0) }}</div>
                                            <small class="text-success">Comm: ₹{{ number_format($order->adminCommissionAmount(), 0) }}</small>
                                        </td>
                                        <td>{{ strtoupper($order->payment_method ?? '—') }}</td>
                                        <td>
                                            @if($driver)
                                                {{ $driver->name }}
                                            @else
                                                <span class="text-muted small">Assign Driver</span>
                                            @endif
                                        </td>
                                        <td><span class="badge {{ $statusClass }}">{{ \App\Models\Orders::statusLabel($order->order_status) }}</span></td>
                                        <td>
                                            <div>{{ optional($order->created_at)->format('d/m/Y') }}</div>
                                            <small class="text-muted">{{ optional($order->created_at)->format('h:i A') }}</small>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.order-details', $order->order_id) }}" class="btn btn-sm btn-outline-primary" title="View"><i class="ri-eye-line"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="text-center text-muted py-4">No orders for today.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(($todayOrders->count() ?? 0) > 0)
                        <div class="d-flex justify-content-between align-items-center mt-3 small text-muted">
                            <span>Showing today's latest {{ $todayOrders->count() }} orders</span>
                            <a href="{{ route('admin.orders') }}" class="text-primary text-decoration-none">View all orders</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<style>
.kpi-card { position: relative; border: 1px solid #e8ebf0; border-radius: 10px; background: #fff; padding: 14px 48px 12px 14px; min-height: 92px; }
.kpi-card p { margin: 0 0 4px; font-size: 11px; color: #6b7280; }
.kpi-card h3 { margin: 0; font-size: 28px; line-height: 1.1; color: #111827; font-weight: 700; }
.kpi-icon { position: absolute; right: 12px; top: 12px; width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; }
.kpi-users .kpi-icon, .kpi-earnings .kpi-icon { background: #fef3c7; color: #d97706; }
.kpi-vendors .kpi-icon { background: #ffedd5; color: #f97316; }
.kpi-delivery .kpi-icon { background: #ede9fe; color: #7c3aed; }
.kpi-orders .kpi-icon, .kpi-completed .kpi-icon { background: #dcfce7; color: #16a34a; }
.kpi-cancelled .kpi-icon { background: #fee2e2; color: #dc2626; }
.kpi-revenue .kpi-icon { background: #fef3c7; color: #eab308; }
</style>
@endsection
