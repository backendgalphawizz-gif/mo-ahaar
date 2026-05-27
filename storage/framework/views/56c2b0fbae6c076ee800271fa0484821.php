<?php $__env->startSection('content'); ?>
<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title mb-4 d-flex align-items-center">
            <h5><i class="ri-notification-3-line me-2"></i><?php echo e($title); ?></h5>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-checkbox-circle-line me-2"></i><?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Please fix the highlighted fields and submit again.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-xl-5">
                <div class="card">
                    <div class="card-header card-header-2">
                        <h5>Send Notification</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?php echo e(route('admin.notifications.store')); ?>" class="row g-3" id="notificationForm">
                            <?php echo csrf_field(); ?>

                            <div class="col-12">
                                <label class="form-label">Send To <span class="text-danger">*</span></label>
                                <select name="target_type" id="target_type" class="form-select <?php $__errorArgs = ['target_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                    <option value="">Select Recipient Type</option>
                                    <option value="users" <?php echo e(old('target_type') === 'users' ? 'selected' : ''); ?>>Users</option>
                                    <option value="vendors" <?php echo e(old('target_type') === 'vendors' ? 'selected' : ''); ?>>Vendors</option>
                                </select>
                                <?php $__errorArgs = ['target_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Recipient Scope <span class="text-danger">*</span></label>
                                <select name="recipient_scope" id="recipient_scope" class="form-select <?php $__errorArgs = ['recipient_scope'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                    <option value="all" <?php echo e(old('recipient_scope', 'all') === 'all' ? 'selected' : ''); ?>>All</option>
                                    <option value="specific" <?php echo e(old('recipient_scope') === 'specific' ? 'selected' : ''); ?>>Specific</option>
                                </select>
                                <?php $__errorArgs = ['recipient_scope'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-12 d-none" id="recipientWrap">
                                <label class="form-label">Select Recipient <span class="text-danger">*</span></label>
                                <select name="recipient_id" id="recipient_id" class="form-select <?php $__errorArgs = ['recipient_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="">Select Recipient</option>
                                </select>
                                <?php $__errorArgs = ['recipient_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('title')); ?>" maxlength="190" required>
                                <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Message <span class="text-danger">*</span></label>
                                <textarea name="message" rows="6" class="form-control <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" maxlength="5000" required><?php echo e(old('message')); ?></textarea>
                                <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-theme">Send Notification</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-7">
                <div class="card card-table">
                    <div class="card-header card-header-2 d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Notification History</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table all-package theme-table align-middle">
                                <thead>
                                    <tr>
                                        <th>S.No.</th>
                                        <th>Audience</th>
                                        <th>Recipient</th>
                                        <th>Title</th>
                                        <th>Details</th>
                                        <th>Sent At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td><?php echo e($loop->iteration); ?></td>
                                            <td>
                                                <?php if($notification->target_type === 'users'): ?>
                                                    <span class="badge badge-soft-success">Users</span>
                                                <?php elseif($notification->target_type === 'vendors'): ?>
                                                    <span class="badge badge-soft-warning">Vendors</span>
                                                <?php else: ?>
                                                    <span class="badge badge-soft-info">Delivery Partners</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo e($notification->recipient_name ?: '-'); ?></td>
                                            <td><?php echo e($notification->title); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary view-notification-btn" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#notificationDetailModal"
                                                    data-title="<?php echo e($notification->title); ?>"
                                                    data-message="<?php echo e(htmlentities($notification->message)); ?>"
                                                    data-audience="<?php echo e($notification->target_type === 'users' ? 'Users' : ($notification->target_type === 'vendors' ? 'Vendors' : 'Other')); ?>"
                                                    data-recipient="<?php echo e($notification->recipient_name ?: '-'); ?>"
                                                    data-date="<?php echo e(optional($notification->created_at)->format('d M Y, h:i A')); ?>"
                                                >View</button>
                                            </td>
                                            <!-- Notification Detail Modal -->
                                            <div class="modal fade" id="notificationDetailModal" tabindex="-1" aria-labelledby="notificationDetailModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                                    <div class="modal-content border border-2 rounded-3 shadow">
                                                        <div class="modal-header border-bottom border-2">
                                                            <h5 class="modal-title" id="notificationDetailModalLabel">Notification Details</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body p-4">
                                                            <div class="row mb-3 pb-2 border-bottom">
                                                                <div class="col-sm-3 fw-bold">Title</div>
                                                                <div class="col-sm-9" id="notif-title"></div>
                                                            </div>
                                                            <div class="row mb-3 pb-2 border-bottom">
                                                                <div class="col-sm-3 fw-bold">Audience</div>
                                                                <div class="col-sm-9" id="notif-audience"></div>
                                                            </div>
                                                            <div class="row mb-3 pb-2 border-bottom">
                                                                <div class="col-sm-3 fw-bold">Recipient</div>
                                                                <div class="col-sm-9" id="notif-recipient"></div>
                                                            </div>
                                                            <div class="row mb-3 pb-2 border-bottom">
                                                                <div class="col-sm-3 fw-bold">Message</div>
                                                                <div class="col-sm-9" id="notif-message"></div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-sm-3 fw-bold">Sent At</div>
                                                                <div class="col-sm-9" id="notif-date"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <td><?php echo e(optional($notification->created_at)->format('d M Y, h:i A')); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">No notifications sent yet.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <?php echo e($notifications->links()); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.view-notification-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('notif-title').textContent = btn.getAttribute('data-title');
            document.getElementById('notif-audience').textContent = btn.getAttribute('data-audience');
            document.getElementById('notif-recipient').textContent = btn.getAttribute('data-recipient');
            document.getElementById('notif-message').innerHTML = btn.getAttribute('data-message');
            document.getElementById('notif-date').textContent = btn.getAttribute('data-date');
        });
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const targetType = document.getElementById('target_type');
    const recipientScope = document.getElementById('recipient_scope');
    const recipientWrap = document.getElementById('recipientWrap');
    const recipientSelect = document.getElementById('recipient_id');
    const oldRecipientId = "<?php echo e(old('recipient_id')); ?>";

    function isSpecificScope() {
        return recipientScope.value === 'specific';
    }

    function resetRecipientSelect() {
        recipientSelect.innerHTML = '<option value="">Select Recipient</option>';
    }

    function toggleRecipient() {
        if (isSpecificScope()) {
            recipientWrap.classList.remove('d-none');
            recipientSelect.setAttribute('required', 'required');
            loadRecipients();
        } else {
            recipientWrap.classList.add('d-none');
            recipientSelect.removeAttribute('required');
            resetRecipientSelect();
        }
    }

    async function loadRecipients() {
        const type = targetType.value;
        resetRecipientSelect();

        if (!type || !isSpecificScope()) {
            return;
        }

        try {
            const response = await fetch("<?php echo e(route('admin.notifications.recipients')); ?>?type=" + encodeURIComponent(type));
            const data = await response.json();

            (data || []).forEach(function (item) {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.label;
                if (String(oldRecipientId) === String(item.id)) {
                    option.selected = true;
                }
                recipientSelect.appendChild(option);
            });
        } catch (e) {
            console.error('Unable to load recipients', e);
        }
    }

    targetType.addEventListener('change', loadRecipients);
    recipientScope.addEventListener('change', toggleRecipient);

    toggleRecipient();
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/developmentalpha/public_html/swastik-food-machinery.developmentalphawizz.com/resources/views/admin/notifications/index.blade.php ENDPATH**/ ?>