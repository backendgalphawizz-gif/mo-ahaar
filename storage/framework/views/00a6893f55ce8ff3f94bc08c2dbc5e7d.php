<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice <?php echo e($order->order_number); ?></title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; }
        .header { display: table; width: 100%; margin-bottom: 18px; }
        .header .left, .header .right { display: table-cell; width: 50%; vertical-align: top; }
        .brand { margin-bottom: 10px; }
        .brand-logo { max-width: 130px; max-height: 60px; margin-bottom: 8px; }
        .brand-name { font-size: 16px; font-weight: bold; }
        .title { font-size: 22px; font-weight: bold; margin-bottom: 4px; }
        .muted { color: #6b7280; }
        .card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; }
        th { background: #f3f4f6; text-align: left; }
        .text-right { text-align: right; }
        .summary td { border: none; padding: 4px 0; }
        .total { font-weight: bold; font-size: 14px; border-top: 1px solid #d1d5db; padding-top: 8px; }
    </style>
</head>
<body>
    <?php
        $store = $storeSetting ?? $globalStoreSetting ?? null;
        $siteName = trim((string) ($store->site_title ?? ''));
        $appName = trim((string) ($store->app_name ?? ''));
        $brandName = $siteName !== '' ? $siteName : ($appName !== '' ? $appName : config('app.name', 'Store'));
        $supportNumber = trim((string) ($store->support_number ?? ''));
        $supportEmail = trim((string) ($store->support_email ?? ''));
        $supportAddress = trim((string) ($store->address ?? ''));
        $logoName = trim((string) ($store->logo ?? ''));
        $logoPath = $logoName !== '' ? public_path('uploads/settings/' . $logoName) : '';

        $customerName = optional(optional($order->customer)->user)->name ?? 'N/A';
        $customerEmail = optional(optional($order->customer)->user)->email ?? 'N/A';
        $customerPhone = optional(optional($order->customer)->user)->mobile ?? 'N/A';
        $customerAddress = optional($order->customer)->customer_address ?? ($order->shipping_address ?? 'N/A');
    ?>

    <div class="header">
        <div class="left">
            <div class="brand">
                <?php if($logoPath !== '' && file_exists($logoPath)): ?>
                    <img src="<?php echo e($logoPath); ?>" alt="<?php echo e($brandName); ?> logo" class="brand-logo">
                <?php endif; ?>
                <div class="brand-name"><?php echo e($brandName); ?></div>
                <?php if($supportNumber !== ''): ?><div class="muted">Phone: <?php echo e($supportNumber); ?></div><?php endif; ?>
                <?php if($supportEmail !== ''): ?><div class="muted">Email: <?php echo e($supportEmail); ?></div><?php endif; ?>
                <?php if($supportAddress !== ''): ?><div class="muted">Address: <?php echo e($supportAddress); ?></div><?php endif; ?>
            </div>
            <div class="title">Invoice</div>
            <div class="muted">Order Number: #<?php echo e($order->order_number); ?></div>
            <div class="muted">Date: <?php echo e(\Carbon\Carbon::parse($order->created_at)->format('d M Y')); ?></div>
        </div>
        <div class="right text-right">
            <div><strong>Payment:</strong> <?php echo e(ucfirst($order->payment_method)); ?></div>
            <div><strong>Payment Status:</strong> <?php echo e(ucfirst($order->payment_status)); ?></div>
            <div><strong>Order Status:</strong> <?php echo e(ucfirst(str_replace('_', ' ', $order->order_status))); ?></div>
        </div>
    </div>

    <div class="card">
        <strong>Customer Details</strong>
        <div style="margin-top:6px;">
            <div><?php echo e($customerName); ?></div>
            <div><?php echo e($customerEmail); ?></div>
            <div><?php echo e($customerPhone); ?></div>
            <div><?php echo e($customerAddress); ?></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 42%;">Item</th>
                <th style="width: 14%;" class="text-right">Price</th>
                <th style="width: 14%;" class="text-right">Qty</th>
                <th style="width: 14%;" class="text-right">Tax</th>
                <th style="width: 16%;" class="text-right">Line Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $order->orderItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($item->product_name ?? ('Item #' . $item->item_id)); ?></td>
                    <td class="text-right">₹<?php echo e(number_format((float)$item->unit_price, 2)); ?></td>
                    <td class="text-right"><?php echo e((int)$item->quantity); ?></td>
                    <td class="text-right">₹<?php echo e(number_format((float)$item->tax_amount, 2)); ?></td>
                    <td class="text-right">₹<?php echo e(number_format((float)$item->line_total, 2)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td>Order Item</td>
                    <td class="text-right">₹<?php echo e(number_format((float)$order->subtotal, 2)); ?></td>
                    <td class="text-right">1</td>
                    <td class="text-right">₹<?php echo e(number_format((float)$order->tax_amount, 2)); ?></td>
                    <td class="text-right">₹<?php echo e(number_format((float)$order->total_amount, 2)); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div style="margin-top: 14px; width: 320px; margin-left: auto;">
        <table class="summary">
            <tr>
                <td>Subtotal</td>
                <td class="text-right">₹<?php echo e(number_format((float)$order->subtotal, 2)); ?></td>
            </tr>
            <?php if($order->shipping_amount > 0): ?>
                <tr>
                    <td>Shipping</td>
                    <td class="text-right">₹<?php echo e(number_format((float)$order->shipping_amount, 2)); ?></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td>Tax</td>
                <td class="text-right">₹<?php echo e(number_format((float)$order->tax_amount, 2)); ?></td>
            </tr>
            <tr>
                <td class="total">Total</td>
                <td class="text-right total">₹<?php echo e(number_format((float)$order->total_amount, 2)); ?></td>
            </tr>
        </table>
    </div>
</body>
</html>

<?php /**PATH /home/developmentalpha/public_html/swastik-food-machinery.developmentalphawizz.com/resources/views/admin/orders/orderInvoicePdf.blade.php ENDPATH**/ ?>