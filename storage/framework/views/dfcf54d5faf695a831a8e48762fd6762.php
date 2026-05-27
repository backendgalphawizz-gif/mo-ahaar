<?php $__env->startSection('content'); ?>

<style>
    .form-switch .form-check-input{
        width: 46px !important;
        height: 24px;
        border-radius: 24px;
        background-color: #d4d7dd !important;
        border: none;
    }
    .form-switch .form-check-input:checked {
        background-color: #c9973a !important;
        border-color: #c9973a !important;

        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3E%3Ccircle r='3' fill='white'/%3E%3C/svg%3E"),
        linear-gradient(
            135deg,
            #b8872b 0%,
            #c9973a 50%,
            #e0b45a 100%
        ) !important;
    }
    .form-switch .form-check-input:focus{
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3E%3Ccircle r='3' fill='white'/%3E%3C/svg%3E") !important;}
    .form-check-input:focus{
        box-shadow: unset !important;

    }
</style>

<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title d-flex align-items-center mb-4">
            <h5><i class="ri-bank-card-line me-2"></i><?php echo e($title); ?></h5>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('admin.settings.payment-methods.update')); ?>" id="paymentMethodsForm" novalidate>
            <?php echo csrf_field(); ?>

            
            <?php $rz = $gateways->get('razorpay'); $rzSettings = $rz->settings ?? []; ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="gateway-icon razorpay-icon">
                            <svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="42" height="42" rx="8" fill="#072654"/>
                                <path d="M13 29L22 13L31 29H26.5L22 21.2L17.5 29H13Z" fill="#3395FF"/>
                            </svg>
                        </span>
                        <h3 class="mb-0 fw-semibold">Razorpay</h3>
                    </div>
                    <div class="form-check form-switch mb-0 d-flex align-items-center gap-2">
                        
                        <input class="form-check-input gateway-toggle m-0" type="checkbox"
                               id="razorpay_enabled"
                               name="gateways[razorpay][is_enabled]"
                               value="1"
                               <?php echo e($rz && $rz->is_enabled ? 'checked' : ''); ?>>
                                                                                           
                        <label class="form-check-label mb-0" for="razorpay_enabled">
                            <?php echo e($rz && $rz->is_enabled ? 'Enabled' : 'Disabled'); ?>

                        </label>
                    </div>
                </div>
                <div class="card-body gateway-fields <?php echo e($rz && $rz->is_enabled ? '' : 'opacity-50'); ?>" id="razorpay_fields">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Razorpay Key ID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php $__errorArgs = ['gateways.razorpay.settings.key_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   name="gateways[razorpay][settings][key_id]"
                                   value="<?php echo e(old('gateways.razorpay.settings.key_id', $rzSettings['key_id'] ?? '')); ?>"
                                   placeholder="rzp_live_xxxxxxxxxxxxxxxxxx">
                            <small class="text-muted">Your Razorpay Key ID from the dashboard.</small>
                            <?php $__errorArgs = ['gateways.razorpay.settings.key_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Secret Key <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control <?php $__errorArgs = ['gateways.razorpay.settings.secret_key'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="razorpay_secret_key"
                                       name="gateways[razorpay][settings][secret_key]"
                                       value="<?php echo e(old('gateways.razorpay.settings.secret_key', $rzSettings['secret_key'] ?? '')); ?>"
                                       placeholder="Enter Razorpay Secret Key">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="razorpay_secret_key">
                                    <i class="ri-eye-line"></i>
                                </button>
                            </div>
                            <small class="text-muted">Keep this key confidential.</small>
                            <?php $__errorArgs = ['gateways.razorpay.settings.secret_key'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Webhook Secret</label>
                            <div class="input-group">
                                <input type="password" class="form-control <?php $__errorArgs = ['gateways.razorpay.settings.webhook_secret'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="razorpay_webhook_secret"
                                       name="gateways[razorpay][settings][webhook_secret]"
                                       value="<?php echo e(old('gateways.razorpay.settings.webhook_secret', $rzSettings['webhook_secret'] ?? '')); ?>"
                                       placeholder="Enter Webhook Secret (optional)">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="razorpay_webhook_secret">
                                    <i class="ri-eye-line"></i>
                                </button>
                            </div>
                            <small class="text-muted">Used to verify Razorpay webhook signatures.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-theme px-4">
                    <i class="ri-save-line me-1"></i> Save Payment Settings
                </button>
            </div>

        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Toggle enable/disable label + opacity ────────────────────────────────
    document.querySelectorAll('.gateway-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            var gatewaySlug = this.id.replace('_enabled', '');
            var fieldsDiv   = document.getElementById(gatewaySlug + '_fields');
            var label       = this.nextElementSibling;

            if (this.checked) {
                label.textContent = 'Enabled';
                if (fieldsDiv) fieldsDiv.classList.remove('opacity-50');
            } else {
                label.textContent = 'Disabled';
                if (fieldsDiv) fieldsDiv.classList.add('opacity-50');
            }
        });
    });

    // ── Show / hide password fields ──────────────────────────────────────────
    document.querySelectorAll('.toggle-password').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetId = this.getAttribute('data-target');
            var input    = document.getElementById(targetId);
            var icon     = this.querySelector('i');

            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('ri-eye-line', 'ri-eye-off-line');
            } else {
                input.type = 'password';
                icon.classList.replace('ri-eye-off-line', 'ri-eye-line');
            }
        });
    });

    // ── Amount inputs: allow only non-negative numbers ───────────────────────
    document.querySelectorAll('.amount-input').forEach(function (input) {
        input.addEventListener('input', function () {
            if (parseFloat(this.value) < 0) {
                this.value = '';
            }
        });
    });

    // ── Client-side validation: if gateway is enabled, key fields must have values ──
    document.getElementById('paymentMethodsForm').addEventListener('submit', function (e) {
        var errors = [];

        // Razorpay
        var rzEnabled = document.getElementById('razorpay_enabled').checked;
        if (rzEnabled) {
            var rzKeyId    = document.querySelector('[name="gateways[razorpay][settings][key_id]"]');
            var rzSecret   = document.querySelector('[name="gateways[razorpay][settings][secret_key]"]');
            if (!rzKeyId.value.trim()) errors.push('Razorpay: Key ID is required when enabled.');
            if (!rzSecret.value.trim()) errors.push('Razorpay: Secret Key is required when enabled.');
        }

        if (errors.length > 0) {
            e.preventDefault();
            var existingAlert = document.getElementById('paymentValidationAlert');
            if (existingAlert) existingAlert.remove();

            var alert = document.createElement('div');
            alert.id         = 'paymentValidationAlert';
            alert.className  = 'alert alert-danger alert-dismissible fade show mb-4';
            alert.innerHTML  = '<strong>Please fix the following:</strong><ul class="mb-0 mt-1">'
                + errors.map(function (e) { return '<li>' + e + '</li>'; }).join('')
                + '</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button>';

            document.getElementById('paymentMethodsForm').insertBefore(alert, document.getElementById('paymentMethodsForm').firstChild);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/developmentalpha/public_html/swastik-food-machinery.developmentalphawizz.com/resources/views/admin/settings/payment-methods.blade.php ENDPATH**/ ?>