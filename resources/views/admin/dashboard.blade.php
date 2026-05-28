@extends('layouts.app')

@section('content')
    @include('admin.partials.dashboard-ui')
    @php
        $dashboardLinks = [
            'customers' => \Illuminate\Support\Facades\Route::has('admin.customers') ? route('admin.customers') : null,
            'orders' => \Illuminate\Support\Facades\Route::has('admin.orders') ? route('admin.orders') : null,
            'revenue' => \Illuminate\Support\Facades\Route::has('admin.reports.revenue') ? route('admin.reports.revenue') : null,
            'orderDetails' => \Illuminate\Support\Facades\Route::has('admin.order-details'),
            'productView' => \Illuminate\Support\Facades\Route::has('admin.view-product'),
        ];

        $distributionValues = array_map('intval', (array) ($kpiDistribution ?? []));
        $hasDistributionData = count(array_filter($distributionValues, function ($value) {
            return $value > 0;
        })) > 0;
    @endphp
    <div class="page-body">
        <div class="container-fluid">

            <div class="sw-hero-card d-none">
                <div class="sw-hero-content">
                    <p class="sw-welcome">Welcome back, Admin 👋</p>
                    <h2>Here’s what’s happening today!</h2>
                    <p class="sw-subtitle">Live overview of users, vendors, orders, and revenue activity
                    </p>
                </div>

                <div class="hero-pill">
                    <i class="ri-pulse-line"></i>
                    <span>Live Snapshot</span>
                </div>
            </div>

            <!-- <div class="dashboard-hero mb-4">
                                                    <div>
                                                        <h4 class="mb-1">Dashboard</h4>
                                                        <p class="sw-welcome">Welcome back, Admin 👋</p>
                                                        <p class="mb-0">Real-time platform health, approvals, and revenue pulse</p>
                                                    </div>
                                                    <div class="hero-pill">
                                                        <i class="ri-pulse-line"></i>
                                                        <span>Live Snapshot</span>
                                                    </div>
                                                </div> -->

            <div class="row g-3 mb-4">
                <div class="col-xxl-3 col-lg-4 col-sm-6">
                    <div class="kpi-card kpi-users">
                        <div class="kpi-icon"><i class="ri-group-line"></i></div>
                        <p>Total Users</p>
                        <h3>{{ number_format((int) ($totalUsers ?? 0)) }}</h3>
                    </div>
                </div>
                <div class="col-xxl-3 col-lg-4 col-sm-6">
                    <div class="kpi-card kpi-vendors">
                        <div class="kpi-icon"><i class="ri-store-2-line"></i></div>
                        <p>Total Vendors</p>
                        <h3>{{ number_format((int) ($totalVendors ?? 0)) }}</h3>
                    </div>
                </div>
                <div class="col-xxl-3 col-lg-4 col-sm-6">
                    <div class="kpi-card kpi-orders">
                        <div class="kpi-icon"><i class="ri-shopping-bag-3-line"></i></div>
                        <p>Total Orders</p>
                        <h3>{{ number_format((int) ($totalOrders ?? 0)) }}</h3>
                    </div>
                </div>
                <div class="col-xxl-3 col-lg-4 col-sm-6">
                    <div class="kpi-card kpi-revenue">
                        <div class="kpi-icon"><i class="ri-coins-line"></i></div>
                        <p>Revenue</p>
                        <h3>₹{{ number_format((float) ($totalRevenue ?? 0), 0) }}</h3>
                    </div>
                </div>
                <div class="col-xxl-3 col-lg-4 col-sm-6">
                    <div class="kpi-card kpi-active-vendors">
                        <div class="kpi-icon"><i class="ri-store-line"></i></div>
                        <p>Active Vendors</p>
                        <h3>{{ number_format((int) ($activeVendors ?? 0)) }}</h3>
                    </div>
                </div>
                <div class="col-xxl-3 col-lg-4 col-sm-6">
                    <div class="kpi-card kpi-active-users">
                        <div class="kpi-icon"><i class="ri-user-follow-line"></i></div>
                        <p>Active Users</p>
                        <h3>{{ number_format((int) ($activeUsers ?? 0)) }}</h3>
                    </div>
                </div>
                <div class="col-xxl-3 col-lg-4 col-sm-6">
                    <div class="kpi-card kpi-pending">
                        <div class="kpi-icon"><i class="ri-time-line"></i></div>
                        <p>Pending Approvals</p>
                        <h3>{{ number_format((int) ($pendingApprovals ?? 0)) }}</h3>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="card dashboard-card h-100">
                        <div class="sw-section-heading mb-3">
                            <h3>Sales Overview</h3>
                            <span></span>
                        </div>
                        <div class="chart-panel chart-panel-lg">
                            <div id="salesOverviewChart"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 d-none">
                <div class="col-12">
                    <div class="card dashboard-card h-100">

                        <div class="sw-section-heading mb-3">
                            <h3>Recent 5 Orders</h3>
                            <span></span>
                        </div>

                        <div class="table-responsive sw-table-wrapper">
                            <table class="table table-modern align-middle">
                                <thead>
                                    <tr>
                                        <th>S.No.</th>
                                        <th>Order</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentOrders as $order)
                                        @php
                                            $status = strtolower((string) ($order->order_status ?? 'pending'));
                                            $statusClass = match ($status) {
                                                'delivered', 'completed', 'success' => 'badge-soft-success',
                                                'cancelled', 'canceled', 'failed' => 'badge-soft-danger',
                                                default => 'badge-soft-warning',
                                            };
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $order->order_number ?? ('#' . $order->order_id) }}</td>
                                            <td>{{ optional($order->created_at)->format('d M Y') ?: '-' }}</td>
                                            <td>₹{{ number_format((float) ($order->total_amount ?? 0), 2) }}</td>
                                            <td><span class="badge {{ $statusClass }}">{{ ucfirst($status) }}</span></td>
                                            <td>
                                                @if($dashboardLinks['orderDetails'])
                                                    <a href="{{ route('admin.order-details', $order->order_id) }}"
                                                        class="btn btn-sm btn-outline-primary">View</a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">No recent orders available
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <!-- Best Selling Products section removed -->
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<style>
.kpi-card { position: relative; border: 1px solid #e8ebf0; border-radius: 10px; background: #fff; padding: 14px 48px 12px 14px; min-height: 92px; }
.kpi-card p { margin: 0 0 4px; font-size: 11px; color: #6b7280; }
.kpi-card h3 { margin: 0; font-size: 32px; line-height: 1; color: #111827; font-weight: 700; }
.kpi-icon { position: absolute; right: 12px; top: 12px; width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; }
.kpi-users .kpi-icon, .kpi-active-users .kpi-icon { background: #fef3c7; color: #d97706; }
.kpi-vendors .kpi-icon, .kpi-pending .kpi-icon { background: #ffedd5; color: #f97316; }
.kpi-orders .kpi-icon { background: #dcfce7; color: #16a34a; }
.kpi-revenue .kpi-icon { background: #fef3c7; color: #eab308; }
.kpi-active-vendors .kpi-icon { background: #dcfce7; color: #16a34a; }
.sw-section-heading h3 { margin: 0; font-size: 14px; font-weight: 600; }
.sw-section-heading span { display: none; }
.chart-panel-lg { height: 320px; min-height: 320px; }
</style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof ApexCharts === 'undefined') {
                return;
            }

            var salesLabels = @json($salesChartLabels ?? []);
            var salesData = @json($salesChartData ?? []);

            var salesNode = document.getElementById('salesOverviewChart');
            if (salesNode) {
                new ApexCharts(salesNode, {
                    chart: { type: 'bar', height: '100%', toolbar: { show: false } },
                    series: [{ name: 'Sales', data: salesData }],
                    colors: ['#22c55e'],
                    xaxis: { categories: salesLabels },
                    yaxis: {
                        labels: {
                            formatter: function (val) { return Math.round(val); }
                        }
                    },
                    dataLabels: { enabled: false },
                    plotOptions: { bar: { borderRadius: 2, columnWidth: '40%' } },
                    grid: { borderColor: 'rgba(148,163,184,.16)' },
                    legend: { position: 'bottom' },
                    tooltip: {
                        y: {
                            formatter: function (val) { return Number(val || 0).toLocaleString('en-IN'); }
                        }
                    }
                }).render();
            }
        });
    </script>
@endsection