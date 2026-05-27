<?php $__env->startSection('content'); ?>
    <?php
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
    ?>
    <div class="page-body">
        <div class="container-fluid">

            <div class="sw-hero-card">
                <div class="sw-hero-content">
                    <p class="sw-welcome">Welcome back, Admin 👋</p>
                    <h2>Here’s what’s happening today!</h2>
                    <p class="sw-subtitle">Live overview of machinery performance, production metrics, and order activity
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
                        <h3><?php echo e(number_format((int) ($totalUsers ?? 0))); ?></h3>
                    </div>
                </div>
                <!-- Vendor KPI card removed -->
                <div class="col-xxl-3 col-lg-4 col-sm-6">
                    <div class="kpi-card kpi-products">
                        <div class="kpi-icon"><i class="ri-xbox-line"></i></div>
                        <p>Total Products</p>
                        <h3><?php echo e(number_format((int) ($totalProducts ?? 0))); ?></h3>
                    </div>
                </div>
                <div class="col-xxl-3 col-lg-4 col-sm-6">
                    <div class="kpi-card kpi-orders">
                        <div class="kpi-icon"><i class="ri-shopping-bag-3-line"></i></div>
                        <p>Total Orders</p>
                        <h3><?php echo e(number_format((int) ($totalOrders ?? 0))); ?></h3>
                    </div>
                </div>
                <!-- Removed Total Recharge Transactions and Total Venue Bookings KPI cards -->
                <div class="col-xxl-3 col-lg-4 col-sm-6">
                    <div class="kpi-card kpi-revenue">
                        <div class="kpi-icon"><i class="ri-coins-line"></i></div>
                        <p>Sales Summary</p>
                        <h3>₹<?php echo e(number_format((float) ($totalRevenue ?? 0), 2)); ?></h3>
                    </div>
                </div>
                <!-- Active Vendors KPI card removed -->
                <!-- Removed Pending Approvals KPI card -->
            </div>

            <!-- Orders & Revenue Trend and Platform Distribution removed -->

            <div class="row g-4">
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
                                    <?php $__empty_1 = true; $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <?php
                                            $status = strtolower((string) ($order->order_status ?? 'pending'));
                                            $statusClass = match ($status) {
                                                'delivered', 'completed', 'success' => 'badge-soft-success',
                                                'cancelled', 'canceled', 'failed' => 'badge-soft-danger',
                                                default => 'badge-soft-warning',
                                            };
                                        ?>
                                        <tr>
                                            <td><?php echo e($loop->iteration); ?></td>
                                            <td><?php echo e($order->order_number ?? ('#' . $order->order_id)); ?></td>
                                            <td><?php echo e(optional($order->created_at)->format('d M Y') ?: '-'); ?></td>
                                            <td>₹<?php echo e(number_format((float) ($order->total_amount ?? 0), 2)); ?></td>
                                            <td><span class="badge <?php echo e($statusClass); ?>"><?php echo e(ucfirst($status)); ?></span></td>
                                            <td>
                                                <?php if($dashboardLinks['orderDetails']): ?>
                                                    <a href="<?php echo e(route('admin.order-details', $order->order_id)); ?>"
                                                        class="btn btn-sm btn-outline-primary">View</a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">No recent orders available
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <!-- Best Selling Products section removed -->
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <style>
        .hero-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #b8872b 0%, #c9973a 50%, #e0b45a 100%);
            border: 1px solid rgba(212, 168, 87, 0.3);
            border-radius: 4px;
            padding: 8px 14px;
            font-weight: 600;
        }

        .kpi-card {
            position: relative;
            min-height: 128px;
            padding: 28px 24px 22px 92px;
            border-radius: 14px;
            overflow: hidden;
            background: #fff;
            border: 1px solid #eee2cf;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.06);
        }

        .kpi-card::after {
            content: "";
            position: absolute;
            right: 18px;
            bottom: 12px;
            width: 72px;
            height: 72px;
            font-family: remixicon !important;
            font-size: 72px;
            line-height: 1;
            color: rgba(201, 151, 58, 0.11);
        }

        /* background icon */
        .kpi-users::after {
            content: "\ede3";
        }

        /* ri-group-line */
        .kpi-products::after {
            content: "\eca1";
        }

        /* ri-box-3-line */
        .kpi-orders::after {
            content: "\f11f";
        }

        /* ri-shopping-bag-3-line */
        .kpi-revenue::after {
            content: "\ef65";
        }

        /* ri-coins-line */

        /* card backgrounds */
        .kpi-users,
        .kpi-orders {
            background: linear-gradient(135deg, #ffffff 0%, #fffaf1 55%, #fff4df 100%);
        }

        .kpi-products {
            background: linear-gradient(135deg, #ffffff 0%, #fbfbfb 100%);
            border-color: #eeeeee;
        }

        .kpi-revenue {
            background: linear-gradient(135deg, #070707 0%, #111111 55%, #1a1a1a 100%);
            color: #fff;
            border-color: rgba(201, 151, 58, 0.45);
        }

        .kpi-revenue::after {
            color: rgba(201, 151, 58, 0.18);
        }

        /* icon circle */
        .kpi-icon {
            position: absolute;
            left: 24px;
            top: 28px;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 23px;
            color: #fff;
            background: linear-gradient(135deg, #e0b45a 0%, #c9973a 45%, #9f650d 100%);
            box-shadow: 0 8px 18px rgba(184, 135, 43, 0.32);
            z-index: 2;
        }

        .kpi-products .kpi-icon {
            background: linear-gradient(135deg, #050505 0%, #151515 55%, #252525 100%);
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.22);
        }

        .kpi-card p {
            margin: 0 0 8px;
            font-size: 14px;
            font-weight: 500;
            color: #4f5665;
        }

        .kpi-card h3 {
            margin: 0;
            font-size: 28px;
            line-height: 1.1;
            font-weight: 700;
            color: #111827;
        }

        .kpi-revenue p,
        .kpi-revenue h3 {
            color: #fff;
        }

        /* small bottom text like image */
        .kpi-card h3::after {
            display: block;
            margin-top: 14px;
            font-size: 12px;
            font-weight: 500;
            color: #6b7280;
        }

        .kpi-users h3::after {
            content: "↑ 12% from last month";
            color: #12a85a;
        }

        .kpi-products h3::after {
            content: "No change";
            color: #6b7280;
        }

        .kpi-orders h3::after {
            content: "↑ 8% from last month";
            color: #12a85a;
        }

        .kpi-revenue h3::after {
            content: "↑ 15% from last month";
            color: #12a85a;
        }

        @media (max-width: 575px) {
            .kpi-card {
                padding: 24px 20px 22px 86px;
            }

            .kpi-card h3 {
                font-size: 24px;
            }
        }

        .dashboard-card {
            border: 1px solid #edf2f7;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, .06);
        }

        .dashboard-card .card-header {
            background: #fff;
        }

        .dashboard-card .card-header h5 {
            color: #1e293b !important;
            font-weight: 700;
        }

        .dashboard-card .card-header p,
        .dashboard-card .card-header small {
            color: #64748b !important;
        }

        .dashboard-card .apexcharts-legend-text,
        .dashboard-card .apexcharts-text,
        .dashboard-card .apexcharts-datalabel-label,
        .dashboard-card .apexcharts-datalabel-value,
        .dashboard-card .apexcharts-xaxis-label,
        .dashboard-card .apexcharts-yaxis-label {
            fill: #334155 !important;
            color: #334155 !important;
        }

        .dashboard-card .apexcharts-legend-series span {
            color: #334155 !important;
        }

        .chart-panel {
            position: relative;
            width: 100%;
        }

        .chart-panel-lg {
            height: 340px;
            min-height: 340px;
        }

        .chart-panel-sm {
            height: 280px;
            min-height: 280px;
        }

        .chart-wrap {
            width: 100%;
            max-width: 280px;
        }

        .distribution-empty-state {
            max-width: 300px;
            color: #334155;
        }

        .distribution-empty-state i {
            display: inline-flex;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            font-size: 20px;
            color: #0f766e;
            background: #ecfeff;
            border: 1px solid #ccfbf1;
        }

        .distribution-empty-state p {
            color: #1e293b;
            font-weight: 600;
        }

        .chart-wrap canvas,
        .chart-panel canvas {
            width: 100% !important;
            height: 100% !important;
        }

        /* 
                .table-modern thead th {
                    background: #f7fafc;
                    border-bottom: 1px solid #e5edf5;
                    color: #4b5563;
                    font-weight: 600;
                    font-size: 13px;
                }

                .table-modern td {
                    border-color: #eef2f6;
                    vertical-align: middle;
                    color: #1e293b !important;
                } */

        /* .dashboard-card .table-modern tbody td,
                .dashboard-card .table-modern tbody td span,
                .dashboard-card .table-modern tbody td small,
                .dashboard-card .table-modern tbody td a:not(.btn) {
                    color: #1e293b !important;
                }

                .dashboard-card .table-modern tbody td .text-muted {
                    color: #64748b !important;
                } */

        .dashboard-card .table-modern tbody td span.badge-soft-success {
            background: #e0fae3;
            color: #3fb96b !important;
            padding: 8px 12px;
            border: 1px solid #7bffab;
        }

        .badge-soft-danger {
            background: #fdecec;
            color: #a61e2f;
        }

        .dashboard-card .table-modern tbody td span.badge-soft-warning {
            background: #fff1c1c7;
            color: #e3951d !important;
            padding: 8px 12px;
            border: 1px solid #ffd89b;
        }

        .sw-hero-card {
            width: 100%;
            min-height: 122px;
            padding: 24px 28px;
            margin-bottom: 25px;
            border-radius: 14px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #fff;
            background:
                radial-gradient(circle at 78% 45%, rgba(184, 135, 43, 0.26) 0%, rgba(184, 135, 43, 0.08) 22%, transparent 45%),
                linear-gradient(135deg, #070707 0%, #111111 48%, #171717 100%);
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.12);
        }

        .sw-hero-card::after {
            content: "";
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(201, 151, 58, 0.7) 1px, transparent 1.8px);
            background-size: 13px 13px;
            opacity: 0.42;
            transform: perspective(420px) rotateX(58deg) rotateZ(-6deg) translate(210px, 12px) scale(1.2);
            transform-origin: center;
            mask-image: radial-gradient(ellipse at 63% 55%, #000 0%, #000 34%, transparent 67%);
            -webkit-mask-image: radial-gradient(ellipse at 63% 55%, #000 0%, #000 34%, transparent 67%);
        }

        .sw-hero-content,
        .sw-live-btn {
            position: relative;
            z-index: 2;
        }

        .sw-welcome {
            margin: 0 0 6px;
            font-size: 15px;
            font-weight: 500;
            color: #f4f4f4;
        }

        .sw-hero-content h2 {
            margin: 0 0 8px;
            font-size: 26px;
            line-height: 1.15;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .sw-subtitle {
            margin: 0;
            font-size: 14px;
            color: #f1f1f1;
        }

        .sw-live-btn {
            border: 0;
            border-radius: 8px;
            padding: 12px 22px;
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            background: linear-gradient(135deg, #b8872b 0%, #c9973a 45%, #e0b45a 100%);
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.25),
                0 8px 18px rgba(184, 135, 43, 0.35);
            cursor: pointer;
        }

        .sw-live-btn span {
            margin-right: 7px;
            font-size: 18px;
        }

        .sw-section-heading h3 {
            margin: 0;
            color: #111827;
            font-size: 24px;
            font-weight: 500;
            line-height: 1.2;
            padding: 10px 0;
            font-family: "Inter", sans-serif !important;
        }

        .sw-section-heading span {
            display: block;
            width: 55px;
            height: 3px;
            border-radius: 20px;
            background: linear-gradient(135deg, #b8872b 0%, #c9973a 50%, #e0b45a 100%);
            position: relative;
        }

        .sw-section-heading span::after {
            content: "";
            position: absolute;
            right: -9px;
            top: 50%;
            width: 5px;
            height: 5px;
            transform: translateY(-50%);
            border-radius: 50%;
            background: #c9973a;
        }

        @media (max-width: 768px) {
            .sw-hero-card {
                padding: 20px;
                min-height: 150px;
                align-items: flex-start;
                flex-direction: column;
                gap: 16px;
            }

            .sw-hero-content h2 {
                font-size: 22px;
            }

            .sw-live-btn {
                padding: 10px 18px;
            }
        }

        @media (max-width: 767px) {
            .dashboard-hero {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .chart-panel-lg {
                height: 280px;
                min-height: 280px;
            }

            .chart-panel-sm {
                height: 240px;
                min-height: 240px;
            }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof ApexCharts === 'undefined') {
                return;
            }

            var labels = <?php echo json_encode($ordersTrendLabels ?? [], 15, 512) ?>;
            var ordersData = <?php echo json_encode($ordersTrendData ?? [], 15, 512) ?>;
            var revenueData = <?php echo json_encode($revenueTrendData ?? [], 15, 512) ?>;
            var distributionData = <?php echo json_encode(array_values(($kpiDistribution ?? [])), 15, 512) ?>;

            labels = Array.isArray(labels) ? labels : [];
            ordersData = Array.isArray(ordersData) ? ordersData.map(function (v) { return Number(v || 0); }) : [];
            revenueData = Array.isArray(revenueData) ? revenueData.map(function (v) { return Number(v || 0); }) : [];
            distributionData = Array.isArray(distributionData) ? distributionData.map(function (v) { return Number(v || 0); }) : [];

            var trendNode = document.getElementById('ordersRevenueChart');
            if (trendNode) {
                var trendOptions = {
                    chart: {
                        type: 'line',
                        height: '100%',
                        toolbar: { show: false },
                        zoom: { enabled: false }
                    },
                    series: [
                        { name: 'Orders', data: ordersData },
                        { name: 'Revenue (₹)', data: revenueData }
                    ],
                    stroke: {
                        width: [3, 3],
                        curve: 'smooth'
                    },
                    colors: ['#0da487', '#2f80ed'],
                    xaxis: {
                        categories: labels
                    },
                    yaxis: [
                        {
                            title: { text: 'Orders' },
                            labels: {
                                formatter: function (val) { return Math.round(val); }
                            }
                        },
                        {
                            opposite: true,
                            title: { text: 'Revenue (₹)' },
                            labels: {
                                formatter: function (val) { return Math.round(val); }
                            }
                        }
                    ],
                    dataLabels: { enabled: false },
                    grid: {
                        borderColor: 'rgba(148,163,184,.16)'
                    },
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        shared: true,
                        intersect: false
                    }
                };

                new ApexCharts(trendNode, trendOptions).render();
            }

            var donutNode = document.getElementById('kpiDonutChart');
            if (donutNode && distributionData.some(function (v) { return v > 0; })) {
                var donutOptions = {
                    chart: {
                        type: 'donut',
                        height: '100%'
                    },
                    series: distributionData,
                    labels: ['Customers', 'Orders', 'Recharge', 'Venue'],
                    colors: ['#2f80ed', '#f08a24', '#556cd6', '#b46ed6'],
                    legend: {
                        position: 'bottom'
                    },
                    dataLabels: {
                        enabled: true
                    },
                    stroke: {
                        colors: ['#fff']
                    },
                    responsive: [{
                        breakpoint: 768,
                        options: {
                            legend: { position: 'bottom' }
                        }
                    }]
                };

                new ApexCharts(donutNode, donutOptions).render();
            }
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/developmentalpha/public_html/mo-aahar.developmentalphawizz.com/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>