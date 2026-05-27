<?php $__env->startSection('content'); ?>
<div class="page-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="title-header option-title d-flex align-items-center mb-4">
                    <h5><i class="ri-user-settings-line me-2"></i><?php echo e($title); ?></h5>
                    <div class="ms-auto d-flex gap-2">
                        <a href="<?php echo e(route('admin.view-customer', urlencode(Crypt::encrypt($customerDetails->customer_id)))); ?>" class="btn btn-theme btn-sm">
                            <i class="ri-eye-line me-1"></i>View Profile
                        </a>
                        <a href="<?php echo e(route('admin.customers')); ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="ri-arrow-left-line me-1"></i>Back
                        </a>
                    </div>
                </div>

                <?php if($errors->any()): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ri-error-warning-line me-2"></i>Please fix the highlighted fields and try again.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if(!empty($hasApproval) && strtolower((string) ($customerDetails->approval_status ?? '')) === 'pending'): ?>
                    <div class="alert alert-warning d-flex align-items-start gap-2" role="alert">
                        <i class="ri-time-line fs-5"></i>
                        <div>
                            <strong>Registration pending.</strong> Approve or reject this customer from the customer list or their profile page before the account can be activated.
                        </div>
                    </div>
                <?php endif; ?>

                <?php
                    $segmentEdit = is_string($customerDetails->user_type ?? null) ? trim($customerDetails->user_type) : '';
                    $isWholesalerEdit = strcasecmp($segmentEdit, \App\Models\Product::TARGET_WHOLESALER) === 0;
                ?>

                <div class="card customer-form-card">
                    <div class="card-body">

                        <form class="theme-form theme-form-2 mega-form" action="<?php echo e(route('admin.update-customer')); ?>" method="post" id="editCustomerForm" enctype="multipart/form-data" novalidate>
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="customer_id" value="<?php echo e($customerDetails->customer_id); ?>">

                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <div class="field-box">
                                        <label class="form-label-title mb-2">Profile Image</label>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?php echo e($customerDetails->profile_image ? asset('public/uploads/customers/' . $customerDetails->profile_image) : asset('public/uploads/customers/customer.png')); ?>" alt="Profile Image" class="rounded-circle" width="60" height="60" style="object-fit:cover;">
                                            <input type="file" name="profile_image" accept="image/*" class="form-control" style="max-width:220px;">
                                        </div>
                                        <?php $__errorArgs = ['profile_image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="invalid-feedback d-block mb-0"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="field-box">
                                        <label class="form-label-title mb-2">Customer Name <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-user-line"></i></span>
                                            <input class="form-control <?php $__errorArgs = ['customer_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="customer_name" type="text" value="<?php echo e(old('customer_name', $customerDetails->name)); ?>" placeholder="Enter full customer name" maxlength="40" pattern="[A-Za-z ]+" data-alpha-name data-label="Customer Name" autocomplete="name">
                                        </div>
                                        <?php $__errorArgs = ['customer_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="invalid-feedback d-block mb-0"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="field-box">
                                        <label class="form-label-title mb-2">Email <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-mail-line"></i></span>
                                            <input class="form-control <?php $__errorArgs = ['customer_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="customer_email" type="email" value="<?php echo e(old('customer_email', $customerDetails->email)); ?>" readonly>
                                        </div>
                                        <?php $__errorArgs = ['customer_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="invalid-feedback d-block mb-0"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="field-box">
                                        <label class="form-label-title mb-2">Phone Number <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-phone-line"></i></span>
                                            <input class="form-control <?php $__errorArgs = ['customer_phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="customer_phone" type="text" maxlength="10" inputmode="numeric" pattern="[0-9]{10}" value="<?php echo e(old('customer_phone', $customerDetails->mobile)); ?>" placeholder="10-digit phone" readonly>
                                        </div>
                                        <?php $__errorArgs = ['customer_phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="invalid-feedback d-block mb-0"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="field-box dob-picker-box">
                                        <label class="form-label-title mb-2">Date of Birth</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-cake-2-line"></i></span>
                                            <input class="form-control <?php $__errorArgs = ['customer_dob'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="customerDobInput" name="customer_dob" type="date" max="<?php echo e(now()->subYears(10)->format('Y-m-d')); ?>" value="<?php echo e(old('customer_dob', $customerDetails->dob)); ?>">
                                        </div>
                                        <?php $__errorArgs = ['customer_dob'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="invalid-feedback d-block mb-0"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="field-box">
                                        <label class="form-label-title mb-2">Gender</label>
                                        <select class="form-select <?php $__errorArgs = ['gender'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="gender">
                                            <option value="">Select gender</option>
                                            <option value="Male" <?php echo e(old('gender', $customerDetails->gender) === 'Male' ? 'selected' : ''); ?>>Male</option>
                                            <option value="Female" <?php echo e(old('gender', $customerDetails->gender) === 'Female' ? 'selected' : ''); ?>>Female</option>
                                            <option value="Other" <?php echo e(old('gender', $customerDetails->gender) === 'Other' ? 'selected' : ''); ?>>Other</option>
                                        </select>
                                        <?php $__errorArgs = ['gender'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="invalid-feedback d-block mb-0"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>

                                <?php if($isWholesalerEdit): ?>
                                    <div class="col-lg-6">
                                        <div class="field-box">
                                            <label class="form-label-title mb-2">GST number</label>
                                            <input type="text" class="form-control bg-light" value="<?php echo e(trim((string) ($customerDetails->gst_number ?? '')) !== '' ? $customerDetails->gst_number : '—'); ?>" readonly tabindex="-1">
                                            <small class="text-muted">Captured at registration. Update from the customer app or support tools if your flow allows it.</small>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="field-box">
                                            <label class="form-label-title mb-2">GST verification</label>
                                            <p class="mb-0 fw-500">
                                                <?php if(!empty($hasGstVerified) && !empty($customerDetails->gst_verified_at)): ?>
                                                    Verified on <?php echo e(\Carbon\Carbon::parse($customerDetails->gst_verified_at)->format('d M Y, h:i A')); ?>

                                                <?php elseif(!empty($hasGstVerified)): ?>
                                                    <?php if(trim((string) ($customerDetails->gst_number ?? '')) !== ''): ?>
                                                        Not verified yet — open <strong>View profile</strong> to mark as verified.
                                                    <?php else: ?>
                                                        —
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    —
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="col-lg-6">
                                    <div class="field-box">
                                        <label class="form-label-title mb-2">Account active</label>
                                        <?php if(!empty($hasApproval) && strtolower((string) ($customerDetails->approval_status ?? '')) === 'pending'): ?>
                                            <p class="text-muted small mb-0">Not available until registration is approved.</p>
                                        <?php else: ?>
                                            <div class="d-flex align-items-center status-toggle-wrap">
                                                <label class="switch mb-0 me-2">
                                                    <input type="checkbox" name="customer_status" value="1" <?php echo e(old('customer_status', $customerDetails->status == 1 ? '1' : '') ? 'checked' : ''); ?>>
                                                    <span class="switch-state"></span>
                                                </label>
                                                <span class="text-muted">Account is active</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="field-box">
                                        <label class="form-label-title mb-2">Address <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-map-pin-line"></i></span>
                                            <textarea class="form-control <?php $__errorArgs = ['customer_address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="customer_address" rows="3" maxlength="255" placeholder="Complete customer address" data-address-field><?php echo e(old('customer_address', $customerDetails->customer_address)); ?></textarea>
                                        </div>
                                        <?php $__errorArgs = ['customer_address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="invalid-feedback d-block mb-0"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="<?php echo e(route('admin.customers')); ?>" class="btn btn-outline-secondary">Cancel</a>
                                <button class="btn btn-theme px-4" type="submit"><i class="ri-save-line me-1"></i>Update Customer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<style>
.customer-form-card {
    border: none;
    border-radius: 14px;
    background: linear-gradient(180deg, rgba(13,164,135,.06), #fff 40%);
}
.field-box {
    background: #f8fafb;
    border: 1px solid #eef3f4;
    border-radius: 10px;
    padding: 14px;
    height: 100%;
}
.status-toggle-wrap {
    min-height: 42px;
}
.dob-picker-box {
    cursor: pointer;
}
.dob-picker-box input[type="date"] {
    cursor: pointer;
}
/* Prevent Bootstrap's validation icon from overlapping the browser's native calendar icon */
input[type="date"].is-invalid,
input[type="date"].is-valid {
    background-image: none;
    padding-right: .75rem;
}
</style>
<script>
$(function () {
    $('[data-alpha-name]').on('input', function () {
        var cleanedValue = this.value.replace(/[^A-Za-z ]+/g, '').replace(/\s{2,}/g, ' ');
        if (cleanedValue !== this.value) {
            this.value = cleanedValue;
        }
    });

    $('[data-address-field]').on('input', function () {
        var cleanedValue = this.value.replace(/(.)\1{5,}/g, '$1$1$1$1$1');
        if (cleanedValue !== this.value) {
            this.value = cleanedValue;
        }
    });

    var $dobFieldBox = $('.dob-picker-box');

    $dobFieldBox.on('click', function () {
        var input = $(this).find('input[type="date"]').get(0);
        if (!input) {
            return;
        }

        input.focus();
        if (typeof input.showPicker === 'function') {
            input.showPicker();
        }
    });
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/developmentalpha/public_html/swastik-food-machinery.developmentalphawizz.com/resources/views/admin/customers/editCustomer.blade.php ENDPATH**/ ?>