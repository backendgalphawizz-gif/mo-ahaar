<?php $__env->startSection('content'); ?>
<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title d-flex align-items-center flex-wrap gap-2 mb-4">
            <h5 class="mb-0"><i class="ri-price-tag-3-line me-2"></i>Add Discount Offer</h5>
            <a class="btn btn-outline-secondary btn-sm ms-auto" href="<?php echo e(route('admin.discount-offers.index')); ?>">
                <i class="ri-arrow-left-line me-1"></i>Back to list
            </a>
        </div>

        <?php if($errors->any()): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="fw-semibold mb-1">Please fix the errors below.</div>
                <ul class="mb-0 ps-3">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('admin.discount-offers.store')); ?>">
            <?php echo csrf_field(); ?>
            <?php echo $__env->make('admin.discount-offers._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-theme px-4">Save Offer</button>
                <a href="<?php echo e(route('admin.discount-offers.index')); ?>" class="btn btn-outline-secondary px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
(function () {
    var applyTo   = document.getElementById('applyTo');
    var prodWrap  = document.getElementById('productPickerWrap');
    var catWrap   = document.getElementById('categoryPickerWrap');
    var discType  = document.getElementById('discountType');
    var discSym   = document.getElementById('discountSymbol');
    var discHint  = document.getElementById('discountHint');
    var discInput = document.getElementById('discountValue');

    function updateApply() {
        var v = applyTo.value;
        prodWrap.classList.toggle('d-none', v !== 'specific_products');
        catWrap.classList.toggle('d-none', v !== 'specific_categories');
        document.getElementById('productPicker').disabled  = (v !== 'specific_products');
        document.getElementById('categoryPicker').disabled = (v !== 'specific_categories');
    }

    function updateDiscountUI() {
        if (discType.value === 'percentage') {
            discSym.textContent = '%';
            discHint.textContent = 'Enter a percentage (0–100).';
            discInput.max = '100';
        } else {
            discSym.textContent = '₹';
            discHint.textContent = 'Enter a fixed rupee amount.';
            discInput.removeAttribute('max');
        }
    }

    applyTo.addEventListener('change', updateApply);
    discType.addEventListener('change', updateDiscountUI);
    updateApply();
    updateDiscountUI();
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/developmentalpha/public_html/swastik-food-machinery.developmentalphawizz.com/resources/views/admin/discount-offers/create.blade.php ENDPATH**/ ?>