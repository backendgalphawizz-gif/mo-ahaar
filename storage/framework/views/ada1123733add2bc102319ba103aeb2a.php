

<?php $__env->startSection('content'); ?>
<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <div class="page-body-wrapper">
        <div class="page-body">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h5 class="mb-0"><?php echo e($title ?? 'Order Details'); ?></h5>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?php echo e(route('admin.order-tracking', $order->order_id)); ?>" class="btn btn-outline-primary btn-sm">Tracking</a>
                        <a href="<?php echo e(route('admin.order-invoice-pdf', $order->order_id)); ?>" class="btn btn-outline-secondary btn-sm">Invoice PDF</a>
                        <a href="<?php echo e(route('admin.orders')); ?>" class="btn btn-outline-secondary btn-sm">Back to orders</a>
                    </div>
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

                <div class="row g-3">
                    <div class="col-lg-8">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="mb-3">Order #<?php echo e($order->order_number); ?></h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0">
                                        <tbody>
                                            <tr>
                                                <th style="width: 220px;">Order number</th>
                                                <td><?php echo e($order->order_number); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Order status</th>
                                                <td><?php echo e(\App\Models\Orders::statusLabel($order->order_status)); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Payment method</th>
                                                <td><?php echo e(ucfirst($order->payment_method)); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Payment status</th>
                                                <td><?php echo e(ucfirst($order->payment_status)); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Shipping address</th>
                                                <td>
                                                    <?php
                                                $rawShippingAddress = $order->shipping_address ?? null;
                                                $shipAddr = is_string($rawShippingAddress) ? json_decode($rawShippingAddress, true) : $rawShippingAddress;
                                                $fallbackShippingAddress = is_string($rawShippingAddress) && trim($rawShippingAddress) !== ''
                                                    ? $rawShippingAddress
                                                    : 'N/A';
                                            ?>
                                            <?php if(is_array($shipAddr)): ?>
                                                <?php if(!empty($shipAddr['contact_name'])): ?><?php echo e($shipAddr['contact_name']); ?><br><?php endif; ?>
                                                <?php if(!empty($shipAddr['mobile'])): ?><?php echo e($shipAddr['mobile']); ?><br><?php endif; ?>
                                                <?php if(!empty($shipAddr['address_line'])): ?><?php echo e($shipAddr['address_line']); ?><?php endif; ?>
                                                <?php if(!empty($shipAddr['landmark'])): ?>, <?php echo e($shipAddr['landmark']); ?><?php endif; ?>
                                                <?php if(!empty($shipAddr['city'])): ?>, <?php echo e($shipAddr['city']); ?><?php endif; ?>
                                                <?php if(!empty($shipAddr['state'])): ?>, <?php echo e($shipAddr['state']); ?><?php endif; ?>
                                                <?php if(!empty($shipAddr['pincode'])): ?> - <?php echo e($shipAddr['pincode']); ?><?php endif; ?>
                                                <?php if(!empty($shipAddr['country'])): ?><br><?php echo e($shipAddr['country']); ?><?php endif; ?>
                                                <?php if(!empty($shipAddr['address_type'])): ?><br><span class="text-muted" style="font-size:12px;"><?php echo e(ucfirst($shipAddr['address_type'])); ?></span><?php endif; ?>
                                            <?php else: ?>
                                                <?php echo e($fallbackShippingAddress); ?>

                                            <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Notes</th>
                                                <td><?php echo e($order->notes ?: 'N/A'); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Created at</th>
                                                <td><?php echo e(optional($order->created_at)->format('d M Y, h:i A')); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Line items</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-modern mb-0">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>SKU</th>
                                                <th class="text-end">Price</th>
                                                <th class="text-end">Qty</th>
                                                <th class="text-end">Line total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__empty_1 = true; $__currentLoopData = $order->orderItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <tr>
                                                    <td><?php echo e($item->product_name ?? ('Item #' . $item->item_id)); ?></td>
                                                    <td><?php echo e($item->sku ?: '—'); ?></td>
                                                    <td class="text-end">₹<?php echo e(number_format((float) $item->unit_price, 2)); ?></td>
                                                    <td class="text-end"><?php echo e((int) $item->quantity); ?></td>
                                                    <td class="text-end">₹<?php echo e(number_format((float) $item->line_total, 2)); ?></td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">No line items for this order.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card mb-3 border">
                            <div class="card-body">
                                <h6 class="mb-3">Update order status</h6>
                                
                                <p class="small text-muted mb-2">Set fulfillment to Ready to Dispatch, Out for Delivery, Delivered, or Cancelled. Legacy statuses stay until you pick a new value.</p>
                                <form method="POST" action="<?php echo e(route('admin.update-order-status', $order->order_id)); ?>" class="d-flex flex-column gap-2">
                                    <?php echo csrf_field(); ?>
                                    <?php echo $__env->make('admin.orders.partials.order-status-quick-select', [
                                        'order' => $order,
                                        'selectClass' => 'form-select',
                                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    <button type="submit" class="btn btn-theme btn-sm">Save status</button>
                                </form>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="mb-3">Customer</h6>
                                <p class="mb-1"><strong>Name:</strong> <?php echo e(optional(optional($order->customer)->user)->name ?? 'N/A'); ?></p>
                                <p class="mb-1"><strong>Email:</strong> <?php echo e(optional(optional($order->customer)->user)->email ?? 'N/A'); ?></p>
                                <p class="mb-0"><strong>Customer ID:</strong> <?php echo e($order->customer_id ?? 'N/A'); ?></p>
                            </div>
                        </div>

                        <?php if(!empty($order->vendor_id)): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="mb-3">Vendor</h6>
                                    <p class="mb-1"><strong>Business:</strong> <?php echo e(optional($order->vendor)->business_name ?? 'N/A'); ?></p>
                                    <p class="mb-1"><strong>Owner:</strong> <?php echo e(optional($order->vendor)->owner_name ?? 'N/A'); ?></p>
                                    <p class="mb-1"><strong>Email:</strong> <?php echo e(optional($order->vendor)->email ?? 'N/A'); ?></p>
                                    <p class="mb-0"><strong>Vendor ID:</strong> <?php echo e($order->vendor_id); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="card">
                            <div class="card-body">
                                <h6 class="mb-3">Amount summary</h6>
                                <p class="mb-1"><strong>Subtotal:</strong> ₹<?php echo e(number_format((float) $order->subtotal, 2)); ?></p>
                                <p class="mb-1"><strong>Tax:</strong> ₹<?php echo e(number_format((float) $order->tax_amount, 2)); ?></p>
                                <p class="mb-1"><strong>Shipping:</strong> ₹<?php echo e(number_format((float) $order->shipping_amount, 2)); ?></p>
                                <p class="mb-0"><strong>Total:</strong> ₹<?php echo e(number_format((float) $order->total_amount, 2)); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/developmentalpha/public_html/swastik-food-machinery.developmentalphawizz.com/resources/views/admin/orders/orderDetails.blade.php ENDPATH**/ ?>