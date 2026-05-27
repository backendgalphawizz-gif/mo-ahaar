<?php
    $selectClass = $selectClass ?? 'form-select form-select-sm order-status-select status-pill-select';
    $adminStatuses = \App\Models\Orders::adminPrimaryFulfillmentStatuses();
    $current = (string) ($order->order_status ?? '');
    $isPrimary = array_key_exists($current, $adminStatuses);
?>
<select
    name="order_status"
    class="<?php echo e($selectClass); ?>"
    data-order-id="<?php echo e($order->order_id); ?>"
    data-order-number="<?php echo e($order->order_number); ?>"
    data-current-status="<?php echo e($order->order_status); ?>"
    aria-label="Change order status"
>
    <?php if (! ($isPrimary)): ?>
        <option value="<?php echo e(e($current)); ?>" selected><?php echo e(\App\Models\Orders::statusLabel($current)); ?> (current)</option>
    <?php endif; ?>
    <?php $__currentLoopData = $adminStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($value); ?>" <?php if($isPrimary && $current === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<?php /**PATH /home/developmentalpha/public_html/swastik-food-machinery.developmentalphawizz.com/resources/views/admin/orders/partials/order-status-quick-select.blade.php ENDPATH**/ ?>