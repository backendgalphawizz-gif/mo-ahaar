<?php $__env->startSection('content'); ?>
    <div class="page-body">
        <div class="container-fluid">
            <div class="title-header option-title d-flex align-items-center flex-wrap gap-2 mb-4">
                <h5 class="mb-0"><?php echo e($title); ?></h5>
                <a class="btn btn-outline-secondary btn-sm ms-auto"
                    href="<?php echo e(route('admin.products', array_filter(['segment' => $segment ?? null]))); ?>">
                    <i class="ri-arrow-left-line me-1"></i>Back to products
                </a>
            </div>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <div class="fw-semibold mb-1">Please fix the highlighted fields and submit again.</div>
                    <ul class="mb-0 ps-3">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('admin.store-product')); ?>" enctype="multipart/form-data" id="productForm">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="is_returnable" value="0">
                <input type="hidden" name="is_active_status" value="1">

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="d-flex align-items-center gap-2 text-muted small fw-semibold text-uppercase"
                                            style="letter-spacing:.4px;">
                                            <i class="ri-information-line"></i>
                                            Basic Information
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label-title">Product name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="product_name"
                                            class="form-control <?php $__errorArgs = ['product_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                            placeholder="Product name" value="<?php echo e(old('product_name')); ?>" maxlength="100"
                                            data-product-name required>
                                        <?php $__errorArgs = ['product_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="invalid-feedback d-block mb-0"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-title">Customer segment <span
                                                class="text-danger">*</span></label>
                                        <select name="target_user_type" id="target_user_type"
                                            class="form-select <?php $__errorArgs = ['target_user_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                            <option value="">Select retailer or wholesaler</option>
                                            <option value="<?php echo e(\App\Models\Product::TARGET_RETAILER); ?>" <?php echo e((string) old('target_user_type', $defaultTarget ?? '') === \App\Models\Product::TARGET_RETAILER ? 'selected' : ''); ?>>Retailer
                                            </option>
                                            <option value="<?php echo e(\App\Models\Product::TARGET_WHOLESALER); ?>" <?php echo e((string) old('target_user_type', $defaultTarget ?? '') === \App\Models\Product::TARGET_WHOLESALER ? 'selected' : ''); ?>>Wholesaler
                                            </option>
                                        </select>
                                        <small class="text-muted">Retailer and wholesaler catalogs are separate in the list
                                            and app.</small>
                                        <?php $__errorArgs = ['target_user_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="invalid-feedback d-block mb-0"><?php echo e($message); ?>

                                        </p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <div> &nbsp;</div>
                                        <div class="form-check mt-3">
                                            <input class="form-check-input" type="checkbox" name="featured" id="featured"
                                                value="1" <?php echo e(old('featured', 0) == 1 ? 'checked' : ''); ?>>
                                            <label class="form-check-label" for="featured">
                                                Feature this product
                                            </label>
                                        </div>
                                        <small class="text-muted">Featured products are highlighted in the app and on the
                                            website.</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-title">Category <span class="text-danger">*</span></label>
                                        <select class="form-select <?php $__errorArgs = ['category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                            name="category_id" id="category_id" required>
                                            <option value="">Select category</option>
                                            <?php $__currentLoopData = $categoryList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($category->category_id); ?>" <?php echo e((string) old('category_id') === (string) $category->category_id ? 'selected' : ''); ?>>
                                                    <?php echo e($category->category_name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <?php $__errorArgs = ['category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="invalid-feedback d-block mb-0"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-title">Sub-category <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select <?php $__errorArgs = ['sub_category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                            name="sub_category_id" id="sub_category_id" required>
                                            <option value="">Select sub-category</option>
                                        </select>
                                        <?php $__errorArgs = ['sub_category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="invalid-feedback d-block mb-0"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label-title">Product Demo Video (YouTube URL)</label>
                                        <input type="url" name="video"
                                            class="form-control <?php $__errorArgs = ['video'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                            placeholder="https://www.youtube.com/watch?v=..." value="<?php echo e(old('video')); ?>">
                                        <small class="text-muted">Paste a YouTube video link to show a demo video for this
                                            product. Optional.</small>
                                        <?php $__errorArgs = ['video'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="invalid-feedback d-block mb-0"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label-title">Description <span
                                                class="text-danger">*</span></label>
                                        <textarea name="product_description" id="product_description" rows="3"
                                            maxlength="220"
                                            class="form-control <?php $__errorArgs = ['product_description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                            placeholder="Full description"
                                            required><?php echo e(old('product_description')); ?></textarea>
                                        <div class="d-flex align-items-center justify-content-between mt-1">
                                            <small class="text-muted"><i class="ri-information-line me-1"></i>Maximum 220
                                                characters allowed.</small>
                                            <small id="desc_counter" class="text-muted"><span
                                                    id="desc_count"><?php echo e(strlen(old('product_description', ''))); ?></span> /
                                                220</small>
                                        </div>
                                        <?php $__errorArgs = ['product_description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="invalid-feedback d-block mb-0"><?php echo e($message); ?>

                                        </p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="col-12 pt-1 mt-2 border-top"></div>

                                    <div class="col-12">
                                        <div class="d-flex align-items-center gap-2 text-muted small fw-semibold text-uppercase"
                                            style="letter-spacing:.4px;">
                                            <i class="ri-price-tag-3-line"></i>
                                            Pricing
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label-title">MRP price (₹) <span
                                                class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" name="mrp_price" id="mrp_price"
                                            class="form-control <?php $__errorArgs = ['mrp_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="0.00"
                                            value="<?php echo e(old('mrp_price')); ?>" required>
                                        <?php $__errorArgs = ['mrp_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="invalid-feedback d-block mb-0"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label-title">Discount (%)</label>
                                        <input type="number" step="0.01" min="0" max="100" name="discount" id="discount_pct"
                                            class="form-control <?php $__errorArgs = ['discount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="0.00"
                                            value="<?php echo e(old('discount', 0)); ?>">
                                        <small class="text-muted">Enter 0 for no discount.</small>
                                        <?php $__errorArgs = ['discount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="invalid-feedback d-block mb-0"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label-title">Price after discount (₹)</label>
                                        <input type="number" step="0.01" id="price_display"
                                            class="form-control bg-light text-dark" placeholder="0.00" readonly
                                            tabindex="-1">
                                        <input type="hidden" name="price" id="price" value="<?php echo e(old('price')); ?>">
                                        <?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="invalid-feedback d-block mb-0"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="col-12 pt-1 mt-2 border-top"></div>

                                    <div class="col-12">
                                        <div class="d-flex align-items-center gap-2 text-muted small fw-semibold text-uppercase"
                                            style="letter-spacing:.4px;">
                                            <i class="ri-archive-line"></i>
                                            Inventory & Tax
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label-title">Stock quantity <span
                                                class="text-danger">*</span></label>
                                        <input type="number" min="0" name="stock" id="product_stock"
                                            class="form-control <?php $__errorArgs = ['stock'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="0"
                                            value="<?php echo e(old('stock')); ?>" required>
                                        <?php $__errorArgs = ['stock'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="invalid-feedback d-block mb-0"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label-title">Stock status <span
                                                class="text-danger">*</span></label>
                                        <select name="stock_status"
                                            class="form-select <?php $__errorArgs = ['stock_status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                            <option value="in_stock" <?php echo e(old('stock_status', 'in_stock') === 'in_stock' ? 'selected' : ''); ?>>In stock</option>
                                            <option value="out_of_stock" <?php echo e(old('stock_status') === 'out_of_stock' ? 'selected' : ''); ?>>Out of stock</option>
                                            <option value="backorder" <?php echo e(old('stock_status') === 'backorder' ? 'selected' : ''); ?>>On backorder</option>
                                        </select>
                                        <?php $__errorArgs = ['stock_status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="invalid-feedback d-block mb-0"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="col-md-4 <?php echo e((string) old('target_user_type', $defaultTarget ?? '') === \App\Models\Product::TARGET_WHOLESALER ? '' : 'd-none'); ?>"
                                        id="wholesalerMinQtyWrap">
                                        <label class="form-label-title">Start Order Quantity <span
                                                class="text-danger">*</span></label>
                                        <input type="number" min="1" name="min_quantity" id="min_quantity"
                                            class="form-control <?php $__errorArgs = ['min_quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                            placeholder="e.g. 6" value="<?php echo e(old('min_quantity')); ?>">
                                        <small class="text-muted">Shown only for wholesaler products. Must be at least 1 and
                                            not greater than stock.</small>
                                        <?php $__errorArgs = ['min_quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="invalid-feedback d-block mb-0"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label-title">SKU <small class="text-muted fw-normal">(optional —
                                                auto-generated if empty)</small></label>
                                        <input type="text" name="sku" maxlength="100"
                                            class="form-control <?php $__errorArgs = ['sku'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                            placeholder="Leave blank to auto-generate" value="<?php echo e(old('sku')); ?>">
                                        <?php $__errorArgs = ['sku'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="invalid-feedback d-block mb-0"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label-title">GST Type</label>
                                        <select name="gst_calculation_type"
                                            class="form-select <?php $__errorArgs = ['gst_calculation_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                            <option value="excluded" <?php echo e(old('gst_calculation_type', \App\Models\Product::GST_EXCLUDED) === \App\Models\Product::GST_EXCLUDED ? 'selected' : ''); ?>>Excluded GST</option>
                                            <option value="included" <?php echo e(old('gst_calculation_type') === \App\Models\Product::GST_INCLUDED ? 'selected' : ''); ?>>Included GST</option>
                                        </select>
                                        <small class="text-muted">Choose if selling price is GST-exclusive or
                                            GST-inclusive.</small>
                                        <?php $__errorArgs = ['gst_calculation_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="invalid-feedback d-block mb-0">
                                        <?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label-title">GST Tax Slab</label>
                                        <select name="gst_tax_id"
                                            class="form-select <?php $__errorArgs = ['gst_tax_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                            <option value="">No GST / Exempt</option>
                                            <?php $__currentLoopData = $gstTaxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gst): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($gst->id); ?>" <?php echo e((string) old('gst_tax_id') === (string) $gst->id ? 'selected' : ''); ?>>
                                                    <?php echo e($gst->name); ?> (<?php echo e(number_format((float) $gst->percentage, 2)); ?>%)
                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <small class="text-muted">GST is added on top of the selling price. Leave blank if
                                            exempt.</small>
                                        <?php $__errorArgs = ['gst_tax_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="invalid-feedback d-block mb-0"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="mb-4 fw-semibold d-flex align-items-center gap-2" style="color:#374151">
                                    <i class="ri-image-2-line" style="font-size:18px;color:#d8ab50"></i> Images
                                </h6>

                                
                                <div class="mb-4">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label-title mb-0">Thumbnail <span
                                                class="text-danger">*</span></label>
                                        <small class="text-muted">600×600 · max 2 MB</small>
                                    </div>
                                    <input type="file" name="product_image" id="product_image"
                                        class="d-none <?php $__errorArgs = ['product_image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" accept="image/*">
                                    <label for="product_image" id="thumbDropZone"
                                        class="img-dropzone <?php $__errorArgs = ['product_image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> has-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                        <div class="dropzone-placeholder" id="thumbPlaceholder">
                                            <i class="ri-image-add-line"></i>
                                            <span class="d-block mt-2 fw-medium" style="font-size:13px">Click or drag &amp;
                                                drop</span>
                                            <small class="text-muted" style="font-size:11px">jpg &nbsp;·&nbsp; jpeg
                                                &nbsp;·&nbsp; png &nbsp;·&nbsp; webp</small>
                                        </div>
                                        <div id="thumbPreview" class="dropzone-preview d-none">
                                            <img id="thumbPreviewImg" src="" alt="thumbnail">
                                            <div class="dropzone-overlay"><i class="ri-edit-2-line me-1"></i>Change photo
                                            </div>
                                        </div>
                                    </label>
                                    <?php $__errorArgs = ['product_image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-danger small mt-2 mb-0"><i
                                    class="ri-error-warning-line me-1"></i><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label-title mb-0">Gallery</label>
                                        <small class="text-muted">Optional · max 4 MB each</small>
                                    </div>
                                    <input type="file" name="gallery_images[]" id="gallery_images"
                                        class="d-none <?php $__errorArgs = ['gallery_images'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> <?php $__errorArgs = ['gallery_images.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        accept="image/*" multiple>
                                    <label for="gallery_images" id="galleryDropZone"
                                        class="img-dropzone img-dropzone--sm <?php $__errorArgs = ['gallery_images'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> has-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> <?php $__errorArgs = ['gallery_images.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> has-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                        <i class="ri-gallery-line"></i>
                                        <div>
                                            <span class="d-block fw-medium" style="font-size:13px">Add gallery images</span>
                                            <small class="text-muted" style="font-size:11px">Click or drag &amp; drop ·
                                                multiple files</small>
                                        </div>
                                    </label>
                                    <?php $__errorArgs = ['gallery_images'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-danger small mt-2 mb-0"><i
                                    class="ri-error-warning-line me-1"></i><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    <?php $__errorArgs = ['gallery_images.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-danger small mt-2 mb-0"><i
                                    class="ri-error-warning-line me-1"></i><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    <div id="galleryPreview" class="gallery-preview-grid mt-3"></div>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-theme px-4">Save product</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <?php
        $__subCatRoutePlaceholder = '999999999';
        $__subCategoriesFetchUrl = route('get-sub-categories', ['category_id' => $__subCatRoutePlaceholder]);
    ?>
    <style>
        /* ── Image upload drop-zones ────────────────────────────────────── */
        .img-dropzone {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 190px;
            border: 2px dashed #d0d7e2;
            border-radius: 18px;
            background: linear-gradient(140deg, #f8f9fb 0%, #f3f4f8 100%);
            cursor: pointer;
            transition: border-color .22s ease, background .22s ease, box-shadow .22s ease, color .22s ease;
            text-align: center;
            padding: 30px 20px;
            position: relative;
            overflow: hidden;
            color: #8e9aaa;
            user-select: none;
            margin-bottom: 0;
        }

        .img-dropzone:hover,
        .img-dropzone.drag-over {
            border-color: #6366f1;
            background: linear-gradient(140deg, #eef2ff 0%, #f0f1ff 100%);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, .1);
            color: #6366f1;
        }

        .img-dropzone.has-error {
            border-color: #f87171;
            background: linear-gradient(140deg, #fff5f5 0%, #fef2f2 100%);
            color: #ef4444;
        }

        .img-dropzone [class*="ri-image-add"],
        .img-dropzone [class*="ri-gallery"] {
            font-size: 40px;
            line-height: 1;
        }

        /* Compact gallery drop zone */
        .img-dropzone--sm {
            min-height: 80px;
            padding: 16px 20px;
            flex-direction: row;
            gap: 14px;
            text-align: left;
        }

        .img-dropzone--sm [class*="ri-"] {
            font-size: 30px;
            flex-shrink: 0;
        }

        /* Thumbnail in-zone preview */
        .dropzone-placeholder {
            pointer-events: none;
        }

        .dropzone-preview {
            position: absolute;
            inset: 0;
        }

        .dropzone-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .dropzone-overlay {
            position: absolute;
            inset: 0;
            background: rgba(10, 10, 30, .46);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .3px;
            opacity: 0;
            transition: opacity .2s;
            backdrop-filter: blur(3px);
        }

        .img-dropzone:hover .dropzone-overlay {
            opacity: 1;
        }

        /* Gallery thumbnail grid */
        .gallery-preview-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .gallery-thumb {
            position: relative;
            width: 78px;
            height: 78px;
            border-radius: 12px;
            overflow: hidden;
            border: 1.5px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
            flex-shrink: 0;
            transition: box-shadow .18s;
        }

        .gallery-thumb:hover {
            box-shadow: 0 4px 14px rgba(0, 0, 0, .14);
        }

        .gallery-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform .22s;
        }

        .gallery-thumb:hover img {
            transform: scale(1.06);
        }

        .gallery-thumb-remove {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: rgba(10, 10, 30, .6);
            color: #fff;
            border: none;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            padding: 0;
            opacity: 0;
            transition: opacity .15s, background .15s;
            backdrop-filter: blur(2px);
            line-height: 1;
        }

        .gallery-thumb:hover .gallery-thumb-remove {
            opacity: 1;
        }

        .gallery-thumb-remove:hover {
            background: #ef4444;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-product-name]').forEach(function (input) {
                input.addEventListener('input', function () {
                    // Strip special characters (keep letters, digits, space and &()-/.,)
                    var val = this.value.replace(/[^a-zA-Z0-9 &()\-\/.,]/g, '');
                    // Collapse any run of the same character exceeding 3
                    val = val.replace(/(.)(\1{3,})/g, '$1$1$1');
                    if (val !== this.value) { this.value = val; }
                });
            });

            var mrpInput = document.getElementById('mrp_price');
            var discountInput = document.getElementById('discount_pct');
            var priceDisplay = document.getElementById('price_display');
            var priceHidden = document.getElementById('price');

            function calcDiscountedPrice() {
                var mrp = parseFloat(mrpInput ? mrpInput.value : '');
                var disc = parseFloat(discountInput ? discountInput.value : '');
                if (Number.isNaN(mrp) || mrp < 0) { return; }
                if (Number.isNaN(disc) || disc < 0) { disc = 0; }
                if (disc > 100) { disc = 100; }
                var finalPrice = Math.round(mrp * (1 - disc / 100) * 100) / 100;
                if (priceDisplay) { priceDisplay.value = finalPrice.toFixed(2); }
                if (priceHidden) { priceHidden.value = finalPrice.toFixed(2); }
            }

            if (mrpInput) { mrpInput.addEventListener('input', calcDiscountedPrice); }
            if (discountInput) { discountInput.addEventListener('input', calcDiscountedPrice); }
            calcDiscountedPrice();

            var wholesaleLabel = <?php echo json_encode(\App\Models\Product::TARGET_WHOLESALER); ?>;
            var segmentSelect = document.getElementById('target_user_type');
            var minWrap = document.getElementById('wholesalerMinQtyWrap');
            var minInput = document.getElementById('min_quantity');
            function syncWholesalerMinQty() {
                if (!segmentSelect || !minWrap || !minInput) return;
                var isWholesale = segmentSelect.value === wholesaleLabel;
                minWrap.classList.toggle('d-none', !isWholesale);
                minInput.required = isWholesale;
                if (!isWholesale) {
                    minInput.value = '';
                }
            }
            if (segmentSelect) {
                segmentSelect.addEventListener('change', syncWholesalerMinQty);
                syncWholesalerMinQty();
            }

            function setOptions(selectEl, items, valueKey, labelKey, placeholder, selectedValue) {
                if (!selectEl) return;
                selectEl.innerHTML = '<option value="">' + placeholder + '</option>';
                var list = Array.isArray(items) ? items : [];
                list.forEach(function (item) {
                    var option = document.createElement('option');
                    option.value = item[valueKey];
                    option.textContent = item[labelKey];
                    if (selectedValue !== '' && selectedValue !== null && selectedValue !== undefined && String(selectedValue) === String(item[valueKey])) {
                        option.selected = true;
                    }
                    selectEl.appendChild(option);
                });
            }

            var subCatRouteTemplate = <?php echo json_encode($__subCategoriesFetchUrl); ?>;
            var subCatRoutePlaceholder = <?php echo json_encode($__subCatRoutePlaceholder); ?>;
            function subCategoriesFetchUrl(categoryId) {
                return String(subCatRouteTemplate).split(String(subCatRoutePlaceholder)).join(encodeURIComponent(String(categoryId)));
            }

            var categoryEl = document.getElementById('category_id');
            var subCategoryEl = document.getElementById('sub_category_id');
            var initialSubCategoryId = <?php echo json_encode(old('sub_category_id', '')); ?>;

            function loadSubCategories(categoryId, selectedSubId) {
                if (!subCategoryEl) return;
                if (!categoryId) {
                    setOptions(subCategoryEl, [], 'sub_category_id', 'sub_cat_name', 'Select sub-category', '');
                    return;
                }
                var pick = (selectedSubId === undefined) ? initialSubCategoryId : selectedSubId;

                fetch(subCategoriesFetchUrl(categoryId), { headers: { Accept: 'application/json' } })
                    .then(function (res) {
                        if (!res.ok) throw new Error('Request failed');
                        return res.json();
                    })
                    .then(function (data) {
                        var rows = Array.isArray(data) ? data : [];
                        setOptions(subCategoryEl, rows, 'sub_category_id', 'sub_cat_name', 'Select sub-category', pick);
                    })
                    .catch(function () {
                        setOptions(subCategoryEl, [], 'sub_category_id', 'sub_cat_name', 'Select sub-category', '');
                    });
            }

            if (categoryEl) {
                categoryEl.addEventListener('change', function () { loadSubCategories(this.value, ''); });
                if (categoryEl.value) {
                    loadSubCategories(categoryEl.value);
                }
            }

            // ── Thumbnail ─────────────────────────────────────────────────
            var thumbInput = document.getElementById('product_image');
            var thumbZone = document.getElementById('thumbDropZone');
            var thumbPlaceholder = document.getElementById('thumbPlaceholder');
            var thumbPreview = document.getElementById('thumbPreview');
            var thumbImg = document.getElementById('thumbPreviewImg');

            function showThumbPreview(file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    thumbImg.src = e.target.result;
                    thumbPlaceholder.classList.add('d-none');
                    thumbPreview.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }

            thumbInput.addEventListener('change', function () {
                if (this.files[0]) showThumbPreview(this.files[0]);
            });

            ['dragenter', 'dragover'].forEach(function (evt) {
                thumbZone.addEventListener(evt, function (e) { e.preventDefault(); thumbZone.classList.add('drag-over'); });
            });
            ['dragleave', 'dragend'].forEach(function (evt) {
                thumbZone.addEventListener(evt, function () { thumbZone.classList.remove('drag-over'); });
            });
            thumbZone.addEventListener('drop', function (e) {
                e.preventDefault();
                thumbZone.classList.remove('drag-over');
                var file = e.dataTransfer.files[0];
                if (!file || !file.type.startsWith('image/')) return;
                var dt = new DataTransfer(); dt.items.add(file);
                thumbInput.files = dt.files;
                showThumbPreview(file);
            });

            // ── Gallery ───────────────────────────────────────────────────
            var galleryInput = document.getElementById('gallery_images');
            var galleryPreview = document.getElementById('galleryPreview');
            var galleryZone = document.getElementById('galleryDropZone');
            var galleryFiles = [];

            function syncGalleryInput() {
                var dt = new DataTransfer();
                galleryFiles.forEach(function (f) { dt.items.add(f); });
                galleryInput.files = dt.files;
            }

            function renderGalleryPreviews() {
                galleryPreview.innerHTML = '';
                galleryFiles.forEach(function (file, idx) {
                    (function (i, f) {
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            var wrap = document.createElement('div');
                            wrap.className = 'gallery-thumb';
                            var img = document.createElement('img');
                            img.src = e.target.result; img.alt = '';
                            var btn = document.createElement('button');
                            btn.type = 'button'; btn.className = 'gallery-thumb-remove';
                            btn.innerHTML = '<i class="ri-close-line"></i>';
                            btn.addEventListener('click', function (ev) {
                                ev.preventDefault(); ev.stopPropagation();
                                galleryFiles.splice(i, 1);
                                renderGalleryPreviews();
                                syncGalleryInput();
                            });
                            wrap.appendChild(img); wrap.appendChild(btn);
                            galleryPreview.appendChild(wrap);
                        };
                        reader.readAsDataURL(f);
                    })(idx, file);
                });
            }

            galleryInput.addEventListener('change', function () {
                Array.prototype.forEach.call(this.files, function (f) { galleryFiles.push(f); });
                renderGalleryPreviews();
                syncGalleryInput();
            });

            ['dragenter', 'dragover'].forEach(function (evt) {
                galleryZone.addEventListener(evt, function (e) { e.preventDefault(); galleryZone.classList.add('drag-over'); });
            });
            ['dragleave', 'dragend'].forEach(function (evt) {
                galleryZone.addEventListener(evt, function () { galleryZone.classList.remove('drag-over'); });
            });
            galleryZone.addEventListener('drop', function (e) {
                e.preventDefault();
                galleryZone.classList.remove('drag-over');
                Array.prototype.forEach.call(e.dataTransfer.files, function (f) {
                    if (f.type.startsWith('image/')) galleryFiles.push(f);
                });
                renderGalleryPreviews();
                syncGalleryInput();
            });

            /* ── Description character counter ─────────────────────────── */
            (function () {
                var textarea = document.getElementById('product_description');
                var counter = document.getElementById('desc_count');
                if (!textarea || !counter) return;
                textarea.addEventListener('input', function () {
                    counter.textContent = textarea.value.length;
                    counter.style.color = textarea.value.length >= 220 ? '#dc3545' : '';
                });
            })();
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/developmentalpha/public_html/swastik-food-machinery.developmentalphawizz.com/resources/views/admin/products/addProduct.blade.php ENDPATH**/ ?>