<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Customers Export</title>
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
        .badge-active { background: #ecfdf5; color: #065f46; border: 1px solid #6ee7b7; }
        .badge-inactive { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; }
        .badge-approved { background: #ecfdf5; color: #065f46; border: 1px solid #6ee7b7; }
        .badge-pending { background: #fff7ed; color: #9a3412; border: 1px solid #fdba74; }
        .badge-rejected { background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; }
        footer { margin-top: 18px; font-size: 9px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <?php
        $store = $storeSetting ?? null;
        $brandName = '';
        if ($store) {
            $brandName = trim((string) ($store->site_title ?? $store->app_name ?? ''));
        }
        if ($brandName === '') {
            $brandName = config('app.name', 'Store');
        }
    ?>

    <h2><?php echo e($brandName); ?> — Customers Export</h2>
    <div class="meta">Generated: <?php echo e(now()->format('d M Y, h:i A')); ?></div>

    <?php if($search): ?>
        <div class="filters">
            <strong>Applied Filters:</strong>
            &nbsp; Search: <em><?php echo e($search); ?></em>
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>User Type</th>
                <th>Account Status</th>
                <?php if($hasApproval): ?>
                <th>Approval</th>
                <?php endif; ?>
                <th>Registered On</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($index + 1); ?></td>
                    <td><?php echo e($customer->name ?? 'N/A'); ?></td>
                    <td><?php echo e($customer->email ?? ''); ?></td>
                    <td><?php echo e($customer->mobile ?? '-'); ?></td>
                    <td><?php echo e($customer->user_type ?? '-'); ?></td>
                    <td>
                        <?php if((int)($customer->status ?? 0) === 1): ?>
                            <span class="badge badge-active">Active</span>
                        <?php else: ?>
                            <span class="badge badge-inactive">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <?php if($hasApproval): ?>
                    <td>
                        <?php $ap = strtolower((string)($customer->approval_status ?? 'approved')); ?>
                        <span class="badge badge-<?php echo e($ap); ?>"><?php echo e(ucfirst($ap)); ?></span>
                    </td>
                    <?php endif; ?>
                    <td>
                        <?php if(!empty($customer->registration_date)): ?>
                            <?php echo e(\Carbon\Carbon::parse($customer->registration_date)->format('d M Y')); ?>

                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="<?php echo e($hasApproval ? 8 : 7); ?>" style="text-align:center; padding:16px; color:#6b7280;">No customers found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <footer>Total <?php echo e($customers->count()); ?> customers &mdash; <?php echo e($brandName); ?></footer>
</body>
</html>
<?php /**PATH /home/developmentalpha/public_html/swastik-food-machinery.developmentalphawizz.com/resources/views/admin/customers/customersExportPdf.blade.php ENDPATH**/ ?>