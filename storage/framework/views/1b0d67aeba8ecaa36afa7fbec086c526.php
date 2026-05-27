<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Orders Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 11px; }
        h2 { font-size: 18px; font-weight: bold; margin: 0 0 4px 0; }
        .meta { color: #6b7280; font-size: 10px; margin-bottom: 14px; }
        .filters { background: #f3f4f6; padding: 6px 10px; border-radius: 4px; margin-bottom: 14px; font-size: 10px; color: #374151; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1f2937; color: #fff; padding: 7px 6px; text-align: left; font-size: 10px; }
        td { padding: 6px 6px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
        tr:nth-child(even) td { background: #f9fafb; }
        .badge { display: inline-block; padding: 2px 7px; border-radius: 99px; font-size: 9px; font-weight: bold; }
        .badge-pending, .badge-processing, .badge-out_for_delivery { background: #fff7ed; color: #9a3412; border: 1px solid #fdba74; }
        .badge-accepted, .badge-delivered, .badge-completed, .badge-success { background: #ecfdf5; color: #065f46; border: 1px solid #6ee7b7; }
        .badge-shipped, .badge-confirmed { background: #eff6ff; color: #1e40af; border: 1px solid #93c5fd; }
        .badge-rejected, .badge-cancelled { background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; }
        .text-right { text-align: right; }
        footer { margin-top: 18px; font-size: 9px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <?php
        $store = $storeSetting ?? $globalStoreSetting ?? null;
        $brandName = '';
        if ($store) {
            $brandName = trim((string) ($store->site_title ?? $store->app_name ?? ''));
        }
        if ($brandName === '') {
            $brandName = config('app.name', 'Store');
        }
    ?>

    <h2><?php echo e($brandName); ?> — Orders Export</h2>
    <div class="meta">Generated: <?php echo e(now()->format('d M Y, h:i A')); ?></div>

    <?php if($fromDate || $toDate || $search): ?>
        <div class="filters">
            <strong>Applied Filters:</strong>
            <?php if($search): ?> &nbsp; Search: <em><?php echo e($search); ?></em> <?php endif; ?>
            <?php if($fromDate): ?> &nbsp; From: <em><?php echo e(\Carbon\Carbon::parse($fromDate)->format('d M Y')); ?></em> <?php endif; ?>
            <?php if($toDate): ?> &nbsp; To: <em><?php echo e(\Carbon\Carbon::parse($toDate)->format('d M Y')); ?></em> <?php endif; ?>
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Order Date</th>
                <th>Payment Method</th>
                <th>Payment Status</th>
                <th>Order Status</th>
                <th class="text-right">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($index + 1); ?></td>
                    <td><?php echo e($order->order_number ?? 'N/A'); ?></td>
                    <td><?php echo e(optional(optional($order->customer)->user)->name ?? 'Customer N/A'); ?></td>
                    <td><?php echo e($order->created_at ? $order->created_at->format('d-m-Y') : ''); ?></td>
                    <td><?php echo e(ucfirst($order->payment_method ?? '')); ?></td>
                    <td><?php echo e(ucfirst($order->payment_status ?? '')); ?></td>
                    <td>
                        <?php $status = $order->order_status ?? ''; ?>
                        <span class="badge badge-<?php echo e($status); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $status))); ?></span>
                    </td>
                    <td class="text-right"><?php echo e(number_format((float)$order->total_amount, 2)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="8" style="text-align:center; padding: 16px; color: #6b7280;">No orders found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <footer>Total <?php echo e($orders->count()); ?> orders &mdash; <?php echo e($brandName); ?></footer>
</body>
</html>
<?php /**PATH /home/developmentalpha/public_html/swastik-food-machinery.developmentalphawizz.com/resources/views/admin/orders/ordersExportPdf.blade.php ENDPATH**/ ?>