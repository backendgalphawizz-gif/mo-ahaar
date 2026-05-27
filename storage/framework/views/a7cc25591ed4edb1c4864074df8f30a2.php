

<?php $__env->startSection('content'); ?>
<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <div class="page-body-wrapper">
        <div class="page-body">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <style>
                            .tracking-shell {
                                background: #f4f6f9;
                                border-radius: 16px;
                                padding: 14px;
                            }
                            .tracking-title {
                                font-size: 24px;
                                font-weight: 700;
                                color: #1f2a37;
                            }
                            .status-row {
                                margin-bottom: 14px;
                            }
                            .track-step {
                                position: relative;
                                background: #dff0ef;
                                border-radius: 6px;
                                padding: 14px 18px;
                                font-weight: 600;
                                color: #334155;
                                display: flex;
                                align-items: center;
                                gap: 10px;
                                min-height: 68px;
                                font-size: 16px;
                            }
                            .track-step.inactive {
                                background: #e8eff0;
                                color: #7d8792;
                            }
                            .track-step.cancelled {
                                background: #f8e9ec;
                                color: #7d3a49;
                            }
                            .track-step:after {
                                content: '';
                                position: absolute;
                                right: -20px;
                                top: 0;
                                width: 0;
                                height: 0;
                                border-top: 34px solid transparent;
                                border-bottom: 34px solid transparent;
                                border-left: 18px solid #dff0ef;
                                z-index: 2;
                            }
                            .track-step.inactive:after {
                                border-left-color: #e8eff0;
                            }
                            .track-step.cancelled:after {
                                border-left-color: #f8e9ec;
                            }
                            .track-step:last-child:after {
                                display: none;
                            }
                            .track-step i {
                                font-size: 22px;
                            }
                            .order-card {
                                background: #fff;
                                border-radius: 12px;
                                box-shadow: 0 2px 8px rgba(16, 24, 40, 0.08);
                                padding: 16px;
                                border-left: 3px solid #c18f33 ;
                            }
                            .order-card h5,
                            .order-card h6 {
                                color: #1f2937;
                                font-weight: 700;
                            }
                            .summary-row {
                                display: flex;
                                justify-content: space-between;
                                padding: 5px 0;
                                color: #5e6a76;
                                font-size: 18px;
                            }
                            .summary-total {
                                border-top: 1px solid #e9ecef;
                                margin-top: 6px;
                                padding-top: 10px;
                                font-weight: 700;
                                color: #1d2733;
                            }
                            .track-table td,
                            .track-table th {
                                vertical-align: middle;
                                font-size: 16px;
                                padding-top: 12px;
                                padding-bottom: 12px;
                                color: #334155;
                            }
                            .track-table thead th {
                                font-weight: 700;
                                color: #475569;
                                border-bottom: 1px solid #e5e7eb;
                            }
                            .track-badge {
                                background: linear-gradient(135deg, #b8872b 0%, #c9973a 50%, #e0b45a 100%);
                                color: #fff;
                                border-radius: 6px;
                                padding: 4px 10px;
                                font-size: 12px;
                                font-weight: 600;
                                letter-spacing: 0.2px;
                                display: inline-block;
                            }
                            .detail-label {
                                color: #94a3b8;
                                font-size: 13px;
                                margin-bottom: 2px;
                            }
                            .detail-value {
                                color: #334155;
                                font-size: 16px;
                                margin-bottom: 10px;
                                line-height: 1.35;
                            }
                            
                        </style>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 tracking-title"><?php echo e($title ?? 'Order Tracking'); ?></h5>
                            <a href="<?php echo e(route('admin.orders')); ?>" class="btn btn-theme">Back to Orders</a>
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

                        <?php
                            $status = strtolower((string) ($order->order_status ?? 'pending'));
                            $isCancelled = in_array($status, ['rejected', 'cancelled'], true);
                            $isDelivered = in_array($status, ['delivered', 'completed', 'success'], true);
                            $stepPlaced = true;
                            $stepProcessing = in_array($status, ['processing', 'shipped', 'out_for_delivery', 'delivered', 'completed', 'success'], true) && !$isCancelled;
                            $stepShipped = in_array($status, ['shipped', 'out_for_delivery', 'delivered', 'completed', 'success'], true) && !$isCancelled;
                            $stepDelivered = $isDelivered && !$isCancelled;

                            $customerName = optional(optional($order->customer)->user)->name ?? 'N/A';
                            $customerEmail = optional(optional($order->customer)->user)->email ?? 'N/A';
                            $customerPhone = optional(optional($order->customer)->user)->mobile ?? 'N/A';
                            $customerAddress = optional($order->customer)->customer_address ?? ($order->shipping_address ?? 'N/A');
                        ?>

                        <div class="tracking-shell">
                            <div class="row g-2 status-row">
                                <div class="col-md-3">
                                    <div class="track-step <?php echo e($stepPlaced ? '' : 'inactive'); ?>">
                                        <i class="ri-shopping-bag-line"></i>
                                        <span>Placed</span>
                                    </div>
                                </div>
                               
                                <div class="col-md-3">
                                    <div class="track-step <?php echo e($stepProcessing ? '' : 'inactive'); ?>">
                                        <i class="ri-truck-line"></i>
                                        
                                        <span>Ready To Dispatch</span>
                                    </div>
                                </div>
                                 <div class="col-md-3">
                                    <div class="track-step <?php echo e($stepShipped ? '' : 'inactive'); ?>">
                                        <i class="ri-settings-3-line"></i>
                                        <span>Out For Delivery</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="track-step <?php echo e($stepDelivered ? '' : ($isCancelled ? 'cancelled' : 'inactive')); ?>">
                                        <i class="<?php echo e($isCancelled ? 'ri-close-circle-line' : 'ri-home-smile-line'); ?>"></i>
                                        <span><?php echo e($isCancelled ? 'Cancelled' : 'Delivered'); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-xl-9">
                                    <div class="order-card">
                                        <h5 class="mb-3">Order Number: #<?php echo e($order->order_number); ?></h5>
                                        <div class="table-responsive">
                                            <table class="table track-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Image</th>
                                                    <th>Name</th>
                                                    <th>Price</th>
                                                    <th>Quantity</th>
                                                    <th>Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $__empty_1 = true; $__currentLoopData = $order->orderItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                    <tr>
                                                        <td>
                                                            <img src="<?php echo e(asset('public/assets/images/product/1.png')); ?>" alt="product" style="height:44px;width:44px;object-fit:cover;border-radius:6px;">
                                                        </td>
                                                        <td><?php echo e($item->product_name ?? ('Item #' . $item->item_id)); ?></td>
                                                        <td>₹<?php echo e(number_format((float)$item->unit_price, 2)); ?></td>
                                                        <td><?php echo e((int)$item->quantity); ?></td>
                                                        <td>₹<?php echo e(number_format((float)$item->line_total, 2)); ?></td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                    <tr>
                                                        <td>
                                                            <img src="<?php echo e(asset('public/assets/images/product/1.png')); ?>" alt="product" style="height:44px;width:44px;object-fit:cover;border-radius:6px;">
                                                        </td>
                                                        <td>Order Item</td>
                                                        <td>₹<?php echo e(number_format((float)$order->subtotal, 2)); ?></td>
                                                        <td>1</td>
                                                        <td>₹<?php echo e(number_format((float)$order->subtotal, 2)); ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3">
                                    <div class="order-card mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h5 class="mb-0">Summary</h5>
                                            <a href="<?php echo e(route('admin.order-invoice-pdf', $order->order_id)); ?>" class="btn btn-sm btn-theme">Invoice <i class="ri-download-line"></i></a>
                                        </div>
                                        <div class="summary-row"><span>Subtotal</span><span>₹<?php echo e(number_format((float)$order->subtotal, 2)); ?></span></div>
                                        <?php if($order->shipping_amount > 0): ?>
                                            <div class="summary-row"><span>Shipping</span><span>₹<?php echo e(number_format((float)$order->shipping_amount, 2)); ?></span></div>
                                        <?php endif; ?>
                                        <div class="summary-row"><span>Tax</span><span>₹<?php echo e(number_format((float)$order->tax_amount, 2)); ?></span></div>
                                        <div class="summary-row summary-total"><span>Total</span><span>₹<?php echo e(number_format((float)$order->total_amount, 2)); ?></span></div>
                                    </div>

                                    <div class="order-card mb-3">
                                        <h5 class="mb-3">Consumer Details</h5>
                                        <p class="detail-label">Name:</p>
                                        <p class="detail-value"><?php echo e($customerName); ?></p>

                                        <p class="detail-label">Email Address:</p>
                                        <p class="detail-value"><?php echo e($customerEmail); ?></p>

                                        <p class="detail-label">Billing Address:</p>
                                        <p class="detail-value"><?php echo e($customerAddress); ?></p>

                                        <p class="detail-label">Phone:</p>
                                        <p class="detail-value"><?php echo e($customerPhone); ?></p>

                                        <p class="detail-label">Shipping Address:</p>
                                        <p class="detail-value">
                                            <?php
                                                $shipAddr = $order->shipping_address ?? null;
                                                $shipAddr = is_string($shipAddr) ? json_decode($shipAddr, true) : $shipAddr;
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
                                                <?php echo e($customerAddress); ?>

                                            <?php endif; ?>
                                        </p>

                                        <p class="detail-label">Payment Mode:</p>
                                        <p class="detail-value mb-0"><?php echo e(ucfirst($order->payment_method)); ?></p>
                                    </div>

                                    <?php if(in_array($order->order_status, ['pending', 'accepted', 'confirmed', 'payment_pending', 'processing', 'out_for_delivery', 'shipped'], true) && !in_array($order->order_status, ['delivered', 'cancelled', 'rejected'], true)): ?>
                                    <div class="order-card mb-3">
                                        <h5 class="mb-3">Ship &amp; deliver</h5>
                                        <form action="<?php echo e(route('admin.update-delivery-status', $order->order_id)); ?>" method="POST" class="mb-2">
                                            <?php echo csrf_field(); ?>
                                            <label for="status_select" class="form-label">Update status</label>
                                            <div class="input-group">
                                                <select name="status" id="status_select" class="form-control" required>
                                                    <option value="">-- Select --</option>
                                                    <?php if(!in_array($order->order_status, ['processing','shipped','out_for_delivery'], true)): ?>
                                                        <option value="processing">Ready to dispatch</option>
                                                        <option value="out_for_delivery">Out for delivery (legacy)</option>
                                                        
                                                    <?php else: ?>
                                                        <?php if(!in_array($order->order_status, ['shipped','out_for_delivery'], true)): ?>
                                                            <option value="out_for_delivery">Out for delivery</option>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    <option value="delivered">Delivered</option>
                                                </select>
                                                <button class="btn btn-primary" type="submit">Update</button>
                                            </div>
                                        </form>
                                        <small class="text-muted">Mark as shipped or delivered. You can also change status from the orders list.</small>
                                    </div>
                                    <?php endif; ?>

                                    <div class="order-card">
                                        <h6 class="mb-2">Tracking Timeline</h6>
                                        <?php $__empty_1 = true; $__currentLoopData = $order->trackings->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tracking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <div class="mb-2">
                                                <span class="track-badge"><?php echo e(ucfirst(str_replace('_', ' ',  App\Models\Orders::statusLabel($tracking->status)))); ?></span>
                                                <div class="small text-muted mt-1"><?php echo e(optional($tracking->tracked_at)->format('d M Y, h:i A') ?? '-'); ?></div>
                                                <div class="small"><?php echo e($tracking->location ?? 'N/A'); ?></div>
                                                <?php if($tracking->description): ?>
                                                <div class="small"><?php echo e($tracking->description); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <p class="text-muted mb-0">No tracking updates found.</p>
                                        <?php endif; ?>
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
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/developmentalpha/public_html/swastik-food-machinery.developmentalphawizz.com/resources/views/admin/orders/orderTracking.blade.php ENDPATH**/ ?>