
<?php
    $isEdit   = isset($discountOffer);
    $oldApply = old('apply_to', $isEdit ? $discountOffer->apply_to : 'all');

    $selectedProducts   = old('product_ids',  $isEdit ? (array)($discountOffer->product_ids ?? [])  : []);
    $selectedCategories = old('category_ids', $isEdit ? (array)($discountOffer->category_ids ?? []) : []);
?>


<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent fw-semibold">Basic Information</div>
    <div class="card-body">

        <div class="mb-3">
            <label class="form-label-title">Offer Title <span class="text-danger">*</span></label>
            <input type="text" name="title"
                   class="form-control <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                   placeholder="e.g. Summer Sale 20% Off"
                   value="<?php echo e(old('title', $isEdit ? $discountOffer->title : '')); ?>"
                   maxlength="150" required>
            <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="mb-3">
            <label class="form-label-title">Description</label>
            <textarea name="description"
                      class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                      rows="2"
                      placeholder="Optional: internal notes about this offer"><?php echo e(old('description', $isEdit ? $discountOffer->description : '')); ?></textarea>
            <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label-title">Discount Type <span class="text-danger">*</span></label>
                <select name="discount_type" id="discountType"
                        class="form-select <?php $__errorArgs = ['discount_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                    <option value="percentage" <?php echo e(old('discount_type', $isEdit ? $discountOffer->discount_type : 'percentage') === 'percentage' ? 'selected' : ''); ?>>
                        Percentage (%)
                    </option>
                    <option value="fixed" <?php echo e(old('discount_type', $isEdit ? $discountOffer->discount_type : '') === 'fixed' ? 'selected' : ''); ?>>
                        Fixed Amount (₹)
                    </option>
                </select>
                <?php $__errorArgs = ['discount_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="col-md-6">
                <label class="form-label-title">Discount Value <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text" id="discountSymbol">%</span>
                    <input type="number" name="discount_value" id="discountValue" step="0.01" min="0.01"
                           class="form-control <?php $__errorArgs = ['discount_value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           placeholder="e.g. 10"
                           value="<?php echo e(old('discount_value', $isEdit ? $discountOffer->discount_value : '')); ?>"
                           required>
                    <?php $__errorArgs = ['discount_value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <small class="text-muted" id="discountHint">Enter a percentage (0–100).</small>
            </div>
        </div>

        <div class="mt-3">
            <label class="form-label-title">Status <span class="text-danger">*</span></label>
            <select name="is_active" class="form-select <?php $__errorArgs = ['is_active'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                <option value="1" <?php echo e(old('is_active', $isEdit ? (string)$discountOffer->is_active : '1') === '1' ? 'selected' : ''); ?>>Active</option>
                <option value="0" <?php echo e(old('is_active', $isEdit ? (string)$discountOffer->is_active : '1') === '0' ? 'selected' : ''); ?>>Inactive</option>
            </select>
            <?php $__errorArgs = ['is_active'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

    </div>
</div>


<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent fw-semibold">Apply Offer To</div>
    <div class="card-body">

        <div class="mb-3">
            <label class="form-label-title">Scope <span class="text-danger">*</span></label>
            <select name="apply_to" id="applyTo"
                    class="form-select <?php $__errorArgs = ['apply_to'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                <option value="all" <?php echo e($oldApply === 'all' ? 'selected' : ''); ?>>All Products</option>
                <option value="specific_products" <?php echo e($oldApply === 'specific_products' ? 'selected' : ''); ?>>Specific Products</option>
                <option value="specific_categories" <?php echo e($oldApply === 'specific_categories' ? 'selected' : ''); ?>>Specific Categories</option>
            </select>
            <?php $__errorArgs = ['apply_to'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        
        <div id="productPickerWrap" class="<?php echo e($oldApply === 'specific_products' ? '' : 'd-none'); ?>">
            <label class="form-label-title">Select Products</label>
            <select name="product_ids[]" id="productPicker"
                    class="form-select <?php $__errorArgs = ['product_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    multiple size="8">
                <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($product->product_id); ?>"
                        <?php echo e(in_array($product->product_id, $selectedProducts) ? 'selected' : ''); ?>>
                        <?php echo e($product->product_name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <small class="text-muted">Hold Ctrl / Cmd to select multiple products.</small>
            <?php $__errorArgs = ['product_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small mt-1"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        
        <div id="categoryPickerWrap" class="<?php echo e($oldApply === 'specific_categories' ? '' : 'd-none'); ?>">
            <label class="form-label-title">Select Categories</label>
            <select name="category_ids[]" id="categoryPicker"
                    class="form-select <?php $__errorArgs = ['category_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    multiple size="6">
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($cat->category_id); ?>"
                        <?php echo e(in_array($cat->category_id, $selectedCategories) ? 'selected' : ''); ?>>
                        <?php echo e($cat->category_name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <small class="text-muted">Hold Ctrl / Cmd to select multiple categories.</small>
            <?php $__errorArgs = ['category_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small mt-1"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

    </div>
</div>


<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent fw-semibold">Validity Period</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label-title">Valid From</label>
                <input type="date" name="valid_from"
                       class="form-control <?php $__errorArgs = ['valid_from'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                       value="<?php echo e(old('valid_from', $isEdit && $discountOffer->valid_from ? $discountOffer->valid_from->format('Y-m-d') : '')); ?>">
                <small class="text-muted">Leave blank for no start restriction.</small>
                <?php $__errorArgs = ['valid_from'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="col-md-6">
                <label class="form-label-title">Valid Until</label>
                <input type="date" name="valid_until"
                       class="form-control <?php $__errorArgs = ['valid_until'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                       value="<?php echo e(old('valid_until', $isEdit && $discountOffer->valid_until ? $discountOffer->valid_until->format('Y-m-d') : '')); ?>">
                <small class="text-muted">Leave blank for no end restriction.</small>
                <?php $__errorArgs = ['valid_until'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>
    </div>
</div>


<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent fw-semibold">Conditions <small class="text-muted fw-normal">(all fields optional)</small></div>
    <div class="card-body">

        <h6 class="text-muted mb-2 small text-uppercase">Quantity per Line Item</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label-title">Minimum Quantity</label>
                <input type="number" name="min_quantity" min="1"
                       class="form-control <?php $__errorArgs = ['min_quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                       placeholder="e.g. 2"
                       value="<?php echo e(old('min_quantity', $isEdit ? $discountOffer->min_quantity : '')); ?>">
                <small class="text-muted">Offer applies only if the item quantity ≥ this value.</small>
                <?php $__errorArgs = ['min_quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="col-md-6">
                <label class="form-label-title">Maximum Quantity</label>
                <input type="number" name="max_quantity" min="1"
                       class="form-control <?php $__errorArgs = ['max_quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                       placeholder="e.g. 100"
                       value="<?php echo e(old('max_quantity', $isEdit ? $discountOffer->max_quantity : '')); ?>">
                <small class="text-muted">Offer applies only if the item quantity ≤ this value.</small>
                <?php $__errorArgs = ['max_quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>

        <h6 class="text-muted mb-2 small text-uppercase">Cart Total Amount</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label-title">Minimum Cart Amount (₹)</label>
                <input type="number" name="min_cart_amount" step="0.01" min="0"
                       class="form-control <?php $__errorArgs = ['min_cart_amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                       placeholder="e.g. 500"
                       value="<?php echo e(old('min_cart_amount', $isEdit ? $discountOffer->min_cart_amount : '')); ?>">
                <small class="text-muted">Offer applies only if cart subtotal ≥ this amount.</small>
                <?php $__errorArgs = ['min_cart_amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="col-md-6">
                <label class="form-label-title">Maximum Cart Amount (₹)</label>
                <input type="number" name="max_cart_amount" step="0.01" min="0"
                       class="form-control <?php $__errorArgs = ['max_cart_amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                       placeholder="e.g. 5000"
                       value="<?php echo e(old('max_cart_amount', $isEdit ? $discountOffer->max_cart_amount : '')); ?>">
                <small class="text-muted">Offer applies only if cart subtotal ≤ this amount.</small>
                <?php $__errorArgs = ['max_cart_amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>

    </div>
</div>
<?php /**PATH /home/developmentalpha/public_html/swastik-food-machinery.developmentalphawizz.com/resources/views/admin/discount-offers/_form.blade.php ENDPATH**/ ?>