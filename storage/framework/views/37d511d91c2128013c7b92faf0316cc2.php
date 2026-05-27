

<?php $__env->startSection('content'); ?>
<style>
    #sidebar-menu .sidebar-submenu li a.active{
        color:  #f7bf57 !important;
        font-weight: 600;
    }
</style>
<div class="page-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="title-header option-title d-flex flex-wrap align-items-center gap-2 mb-4">
                    <h5 class="mb-0"><i class="ri-shopping-bag-3-line me-2"></i><?php echo e($title); ?></h5>
                    <a class="btn btn-theme btn-sm ms-auto" href="<?php echo e(route('admin.add-product', array_filter(['segment' => $segmentFilter ?? null]))); ?>">
                        <i class="ri-add-line me-1"></i>Add product
                    </a>
                </div>

                

                <?php if(session('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ri-checkbox-circle-line me-2"></i><?php echo e(session('success')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if(session('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ri-error-warning-line me-2"></i><?php echo e(session('error')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 product-stat product-stat-teal h-100">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div class="stat-content">
                                    <small class="stat-label">Approved</small>
                                    <h3 class="stat-value"><?php echo e($approved); ?></h3>
                                </div>
                                <span class="stat-icon-box">
                                    <i class="ri-checkbox-circle-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 product-stat product-stat-amber h-100">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div class="stat-content">
                                    <small class="stat-label">Pending</small>
                                    <h3 class="stat-value"><?php echo e($pending); ?></h3>
                                </div>
                                <span class="stat-icon-box">
                                    <i class="ri-time-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 product-stat product-stat-rose h-100">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div class="stat-content">
                                    <small class="stat-label">Rejected</small>
                                    <h3 class="stat-value"><?php echo e($rejected); ?></h3>
                                </div>
                                <span class="stat-icon-box">
                                    <i class="ri-close-circle-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-table">
                      <div class="product-search-toolbar flex-wrap gap-2">
                            <div class="dropdown">
                                <button class="btn btn-theme dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-download-line"></i> Export
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="<?php echo e(route('admin.products.export-excel', array_filter(['search' => $search ?? '', 'segment' => $segmentFilter ?? '']))); ?>">
                                            <i class="ri-file-excel-line me-1 text-success"></i> Export Excel
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?php echo e(route('admin.products.export-pdf', array_filter(['search' => $search ?? '', 'segment' => $segmentFilter ?? '']))); ?>">
                                            <i class="ri-file-pdf-line me-1 text-danger"></i> Export PDF
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    <div class="card-body">
                        <div class="product-search-toolbar flex-wrap gap-2">
                             <form method="GET" action="<?php echo e(route('admin.products')); ?>" class="product-search-form">
                                <?php if(!empty($segmentFilter)): ?>
                                    <input type="hidden" name="segment" value="<?php echo e($segmentFilter); ?>">
                                <?php endif; ?>
                                <div class="product-search-field d-flex align-items-center gap-2" style="max-width: 350px !important;">
                                    <i class="ri-search-line product-search-icon"></i>
                                    <input type="text" name="search" class="form-control" style="font-size: 14px;" value="<?php echo e($search ?? ''); ?>" placeholder="Search by product name, SKU, store, or category">
                                    <button type="submit" class="btn btn-theme ">Search</button>
                                </div>
                                <a href="<?php echo e(route('admin.products', array_filter(['segment' => $segmentFilter ?? null]))); ?>" class="btn btn-outline-secondary">Reset</a>
                                
                            </form>

                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="text-muted  me-1" style="font-size: 16px; ">Segment:</span>
                                <a href="<?php echo e(route('admin.products', request()->except(['segment', 'page']))); ?>" class="btn   <?php echo e(empty($segmentFilter) ? 'btn-theme' : 'btn-outline-secondary'); ?>" style="height: 40px !important;">All</a>
                                <a href="<?php echo e(route('admin.products', array_merge(request()->except(['segment', 'page']), ['segment' => 'retailer']))); ?>" class="btn <?php echo e(($segmentFilter ?? '') === 'retailer' ? 'btn-theme' : 'btn-outline-secondary'); ?>" style="height: 40px !important;">Retailer</a>
                                <a href="<?php echo e(route('admin.products', array_merge(request()->except(['segment', 'page']), ['segment' => 'wholesaler']))); ?>" class="btn <?php echo e(($segmentFilter ?? '') === 'wholesaler' ? 'btn-theme' : 'btn-outline-secondary'); ?>" style="height: 40px !important;">Wholesaler</a>
                            </div>

                           
                        </div>
                      
                        <div class="table-responsive">
                            <table class="table table table-modern align-middle" id="table_id">
                                <thead>
                                    <tr>
                                        <th>S.No.</th>
                                        <th>Image</th>
                                        <th>Product Name</th>

                                        <th>Category</th>
                                        <th>Segment</th>
                                        <th>Pricing</th>
                                        <th>Stock Qty</th>
                                        <th>SKU</th>
                                        <th>Approval Status</th>
                                        <th class="text-center">Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $allProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td><?php echo e($allProducts->firstItem() + $loop->index); ?></td>
                                            <td>
                                                <div class="table-image" style="float:none;">
                                                    <?php if($product->product_image): ?>
                                                        <img src="<?php echo e(asset('public/uploads/products/' . $product->product_image)); ?>" class="img-fluid" alt="<?php echo e($product->product_name); ?>">
                                                    <?php else: ?>
                                                        <img src="<?php echo e(asset('public/assets/images/product/1.png')); ?>" class="img-fluid" alt="No image">
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="user-name">
                                                    <span><?php echo e($product->product_name); ?></span>
                                                    <!-- <span><?php echo e($product->product_type ? ucwords(str_replace(['_', '-'], ' ', $product->product_type)) . ' Product' : 'Simple Product'); ?></span> -->
                                                </div>
                                            </td>

                                            <td>
                                                <span class="d-block text-nowrap " ><?php echo e($product->category_name ?: '-'); ?></span>
                                                <?php if(!empty($product->sub_cat_name)): ?>
                                                    <small class="text-muted"><?php echo e($product->sub_cat_name); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if(!empty($product->target_user_type)): ?>
                                                    <?php if($product->target_user_type === \App\Models\Product::TARGET_WHOLESALER): ?>
                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Wholesaler</span>
                                                    <?php elseif($product->target_user_type === \App\Models\Product::TARGET_RETAILER): ?>
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Retailer</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle"><?php echo e($product->target_user_type); ?></span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                    $mrpAmount = (float) ($product->mrp_price ?? $product->price ?? 0);
                                                    $sellingAmount = (float) ($product->price ?? 0);
                                                ?>
                                                <?php if($mrpAmount > $sellingAmount): ?>
                                                    <small class="d-block text-nowrap text-muted">MRP: <span class="text-decoration-line-through">₹<?php echo e(number_format($mrpAmount, 2)); ?></span></small>
                                                <?php else: ?>
                                                    <small class="d-block text-nowrap text-muted">MRP: ₹<?php echo e(number_format($mrpAmount, 2)); ?></small>
                                                <?php endif; ?>
                                                <span class="d-block fw-semibold text-nowrap text-success">Price: ₹<?php echo e(number_format($sellingAmount, 2)); ?></span>
                                            </td>
                                            <td><?php echo e((int) $product->stock); ?></td>
                                            <td class="text-nowrap"><?php echo e($product->sku ?: '-'); ?></td>
                                            <td class="approval-status-cell">
                                                <form method="POST" action="<?php echo e(route('admin.products.update-approval-status', $product->product_id)); ?>" class="product-approval-form m-0">
                                                    <?php echo csrf_field(); ?>
                                                    <select name="status" class="form-select form-select-sm product-approval-select status-pill-select"
                                                        data-product-id="<?php echo e($product->product_id); ?>"
                                                        data-product-name="<?php echo e($product->product_name); ?>"
                                                        data-current-status="<?php echo e((string) $product->status); ?>"
                                                        aria-label="Change product approval status">
                                                        <option value="1" <?php echo e((int) $product->status === 1 ? 'selected' : ''); ?>>Approved</option>
                                                        <option value="2" <?php echo e((int) $product->status === 2 ? 'selected' : ''); ?>>Pending</option>
                                                        <option value="3" <?php echo e((int) $product->status === 3 ? 'selected' : ''); ?>>Rejected</option>
                                                    </select>
                                                </form>
                                            </td>
                                            <td class="text-center">
                                                <form method="POST" action="<?php echo e(route('admin.products.toggle-status', $product->product_id)); ?>" class="status-toggle-form m-0">
                                                    <?php echo csrf_field(); ?>
                                                    <?php
                                                        $isProductActive = (int) $product->is_active_status === 1;
                                                    ?>
                                                    <label class="status-switch" title="Toggle status">
                                                        <input type="checkbox" aria-label="Toggle product status" <?php echo e($isProductActive ? 'checked' : ''); ?> onchange="this.form.submit()">
                                                        <span class="status-slider"></span>
                                                    </label>
                                                </form>
                                            </td>
                                            <td>
                                                <ul class="d-flex gap-2 mb-0 list-unstyled">
                                                    <li>
                                                        <a href="<?php echo e(route('admin.view-product', $product->product_id)); ?>" title="View">
                                                            <i class="ri-eye-line"></i>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="<?php echo e(route('admin.edit-product', array_merge(['id' => $product->product_id], array_filter(['segment' => $segmentFilter ?? null])))); ?>" title="Edit">
                                                            <i class="ri-pencil-line"></i>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)" title="Delete" data-product-id="<?php echo e($product->product_id); ?>" data-product-name="<?php echo e($product->product_name); ?>" data-bs-toggle="modal" data-bs-target="#deleteProductModal">
                                                            <i class="ri-delete-bin-line text-danger"></i>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="11" class="text-center text-muted py-4">No products found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if($allProducts->hasPages()): ?>
                            <div class="product-pagination-wrap d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                                <p class="text-muted mb-0 small">
                                    Showing <?php echo e($allProducts->firstItem()); ?> to <?php echo e($allProducts->lastItem()); ?> of <?php echo e($allProducts->total()); ?> products
                                </p>
                                <div class="product-pagination">
                                    <?php echo e($allProducts->onEachSide(1)->links('pagination::bootstrap-5')); ?>

                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="modal fade" id="deleteProductModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Delete Product</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                                <i class="ri-error-warning-line text-danger" style="font-size:48px;"></i>
                                <p class="mt-3 mb-1">Are you sure you want to delete this product?</p>
                                <p class="mb-0 text-muted" id="deleteProductName"></p>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <a href="#" class="btn btn-danger" id="confirmDeleteProductBtn">Yes, Delete</a>
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
.product-stat {
    border-radius: 12px;
    border-left: 3px solid transparent;
}
.product-stat .card-body {
    padding: 18px 14px 18px 12px;
}
.product-stat .stat-label {
    display: block;
    font-size: 15px;
    font-weight: 500;
    color: #5f6b7a;
}
.product-stat .stat-value {
    margin: 6px 0 0;
    font-size: 40px;
    line-height: 1;
    font-weight: 700;
    color: #121f2d;
}
.product-stat .stat-icon-box {
    width: 54px;
    height: 54px;
    border-radius: 8px;
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}

.product-stat-teal {
    background: #dff1ed;
    border-left-color: #1ca18c;
}
.product-stat-teal .stat-icon-box {
    background: #1ca18c;
}

.product-stat-amber {
    background: #f6efe2;
    border-left-color: #f2a533;
}
.product-stat-amber .stat-icon-box {
    background: #f2a533;
}

.product-stat-rose {
    background: #f4e8e7;
    border-left-color: #f06265;
}
.product-stat-rose .stat-icon-box {
    background: #f06265;
}

.approval-status-cell {
    min-width: 180px;
}

.status-pill-select {
    width: 50px;
    border-radius: 999px;
    font-weight: 600;
    font-size: 12px;
    border-width: 1px;
    transition: all 0.2s ease;
    padding-right: 34px;
}

.status-pill-select.status-approved {
    width: 120px;
    background-color: #eaf8ef !important;
    color: #3fb96b !important;
    padding: 8px 12px;
    border: 1px solid #7bffab;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 700;
}

.status-pill-select.status-pending {
    width: 120px;
    border-color: #fdba74;
    color: #9a3412;
}

.status-pill-select.status-rejected {
    width: 120px;
    border-color: #fca5a5;
    color: #991b1b;
}

.status-pill-select:focus {
    box-shadow: 0 0 0 0.2rem rgba(13, 148, 136, 0.15);
}
.product-search-form{
    justify-content: unset !important;
}


@media (max-width: 767px) {
    .product-stat .stat-label {
        font-size: 12px;
    }
    .product-stat .stat-value {
        font-size: 30px;
    }
}
.status-switch {
    position: relative;
    display: inline-block;
    width: 46px;
    height: 24px;
}
.status-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.status-slider {
    position: absolute;
    cursor: pointer;
    inset: 0;
    background: linear-gradient(135deg, #b8872b 0%, #c9973a 50%, #e0b45a 100%);
    transition: .25s;
    border-radius: 24px;
}
.status-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    top: 3px;
    background-color: #fff;
    transition: .25s;
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0,0,0,.25);
}
.status-switch input:checked + .status-slider {
    background-color: #0da487;
}
.status-switch input:checked + .status-slider:before {
    transform: translateX(22px);
}

