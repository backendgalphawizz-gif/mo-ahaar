

<?php $__env->startSection('content'); ?>
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
        <div class="page-body-wrapper">
            <div class="page-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card card-table">
                                <div class="card-body">
                                    <div
                                        class="title-header option-title d-flex align-items-center justify-content-between">
                                        <div>
                                            <h5 class="mb-0"><?php echo e($title ?? 'Order List'); ?></h5>

                                        </div>
                                        
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false" style="height: 38px;">
                                                <i class="ri-download-line"></i> Export
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item "
                                                        href="<?php echo e(route('admin.orders.export-excel', array_filter(['search' => request('search'), 'from_date' => request('from_date'), 'to_date' => request('to_date'), 'scope' => request('scope')]))); ?>">
                                                        <i class="ri-file-excel-line me-1 text-success"></i> Export
                                                        Excel
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="<?php echo e(route('admin.orders.export-pdf', array_filter(['search' => request('search'), 'from_date' => request('from_date'), 'to_date' => request('to_date'), 'scope' => request('scope')]))); ?>">
                                                        <i class="ri-file-pdf-line me-1 text-danger"></i> Export PDF
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>

                                    </div>
                                    <div class="d-flex align-items-center justify-content-between flex-wrap mb-3 gap-2 ">
                                        
                                        <form method="GET" action="<?php echo e(route('admin.orders')); ?>"
                                            class="d-flex align-items-center justify-content-between flex-wrap"
                                            style="gap: 8px;">
                                            <input type="text" name="search" class="form-control form-control-sm"
                                                placeholder="Search orders..." value="<?php echo e(request('search')); ?>"
                                                style="width: 180px; height: 38px;">

                                            <button type="submit" class="btn btn-outline-primary"
                                                style="height: 38px;">Search</button>
                                            <a href="<?php echo e(route('admin.orders')); ?>" class="btn btn-outline-secondary"
                                                style="height: 38px;">All
                                                Orders</a>
                                        </form>

                                        <form method="GET" action="<?php echo e(route('admin.orders')); ?>"
                                            class="d-flex align-items-center flex-wrap customFlexDiv" style="gap: 8px;">



                                            <div class="d-flex flex-column flex-md-row gap-2 customFlexDiv"><input
                                                    type="date" name="from_date"
                                                    class="form-control form-control-sm customSelect"
                                                    value="<?php echo e(request('from_date')); ?>">

                                                <input type="date" name="to_date"
                                                    class="form-control form-control-sm customSelect"
                                                    value="<?php echo e(request('to_date')); ?>">

                                                <button type="submit" class="btn btn-outline-primary" style="height: 38px;">
                                                    Filter
                                                </button>
                                            </div>

                                            <a href="<?php echo e(route('admin.add-order')); ?>" class="btn btn-solid"
                                                style="height: 38px;">Create Order</a>
                                    </div>

                                    </form>







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

                                    <style>
                                        .form-select {
                                            width: fit-content;
                                        }

                                        .customSelect {
                                            style="width: 150px; height: 38px;"
                                        }

                                        .order-status-cell {
                                            min-width: 190px;
                                        }

                                        .status-pill-select {
                                            min-width: 200px !important;
                                            border-radius: 4px;
                                            font-weight: 600;
                                            font-size: 12px;
                                            border-width: 1px;
                                            transition: all 0.2s ease;
                                            padding: 8px 16px;
                                            padding-right: 34px;
                                        }

                                        .status-pill-select.status-pending,
                                        .status-pill-select.status-processing,
                                        .status-pill-select.status-out_for_delivery {
                                            background-color: #fff1c1c7;
                                            border-color: #f0626524;
                                            color: #e3951d;
                                        }

                                        .status-pill-select.status-accepted,
                                        .status-pill-select.status-delivered {
                                            background-color: #eaf8ef;
                                            border-color: #18a95724;
                                            color: #3fb96b;
                                        }

                                        .status-pill-select.status-shipped {
                                            background-color: #eff6ff;
                                            border-color: #93c5fd;
                                            color: #1e40af;
                                        }

                                        .status-pill-select.status-rejected,
                                        .status-pill-select.status-cancelled {
                                            background-color: #fdecec;
                                            border-color: #dc354524;
                                            color: #dc3545;
                                        }

                                        .status-pill-select:focus {
                                            box-shadow: 0 0 0 0.2rem rgba(13, 148, 136, 0.15);
                                        }


                                        @media (max-width: 992px) {

                                            .customFlexDiv,
                                            .customFlexDiv .btn.btn-outline-primary {
                                                width: 100%;
                                            }

                                        }
                                    </style>

                                    <div class="table-responsive">
                                        <table class="table all-package order-table theme-table text-start" id="table_id">
                                            <thead>
                                                <tr>
                                                    <th>S.No.</th>
                                                    <th>Order ID</th>
                                                    <th>Order Date</th>
                                                    <th>Payment Method</th>
                                                    <th>Order Status</th>
                                                    <th>Amount</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <?php $__empty_1 = true; $__currentLoopData = $allOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                    <tr>
                                                        <td><?php echo e(($allOrders->firstItem() ?? 0) + $loop->index); ?></td>
                                                        <td>
                                                            <?php echo e($order->order_number); ?>

                                                            <br>
                                                            <small class="text-muted">
                                                                <?php echo e(optional(optional($order->customer)->user)->name ?? 'Customer N/A'); ?>

                                                            </small>
                                                            <!-- Vendor info removed -->
                                                        </td>

                                                        <td><?php echo e(\Carbon\Carbon::parse($order->created_at)->format('M d, Y')); ?>

                                                        </td>

                                                        <td><?php echo e(ucfirst($order->payment_method)); ?></td>

                                                        <td class="order-status-cell">
                                                            <form method="POST"
                                                                action="<?php echo e(route('admin.update-order-status', $order->order_id)); ?>"
                                                                class="order-status-form">
                                                                <?php echo csrf_field(); ?>
                                                                <?php echo $__env->make('admin.orders.partials.order-status-quick-select', ['order' => $order], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                            </form>
                                                        </td>

                                                        <td>₹<?php echo e(number_format((float) $order->total_amount, 2)); ?></td>

                                                        <td>
                                                            <ul>
                                                                <li>
                                                                    <a href="<?php echo e(route('admin.order-details', $order->order_id)); ?>"
                                                                        title="View order details">
                                                                        <i class="ri-eye-line"></i>
                                                                    </a>
                                                                </li>

                                                                <li>
                                                                    <a href="<?php echo e(route('admin.edit-order', $order->order_id)); ?>">
                                                                        <i class="ri-pencil-line"></i>
                                                                    </a>
                                                                </li>

                                                                <li>
                                                                    <a href="javascript:void(0)" class="delete-order"
                                                                        data-order-id="<?php echo e($order->order_id); ?>">
                                                                        <i class="ri-delete-bin-line"></i>
                                                                    </a>
                                                                </li>

                                                                <li>
                                                                    <a class="btn btn-sm btn-solid text-white"
                                                                        href="<?php echo e(route('admin.order-tracking', $order->order_id)); ?>">Tracking</a>
                                                                </li>
                                                            </ul>

                                                            <!-- <div class="d-flex flex-wrap gap-1 mt-2">
                                                                                                                                                                                                    <?php if($order->order_status === 'pending'): ?>
                                                                                                                                                                                                        <form method="POST" action="<?php echo e(route('admin.update-order-status', $order->order_id)); ?>" class="d-inline">
                                                                                                                                                                                                            <?php echo csrf_field(); ?>
                                                                                                                                                                                                            <input type="hidden" name="order_status" value="accepted">
                                                                                                                                                                                                            <button type="submit" class="btn btn-sm btn-success">Accept</button>
                                                                                                                                                                                                        </form>
                                                                                                                                                                                                        <form method="POST" action="<?php echo e(route('admin.update-order-status', $order->order_id)); ?>" class="d-inline">
                                                                                                                                                                                                            <?php echo csrf_field(); ?>
                                                                                                                                                                                                            <input type="hidden" name="order_status" value="rejected">
                                                                                                                                                                                                            <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                                                                                                                                                                                        </form>
                                                                                                                                                                                                    <?php endif; ?>

                                                                                                                                                                                                    <?php if(in_array($order->order_status, ['accepted', 'processing'])): ?>
                                                                                                                                                                                                        <form method="POST" action="<?php echo e(route('admin.update-order-status', $order->order_id)); ?>" class="d-inline">
                                                                                                                                                                                                            <?php echo csrf_field(); ?>
                                                                                                                                                                                                            <input type="hidden" name="order_status" value="out_for_delivery">
                                                                                                                                                                                                            <button type="submit" class="btn btn-sm btn-primary">Out for Delivery</button>
                                                                                                                                                                                                        </form>
                                                                                                                                                                                                    <?php endif; ?>

                                                                                                                                                                                                    <?php if($order->order_status === 'out_for_delivery'): ?>
                                                                                                                                                                                                        <form method="POST" action="<?php echo e(route('admin.update-order-status', $order->order_id)); ?>" class="d-inline">
                                                                                                                                                                                                            <?php echo csrf_field(); ?>
                                                                                                                                                                                                            <input type="hidden" name="order_status" value="delivered">
                                                                                                                                                                                                            <button type="submit" class="btn btn-sm btn-dark">Delivered</button>
                                                                                                                                                                                                        </form>
                                                                                                                                                                                                    <?php endif; ?>
                                                                                                                                                                                                </div> -->
                                                        </td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center py-4">No orders found.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <?php if($allOrders->hasPages()): ?>
                                        <div
                                            class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 mt-3 admin-pagination-wrap">
                                            <div class="text-muted small">
                                                Showing <?php echo e($allOrders->firstItem()); ?> to <?php echo e($allOrders->lastItem()); ?> of
                                                <?php echo e($allOrders->total()); ?> entries
                                            </div>
                                            <div>
                                                <?php echo e($allOrders->onEachSide(1)->links('pagination::bootstrap-5')); ?>

                                            </div>
                                        </div>
                                    <?php endif; ?>
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
    <style>
        .admin-pagination-wrap .pagination {
            margin-bottom: 0;
        }
    </style>
    <script>
        function confirmDelete(id) {
            var siteUrl = "<?php echo e(url('/')); ?>";
            Swal.fire({
                title: 'Are you sure?',
                text: 'This will delete the order.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.value) {
                    window.location.href = siteUrl + '/admin/delete-order/' + id;
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.delete-order').forEach(function (element) {
                element.addEventListener('click', function () {
                    confirmDelete(this.dataset.orderId);
                });
            });

            document.querySelectorAll('.order-status-select').forEach(function (selectElement) {
                selectElement.dataset.lastValue = selectElement.value;

                var applySelectStatusClass = function (element, status) {
                    element.classList.remove(
                        'status-pending',
                        'status-accepted',
                        'status-processing',
                        'status-shipped',
                        'status-out_for_delivery',
                        'status-delivered',
                        'status-rejected',
                        'status-cancelled'
                    );
                    element.classList.add('status-' + status);
                };

                applySelectStatusClass(selectElement, selectElement.value);

                selectElement.addEventListener('change', function () {
                    var newStatus = this.value;
                    var oldStatus = this.dataset.lastValue || this.dataset.currentStatus || '';
                    var form = this.closest('form');
                    var orderNumber = this.dataset.orderNumber || '';

                    if (!form || newStatus === oldStatus) {
                        return;
                    }

                    var humanize = function (status) {
                        if (!status) return '';
                        var labels = {
                            processing: 'Processing',
                            shipped: 'Shipped',
                            delivered: 'Delivered',
                            cancelled: 'Cancelled',
                            pending: 'Pending',
                            payment_pending: 'Payment pending',
                            confirmed: 'Confirmed',
                            accepted: 'Accepted',
                            rejected: 'Rejected',
                            out_for_delivery: 'Out for delivery'
                        };
                        if (labels[status]) return labels[status];
                        return status.replace(/_/g, ' ').replace(/\b\w/g, function (char) { return char.toUpperCase(); });
                    };

                    applySelectStatusClass(this, newStatus);

                    Swal.fire({
                        title: 'Change Order Status?',
                        html: 'Order <strong>' + orderNumber + '</strong><br>from <strong>' + humanize(oldStatus) + '</strong> to <strong>' + humanize(newStatus) + '</strong>.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#0d9488',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Yes, Change Status',
                        cancelButtonText: 'No, Keep Current'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        } else {
                            this.value = oldStatus;
                            applySelectStatusClass(this, oldStatus);
                        }
                    });
                });
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/developmentalpha/public_html/swastik-food-machinery.developmentalphawizz.com/resources/views/admin/orders/ordersList.blade.php ENDPATH**/ ?>