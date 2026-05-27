<?php $__env->startSection('content'); ?>
<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title d-flex align-items-center flex-wrap gap-2 mb-4">
            <h5 class="mb-0">Add GST Tax</h5>
            <a class="btn btn-outline-secondary btn-sm ms-auto" href="<?php echo e(route('admin.gst-taxes.index')); ?>">
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

        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form method="POST" action="<?php echo e(route('admin.gst-taxes.store')); ?>">
                            <?php echo csrf_field(); ?>

                            <div class="mb-3">
                                <label class="form-label-title">GST Name <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                       class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       placeholder="e.g. GST 18%"
                                       value="<?php echo e(old('name')); ?>"
                                       maxlength="100"
                                       required>
                                <small class="text-muted">Use a clear label like "GST 5%", "GST 12%", etc.</small>
                                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label-title">Percentage (%) <span class="text-danger">*</span></label>
                                <input type="number" name="percentage" step="0.01" min="0" max="100"
                                       class="form-control <?php $__errorArgs = ['percentage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       placeholder="e.g. 18.00"
                                       value="<?php echo e(old('percentage')); ?>"
                                       required>
                                <small class="text-muted">Enter a value between 0 and 100. Up to 2 decimal places.</small>
                                <?php $__errorArgs = ['percentage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="mb-4">
                                <label class="form-label-title">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                    <option value="1" <?php echo e(old('status', '1') === '1' ? 'selected' : ''); ?>>Active</option>
                                    <option value="0" <?php echo e(old('status') === '0' ? 'selected' : ''); ?>>Inactive</option>
                                </select>
                                <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-theme px-4">Save</button>
                                <a href="<?php echo e(route('admin.gst-taxes.index')); ?>" class="btn btn-outline-secondary px-4">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/developmentalpha/public_html/swastik-food-machinery.developmentalphawizz.com/resources/views/admin/gst-taxes/create.blade.php ENDPATH**/ ?>