.product-pagination .pagination {
    margin-bottom: 0;
    gap: 6px;
    flex-wrap: wrap;
}

.product-pagination .page-item {
    margin: 0;
}

.product-pagination .page-item .page-link {
    border: 1px solid #d9e2ec;
    border-radius: 8px;
    color: #334155;
    min-width: 38px;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 10px;
    font-size: 13px;
    font-weight: 600;
    background: #fff;
    box-shadow: none;
}

.product-pagination .page-item.active .page-link {
    background: #0da487;
    border-color: #0da487;
    color: #fff;
}

.product-pagination .page-item .page-link:hover {
    background: #f0fdfa;
    border-color: #0da487;
    color: #0f766e;
}

.product-pagination .page-item.disabled .page-link {
    color: #94a3b8;
    background: #f8fafc;
    border-color: #e2e8f0;
}

@media (max-width: 767px) {
    .product-pagination-wrap {
        align-items: flex-start !important;
    }

    .product-pagination .page-item .page-link {
        min-width: 34px;
        height: 34px;
        font-size: 12px;
    }
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('deleteProductModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', function (event) {
            var trigger = event.relatedTarget;
            if (!trigger) return;

            var productId = trigger.getAttribute('data-product-id');
            var productName = trigger.getAttribute('data-product-name') || 'this product';

            document.getElementById('deleteProductName').textContent = productName;
            document.getElementById('confirmDeleteProductBtn').href = '<?php echo e(url('/admin/delete-product/')); ?>/' + productId;
        });
    }

    document.querySelectorAll('.product-approval-select').forEach(function (selectElement) {
        selectElement.dataset.lastValue = selectElement.value;

        var applySelectStatusClass = function (element, status) {
            element.classList.remove('status-approved', 'status-pending', 'status-rejected');
            element.classList.add('status-' + status);
        };

        var humanize = function (status) {
            if (status === '1') return 'Approved';
            if (status === '2') return 'Pending';
            if (status === '3') return 'Rejected';
            return status || '';
        };

        var normalizeStatus = function (status) {
            if (status === '1') return 'approved';
            if (status === '2') return 'pending';
            if (status === '3') return 'rejected';
            return status || '';
        };

        applySelectStatusClass(selectElement, normalizeStatus(selectElement.value));

        selectElement.addEventListener('change', function () {
            var newStatus = this.value;
            var oldStatus = this.dataset.lastValue || this.dataset.currentStatus || '';
            var form = this.closest('form');
            var productName = this.dataset.productName || 'this product';

            if (!form || newStatus === oldStatus) {
                return;
            }

            applySelectStatusClass(this, normalizeStatus(newStatus));

            Swal.fire({
                title: 'Change Product Approval Status?',
                html: '<strong>' + productName + '</strong><br>from <strong>' + humanize(oldStatus) + '</strong> to <strong>' + humanize(newStatus) + '</strong>.',
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
                    applySelectStatusClass(this, normalizeStatus(oldStatus));
                }
            });
        });
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/developmentalpha/public_html/swastik-food-machinery.developmentalphawizz.com/resources/views/admin/products/productList.blade.php ENDPATH**/ ?>