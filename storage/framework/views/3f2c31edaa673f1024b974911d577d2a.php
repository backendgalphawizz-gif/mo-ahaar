<?php $__env->startSection('content'); ?>
    <div class="page-body">
        <div class="container-fluid">
            <div class="title-header option-title d-flex align-items-center mb-4">
                <h5><i class="ri-user-settings-line me-2"></i>Profile Setting</h5>
            </div>

            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('admin.profile.update')); ?>" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="row g-4">
                    <div class="col-xl-4 col-lg-5">
                        <div class="card h-100 profile-summary-card">
                            <div class="card-body text-center">
                                <div class="profile-avatar mx-auto mb-3">
                                    <?php if(!empty($admin->profile_image)): ?>
                                        <img src="<?php echo e(asset('public/uploads/admins/' . $admin->profile_image)); ?>"
                                            alt="<?php echo e($admin->name); ?>" class="profile-avatar-image">
                                    <?php else: ?>
                                        <?php echo e(strtoupper(substr((string) ($admin->name ?? 'A'), 0, 1))); ?>

                                    <?php endif; ?>
                                </div>
                                <h5 class="mb-1"><?php echo e($admin->name); ?></h5>
                                <p class="text-muted mb-2"><?php echo e($admin->email); ?></p>
                                <span class="badge badge-light-success rounded-pill px-3 py-2">Administrator</span>

                                <div class="mt-3 text-start">
                                    <label class="form-label">Profile Photo</label>
                                    <input type="file" name="profile_image"
                                        class="form-control <?php $__errorArgs = ['profile_image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        accept=".jpg,.jpeg,.png,.webp">
                                    <?php $__errorArgs = ['profile_image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    <small class="text-muted">Upload JPG, PNG, or WEBP up to 2MB.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-8 col-lg-7">
                        <div class="card mb-4">
                            <div class="card-header card-header-2">
                                <h5><i class="ri-profile-line me-2"></i>Profile Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="name" value="<?php echo e(old('name', $admin->name)); ?>"
                                            class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" data-alpha-name
                                            required>
                                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email ID</label>
                                        <div class="position-relative">
                                            <input type="email" value="<?php echo e($admin->email); ?>"
                                                class="form-control  pe-5" readonly disabled>
                                            <span class="profile-lock-icon"><i class="ri-lock-line"></i></span>
                                        </div>
                                        <small class="text-muted">Email ID cannot be updated. It is unique for the admin
                                            account.</small>
                                    </div>
                                    <?php if(isset($admin->mobile) || \Illuminate\Support\Facades\Schema::hasColumn('users', 'mobile')): ?>
                                        <div class="col-md-6">
                                            <label class="form-label">Mobile No.</label>
                                            <input type="text" name="mobile" value="<?php echo e(old('mobile', $admin->mobile ?? '')); ?>"
                                                class="form-control" maxlength="10" inputmode="numeric" pattern="[0-9]{10}"
                                                placeholder="Example: 9876543210"
                                                title="Enter a 10-digit mobile number (e.g., 9876543210)"
                                                oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)">
                                            <small class="text-muted">Accepted format: 10 digits only (example:
                                                9876543210).</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header card-header-2">
                                <h5><i class="ri-lock-password-line me-2"></i>Update Password</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" name="current_password"
                                            class="form-control <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                            autocomplete="current-password">
                                        <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">New Password</label>
                                        <input type="password" name="new_password"
                                            class="form-control <?php $__errorArgs = ['new_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                            autocomplete="new-password">
                                        <?php $__errorArgs = ['new_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" name="new_password_confirmation"
                                            class="form-control <?php $__errorArgs = ['new_password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                            autocomplete="new-password">
                                        <?php $__errorArgs = ['new_password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                                <p class="text-muted small mt-3 mb-0">Leave password fields blank if you do not want to
                                    change the password.</p>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <a href="<?php echo e(route('admin.dashboard')); ?>" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-theme">Update Profile</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <style>
        .profile-summary-card {
            border: 1px solid #ebeff4;
            background: radial-gradient(circle at top right, rgb(213 124 34 / 16%), #fff 60%);
        }

        .profile-avatar {
            width: 94px;
            height: 94px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, #0f4c75, #3282b8);
            overflow: hidden;
        }

        .profile-avatar-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-lock-icon {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #7c8798;
            font-size: 18px;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var nameInput = document.querySelector('input[name="name"][data-alpha-name]');
            if (!nameInput) {
                return;
            }

            function sanitizeName(value) {
                return value.replace(/[^a-zA-Z\s]/g, '').replace(/\s{2,}/g, ' ');
            }

            nameInput.addEventListener('input', function () {
                this.value = sanitizeName(this.value);
            });

            nameInput.addEventListener('paste', function () {
                var input = this;
                setTimeout(function () {
                    input.value = sanitizeName(input.value);
                }, 0);
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/developmentalpha/public_html/swastik-food-machinery.developmentalphawizz.com/resources/views/admin/settings/profile.blade.php ENDPATH**/ ?>