@extends('layouts.app')

@section('content')
    @include('admin.partials.dashboard-ui')
    <div class="page-body">
        <div class="container-fluid">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                <h5 class="mb-0">Vendor Dashboard</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('vendor.products') }}" class="btn btn-outline-secondary btn-sm">Manage Products</a>
                    <a href="{{ route('vendor.orders') }}" class="btn btn-danger btn-sm">View Orders</a>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-lg-4 col-sm-6">
                    <div class="kpi-card">
                        <p>Total Products</p>
                        <h3>{{ number_format((int) ($totalProducts ?? 0)) }}</h3>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-6">
                    <div class="kpi-card">
                        <p>Total Orders</p>
                        <h3>{{ number_format((int) ($totalOrders ?? 0)) }}</h3>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-6">
                    <div class="kpi-card">
                        <p>Total Revenue</p>
                        <h3>₹{{ number_format((float) ($totalRevenue ?? 0), 0) }}</h3>
                    </div>
                </div>
            </div>

            <div class="card dashboard-card mb-4">
                <div class="card-body">
                    <div class="sw-section-heading mb-3">
                        <h3>Sales Overview</h3>
                    </div>
                    <div class="chart-panel chart-panel-lg">
                        <div id="vendorSalesOverviewChart"></div>
                    </div>
                </div>
            </div>

            <div class="card dashboard-card">
                <div class="card-body">
                    <div class="sw-section-heading mb-3">
                        <h3>Recent Orders</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-modern align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
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
                                            'cancelled', 'canceled', 'failed', 'rejected' => 'badge-soft-danger',
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
                                            <a href="{{ route('vendor.order-details', $order->order_id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No recent orders available</td>
                                    </tr>
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
.kpi-card { border: 1px solid #e8ebf0; border-radius: 10px; background: #fff; padding: 14px; min-height: 92px; }
.kpi-card p { margin: 0 0 4px; font-size: 11px; color: #6b7280; }
.kpi-card h3 { margin: 0; font-size: 32px; line-height: 1; color: #111827; font-weight: 700; }
.sw-section-heading h3 { margin: 0; font-size: 14px; font-weight: 600; }
.chart-panel-lg { height: 320px; min-height: 320px; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof ApexCharts === 'undefined') {
        return;
    }

    var salesLabels = @json($salesChartLabels ?? []);
    var salesData = @json($salesChartData ?? []);
    var salesNode = document.getElementById('vendorSalesOverviewChart');

    if (!salesNode) {
        return;
    }

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
});
</script>
@endsection
