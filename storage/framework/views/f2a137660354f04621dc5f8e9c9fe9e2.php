

<?php $__env->startSection('content'); ?>
    <?php
        $gallery = !empty($product->gallery_images) ? array_filter(array_map('trim', explode(',', $product->gallery_images))) : [];
        $editSegment = match ($product->target_user_type ?? null) {
            \App\Models\Product::TARGET_RETAILER => 'retailer',
            \App\Models\Product::TARGET_WHOLESALER => 'wholesaler',
            default => null,
        };
        $approvalLabel = match ((string) ($product->status ?? '')) {
            '1' => 'Approved',
            '2' => 'Pending',
            '3' => 'Rejected',
            default => '—',
        };
    ?>
    <div class="page-body">
        <div class="container-fluid">
            <div class="title-header option-title d-flex align-items-center mb-4">
                <h5><?php echo e($title); ?></h5>
                <a href="<?php echo e(route('admin.edit-product', array_merge(['id' => $product->product_id], array_filter(['segment' => $editSegment])))); ?>"
                    class="btn btn-theme btn-sm ms-auto me-2">Edit Product</a>
                <a href="<?php echo e(route('admin.products', array_filter(['segment' => $editSegment]))); ?>"
                    class="btn btn-outline-secondary btn-sm">Back</a>
            </div>

            <div class="row g-4">
                <div class="col-xl-4">
                    <div class="card h-100 product-summary-card">
                        <div class="card-body text-center">
                            <img src="<?php echo e(!empty($product->product_image) ? asset('public/uploads/products/' . $product->product_image) : asset('public/assets/images/product/1.png')); ?>"
                                alt="product" class="summary-image mb-3">
                            <h5 class="mb-1"><?php echo e($product->product_name); ?></h5>
                            <span class="badge badge-soft-warning mb-2">SKU: <?php echo e($product->sku ?: '—'); ?></span>
                            <div class="d-grid gap-2 mt-3 text-start">
                                <div class="summary-line"><small>Customer segment</small><strong>
                                        <?php if(!empty($product->target_user_type)): ?>
                                            <?php if($product->target_user_type === \App\Models\Product::TARGET_WHOLESALER): ?>
                                                Wholesaler
                                            <?php else: ?>
                                                Retailer
                                            <?php endif; ?>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </strong></div>
                                <div class="summary-line">
                                    <small>MRP</small><strong>₹<?php echo e(number_format((float) ($product->mrp_price ?? $product->price), 2)); ?></strong>
                                </div>
                                <div class="summary-line">
                                    <small>Price</small><strong>₹<?php echo e(number_format((float) $product->price, 2)); ?></strong>
                                </div>
                                <div class="summary-line"><small>Stock</small><strong><?php echo e((int) $product->stock); ?></strong>
                                </div>
                                <div class="summary-line"><small>Stock
                                        status</small><strong><?php echo e(str_replace('_', ' ', $product->stock_status ?: '—')); ?></strong>
                                </div>
                                <?php if(($product->target_user_type ?? null) === \App\Models\Product::TARGET_WHOLESALER): ?>
                                    <div class="summary-line"><small>Min. order
                                            qty</small><strong><?php echo e($product->min_quantity !== null ? (int) $product->min_quantity : '—'); ?></strong>
                                    </div>
                                <?php endif; ?>
                                <div class="summary-line"><small>Approval</small><strong><?php echo e($approvalLabel); ?></strong></div>
                                <div class="summary-line"><small>Catalog
                                        listing</small><strong><?php echo e((int) $product->is_active_status === 1 ? 'Active' : 'Inactive'); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8">
                    <div class="card mb-4">
                        <div class="card-header card-header-2">
                            <h5>Product details</h5>
                        </div>
                        <div class="card-body detail-grid">
                            <div class="detail-item span-2"><label>Description</label><span
                                    class="text-break"><?php echo e($product->product_description ?: '—'); ?></span></div>
                            <div class="detail-item">
                                <label>Category</label><span><?php echo e($product->category_name ?: '—'); ?></span></div>
                            <div class="detail-item">
                                <label>Sub-category</label><span><?php echo e($product->sub_cat_name ?: '—'); ?></span></div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header card-header-2">
                            <h5>Gallery</h5>
                        </div>
                        <div class="card-body">
                            <div class="preview-grid">
                                <?php $__empty_1 = true; $__currentLoopData = $gallery; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <img src="<?php echo e(asset('public/uploads/products/' . $img)); ?>" alt="gallery">
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <p class="text-muted mb-0">No gallery images.</p>
                                <?php endif; ?>
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
        .product-summary-card {
            border: 1px solid #ebeff4;
            background: radial-gradient(circle at top right, rgba(193, 143, 51, .16), #fff 60%);
        }

        .summary-image {
            width: 100%;
            max-width: 240px;
            height: 220px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #dbe2ea;
        }

        .summary-line {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dashed #e4e9ef;
            padding: 4px 0;
        }

        .summary-line small {
            color: #7f8a99;
        }

        .detail-grid {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .detail-item {
            border: 1px solid #ecf1f5;
            border-radius: 8px;
            background: #fafbfd;
            padding: 10px 12px;
        }

        .detail-item.span-2 {
            grid-column: 1 / -1;
        }

        .detail-item label {
            display: block;
            font-size: 11px;
            color: #7f8a99;
            text-transform: uppercase;
            margin-bottom: 3px;
            font-weight: 600;
        }

        .detail-item span {
            font-size: 14px;
            color: #27313f;
        }

        .preview-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .preview-grid img {
            width: 94px;
            height: 94px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #d9e0e8;
        }

        @media (max-width: 991px) {
            .detail-grid {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }
        }
    </style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/developmentalpha/public_html/swastik-food-machinery.developmentalphawizz.com/resources/views/admin/products/viewProduct.blade.php ENDPATH**/ ?>