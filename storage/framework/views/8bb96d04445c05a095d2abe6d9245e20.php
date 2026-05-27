<?php $__env->startSection('content'); ?>
<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <div class="page-body-wrapper">
        <div class="page-body">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-sm-8 m-auto">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="card-header-2">
                                            <h5><?php echo e($title ?? 'Edit Order'); ?></h5>
                                        </div>

                                        <?php if(session('success')): ?>
                                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <?php echo e(session('success')); ?>

                                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                            </div>
                                        <?php endif; ?>

                                        <?php if(session('error')): ?>
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <?php echo e(session('error')); ?>

                                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                            </div>
                                        <?php endif; ?>

                                        <form action="<?php echo e(route('admin.update-order', $order->order_id)); ?>" method="POST" id="orderEditForm">
                                            <?php echo csrf_field(); ?>
                                            <div class="theme-form theme-form-2 mega-form">
                                                <div class="mb-4 row align-items-center">
                                                    <label class="form-label-title col-sm-3 mb-0">Customer</label>
                                                    <div class="col-sm-9">
                                                        <select class="form-select" id="customer_id" name="customer_id">
                                                            <option value="">Select Customer</option>
                                                            <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <option value="<?php echo e($customer->customer_id); ?>" <?php echo e(old('customer_id', $order->customer_id) == $customer->customer_id ? 'selected' : ''); ?>>
                                                                    <?php echo e($customer->name); ?> (<?php echo e($customer->email); ?>)
                                                                </option>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </select>
                                                        <p class="errors text-danger mb-0" id="err_customer_id"><?php $__errorArgs = ['customer_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <?php echo e($message); ?> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></p>
                                                    </div>
                                                </div>

                                                <!-- Vendor field removed: No vendor logic in system -->

                                                <div class="mb-4 row align-items-center">
                                                    <label class="form-label-title col-sm-3 mb-0">Order Number</label>
                                                    <div class="col-sm-9">
                                                        <input class="form-control" id="order_number" type="text" value="<?php echo e($order->order_number); ?>" readonly>
                                                        <small class="text-muted">Auto generated vendor-wise. It will refresh only if vendor is changed.</small>
                                                    </div>
                                                </div>

                                                <div class="mb-4 row align-items-center">
                                                    <label class="form-label-title col-sm-3 mb-0">Payment Method</label>
                                                    <div class="col-sm-9">
                                                        <select class="form-select" id="payment_method" name="payment_method">
                                                            <option value="">Select Payment Method</option>
                                                            <?php $__empty_1 = true; $__currentLoopData = ($activePaymentMethods ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                                <option value="<?php echo e($method->gateway); ?>" <?php echo e(old('payment_method', $order->payment_method) == $method->gateway ? 'selected' : ''); ?>>
                                                                    <?php echo e($method->display_name); ?>

                                                                </option>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                                <option value="" disabled>No active payment methods found</option>
                                                            <?php endif; ?>
                                                        </select>
                                                        <p class="errors text-danger mb-0" id="err_payment_method"><?php $__errorArgs = ['payment_method'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <?php echo e($message); ?> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></p>
                                                    </div>
                                                </div>

                                                <div class="mb-4 row align-items-center">
                                                    <label class="form-label-title col-sm-3 mb-0">Payment Status</label>
                                                    <div class="col-sm-9">
                                                        <select class="form-select" id="payment_status" name="payment_status">
                                                            <option value="pending" <?php echo e(old('payment_status', $order->payment_status) == 'pending' ? 'selected' : ''); ?>>Pending</option>
                                                            <option value="paid" <?php echo e(old('payment_status', $order->payment_status) == 'paid' ? 'selected' : ''); ?>>Paid</option>
                                                            <option value="failed" <?php echo e(old('payment_status', $order->payment_status) == 'failed' ? 'selected' : ''); ?>>Failed</option>
                                                            <option value="refunded" <?php echo e(old('payment_status', $order->payment_status) == 'refunded' ? 'selected' : ''); ?>>Refunded</option>
                                                        </select>
                                                        <p class="errors text-danger mb-0" id="err_payment_status"><?php $__errorArgs = ['payment_status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <?php echo e($message); ?> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></p>
                                                    </div>
                                                </div>

                                                <div class="mb-4 row align-items-center">
                                                    <label class="form-label-title col-sm-3 mb-0">Order Status</label>
                                                    <div class="col-sm-9">
                                                        <?php
                                                            $os = (string) old('order_status', $order->order_status);
                                                            $formStatuses = [
                                                                'pending' => 'Pending',
                                                                'payment_pending' => 'Payment pending',
                                                                'confirmed' => 'Confirmed',
                                                                'accepted' => 'Accepted',
                                                                'processing' => 'Processing',
                                                                'shipped' => 'Shipped',
                                                                'out_for_delivery' => 'Out for delivery (legacy)',
                                                                'delivered' => 'Delivered',
                                                                'cancelled' => 'Cancelled',
                                                                'rejected' => 'Rejected',
                                                            ];
                                                        ?>
                                                        <select class="form-select" id="order_status" name="order_status">
                                                            <?php if(!array_key_exists($os, $formStatuses)): ?>
                                                                <option value="<?php echo e($os); ?>" selected><?php echo e(\App\Models\Orders::statusLabel($os)); ?> (current)</option>
                                                            <?php endif; ?>
                                                            <?php $__currentLoopData = $formStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <option value="<?php echo e($val); ?>" <?php if($os === $val): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </select>
                                                        <p class="errors text-danger mb-0" id="err_order_status"><?php $__errorArgs = ['order_status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <?php echo e($message); ?> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></p>
                                                    </div>
                                                </div>

                                                <div class="mb-4 row align-items-center">
                                                    <label class="form-label-title col-sm-3 mb-0">Subtotal</label>
                                                    <div class="col-sm-9">
                                                        <input class="form-control amount-input" id="subtotal" type="number" step="0.01" min="0" name="subtotal" value="<?php echo e(old('subtotal', $order->subtotal)); ?>">
                                                        <p class="errors text-danger mb-0" id="err_subtotal"><?php $__errorArgs = ['subtotal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <?php echo e($message); ?> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></p>
                                                    </div>
                                                </div>

                                                <div class="mb-4 row align-items-center">
                                                    <label class="form-label-title col-sm-3 mb-0">Tax Amount</label>
                                                    <div class="col-sm-9">
                                                        <input class="form-control amount-input" id="tax_amount" type="number" step="0.01" min="0" name="tax_amount" value="<?php echo e(old('tax_amount', $order->tax_amount)); ?>">
                                                        <p class="errors text-danger mb-0" id="err_tax_amount"><?php $__errorArgs = ['tax_amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <?php echo e($message); ?> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></p>
                                                    </div>
                                                </div>

                                                <div class="mb-4 row align-items-center">
                                                    <label class="form-label-title col-sm-3 mb-0">Shipping Amount</label>
                                                    <div class="col-sm-9">
                                                        <input class="form-control amount-input" id="shipping_amount" type="number" step="0.01" min="0" name="shipping_amount" value="<?php echo e(old('shipping_amount', $order->shipping_amount)); ?>">
                                                        <p class="errors text-danger mb-0" id="err_shipping_amount"><?php $__errorArgs = ['shipping_amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <?php echo e($message); ?> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></p>
                                                    </div>
                                                </div>

                                                <div class="mb-4 row align-items-center">
                                                    <label class="form-label-title col-sm-3 mb-0">Total Amount</label>
                                                    <div class="col-sm-9">
                                                        <input class="form-control" id="total_amount" type="number" step="0.01" min="0" name="total_amount" value="<?php echo e(old('total_amount', $order->total_amount)); ?>" readonly>
                                                        <p class="errors text-danger mb-0" id="err_total_amount"><?php $__errorArgs = ['total_amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <?php echo e($message); ?> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></p>
                                                    </div>
                                                </div>

                                                <div class="mb-4 row align-items-center">
                                                    <label class="form-label-title col-sm-3 mb-0">Shipping Address</label>
                                                    <div class="col-sm-9">
                                                        <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" placeholder="Shipping Address"><?php echo e(old('shipping_address', $order->shipping_address)); ?></textarea>
                                                        <p class="errors text-danger mb-0" id="err_shipping_address"><?php $__errorArgs = ['shipping_address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <?php echo e($message); ?> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></p>
                                                    </div>
                                                </div>

                                                <div class="mb-4 row align-items-center">
                                                    <label class="form-label-title col-sm-3 mb-0">Notes</label>
                                                    <div class="col-sm-9">
                                                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Order Notes"><?php echo e(old('notes', $order->notes)); ?></textarea>
                                                        <p class="errors text-danger mb-0" id="err_notes"><?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <?php echo e($message); ?> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></p>
                                                    </div>
                                                </div>

                                                <div class="mb-4 row align-items-center mt-5">
                                                    <label class="form-label-title col-sm-3 mb-0"></label>
                                                    <div class="col-sm-9 d-flex gap-2">
                                                        <button type="button" class="btn btn-solid" onclick="updateOrder()">Update Order</button>
                                                        <button type="button" onclick="history.back()" class="btn btn-outline-secondary">Back</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    function setError(field, message) {
        var el = document.getElementById('err_' + field);
        if (el) {
            el.textContent = message;
        }
    }

    function clearErrors() {
        [
            'customer_id', 'vendor_id', 'payment_method', 'payment_status',
            'order_status', 'subtotal', 'tax_amount', 'shipping_amount',
            'total_amount', 'shipping_address', 'notes'
        ].forEach(function(field) {
            setError(field, '');
        });
    }

    function calculateTotal() {
        var subtotal = parseFloat(document.getElementById('subtotal').value || 0);
        var tax = parseFloat(document.getElementById('tax_amount').value || 0);
        var shipping = parseFloat(document.getElementById('shipping_amount').value || 0);

        var total = subtotal + tax + shipping;
        document.getElementById('total_amount').value = total.toFixed(2);
    }

    function updateOrder() {
        clearErrors();

        var isValid = true;
        var customerId = document.getElementById('customer_id').value.trim();
        var vendorId = document.getElementById('vendor_id').value.trim();
        var paymentMethod = document.getElementById('payment_method').value.trim();
        var paymentStatus = document.getElementById('payment_status').value.trim();
        var orderStatus = document.getElementById('order_status').value.trim();
        var totalAmount = parseFloat(document.getElementById('total_amount').value || 0);

        if (customerId === '') {
            setError('customer_id', 'Please select a customer.');
            isValid = false;
        }

        if (vendorId === '') {
            setError('vendor_id', 'Please select a vendor.');
            isValid = false;
        }

        if (paymentMethod === '') {
            setError('payment_method', 'Payment method is required.');
            isValid = false;
        }

        if (paymentStatus === '') {
            setError('payment_status', 'Payment status is required.');
            isValid = false;
        }

        if (orderStatus === '') {
            setError('order_status', 'Order status is required.');
            isValid = false;
        }

        if (isNaN(totalAmount) || totalAmount < 0) {
            setError('total_amount', 'Total amount must be 0 or greater.');
            isValid = false;
        }

        if (!isValid) {
            return;
        }

        document.getElementById('orderEditForm').submit();
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.amount-input').forEach(function(el) {
            el.addEventListener('input', calculateTotal);
        });

        calculateTotal();
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/developmentalpha/public_html/swastik-food-machinery.developmentalphawizz.com/resources/views/admin/orders/editOrder.blade.php ENDPATH**/ ?>