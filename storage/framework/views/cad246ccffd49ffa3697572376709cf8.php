<?php $__env->startSection('content'); ?>
    <div class="page-body">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="title-header option-title d-flex align-items-center mb-4">
                        <h5><i class="ri-profile-line me-2"></i><?php echo e($title); ?></h5>
                        <div class="ms-auto d-flex gap-2">
                            <a href="<?php echo e(route('admin.edit-customer', urlencode(Crypt::encrypt($customer->customer_id)))); ?>"
                                class="btn btn-theme btn-sm">
                                <i class="ri-pencil-line me-1"></i>Edit Customer
                            </a>
                            <a href="<?php echo e(route('admin.customers')); ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="ri-arrow-left-line me-1"></i>Back to List
                            </a>
                        </div>
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

                    <?php
                        $segmentView = is_string($customer->user_type ?? null) ? trim($customer->user_type) : '';
                        $isWholesalerCustomer = strcasecmp($segmentView, \App\Models\Product::TARGET_WHOLESALER) === 0;
                    ?>
                    <div class="row g-4">
                        <div class="col-xl-4 col-lg-5">
                            <div class="card h-100 customer-profile-card">
                                <div class="card-body text-center pb-4">
                                    <div class="customer-profile-img mb-3">
                                        <img src="<?php echo e(!empty($customer->profile_image) ? asset('public/uploads/customers/' . $customer->profile_image) : asset('public/uploads/customers/customer.png')); ?>"
                                            class="rounded-circle" width="108" height="108" style="object-fit:cover;"
                                            alt="<?php echo e($customer->name); ?>">
                                    </div>
                                    <h5 class="mb-1"><?php echo e($customer->name); ?></h5>
                                    <p class="text-muted mb-2">Customer ID: #<?php echo e($customer->customer_id); ?></p>

                                    <?php
                                        $vAp = strtolower((string) ($customer->approval_status ?? 'approved'));
                                    ?>
                                    <?php if(!empty($hasApproval) && $vAp === 'pending'): ?>
                                        <span class="badge badge-light-warning rounded-pill px-3 py-2">Pending approval</span>
                                    <?php elseif(!empty($hasApproval) && $vAp === 'rejected'): ?>
                                        <span class="badge badge-light-danger rounded-pill px-3 py-2">Registration
                                            rejected</span>
                                    <?php elseif((int) $customer->status === 1): ?>
                                        <span class="badge badge-light-success rounded-pill px-3 py-2">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-light-secondary rounded-pill px-3 py-2">Inactive</span>
                                    <?php endif; ?>


                                    <hr class="my-3">

                                    <div class="text-start customer-contact-list">
                                        <div class="d-flex align-items-center mb-3">
                                            <span class="customer-icon-box me-3"><i class="ri-mail-line"></i></span>
                                            <div>
                                                <small class="text-muted d-block">Email</small>
                                                <span class="fw-500"><?php echo e($customer->email ?: '-'); ?></span>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center mb-3">
                                            <span class="customer-icon-box me-3"><i class="ri-phone-line"></i></span>
                                            <div>
                                                <small class="text-muted d-block">Phone</small>
                                                <span class="fw-500"><?php echo e($customer->mobile ?: '-'); ?></span>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center mb-3">
                                            <span class="customer-icon-box me-3"><i class="ri-user-star-line"></i></span>
                                            <div>
                                                <small class="text-muted d-block">Gender</small>
                                                <span class="fw-500"><?php echo e($customer->gender ?: '-'); ?></span>
                                            </div>
                                        </div>

                                        <div
                                            class="d-flex align-items-center <?php echo e($isWholesalerCustomer ? 'mb-3' : 'mb-0'); ?>">
                                            <span class="customer-icon-box me-3"><i class="ri-cake-2-line"></i></span>
                                            <div>
                                                <small class="text-muted d-block">Date of Birth</small>
                                                <span
                                                    class="fw-500"><?php echo e($customer->dob ? date('d M Y', strtotime($customer->dob)) : '-'); ?></span>
                                            </div>
                                        </div>

                                        <?php if($isWholesalerCustomer): ?>
                                            <div class="d-flex align-items-start">
                                                <span class="customer-icon-box me-3"><i class="ri-government-line"></i></span>
                                                <div>
                                                    <small class="text-muted d-block">GST number</small>
                                                    <span
                                                        class="fw-500"><?php echo e(trim((string) ($customer->gst_number ?? '')) !== '' ? $customer->gst_number : '—'); ?></span>
                                                    <?php if(!empty($hasGstVerified)): ?>
                                                        <div class="small mt-1">
                                                            <?php if(!empty($customer->gst_verified_at)): ?>
                                                                <span class="text-success">Verified</span>
                                                            <?php elseif(trim((string) ($customer->gst_number ?? '')) !== ''): ?>
                                                                <span class="text-warning">Not verified</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-8 col-lg-7">
                            <div class="card h-100">
                                <div class="card-header card-header-2 border-0 pb-0">
                                    <h5><i class="ri-map-pin-line me-2"></i>Address & Activity</h5>
                                </div>
                                <div class="card-body">
                                    <div class="customer-detail-item mb-3">
                                        <label>Primary Address</label>
                                        <span><?php echo e($customer->customer_address ?: 'No address added.'); ?></span>
                                    </div>

                                    <div class="customer-detail-item mb-4">
                                        <label>Saved Shipping Addresses</label>
                                        <?php if(isset($customerAddresses) && $customerAddresses->isNotEmpty()): ?>
                                            <div class="table-responsive">
                                                <table class="table table-sm align-middle mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Type</th>
                                                            <th>Contact</th>
                                                            <th>Address</th>
                                                            <th>Default</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $__currentLoopData = $customerAddresses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $address): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <tr>
                                                                <td><?php echo e(ucfirst($address->address_type ?: 'other')); ?></td>
                                                                <td>
                                                                    <div><?php echo e($address->contact_name ?: $customer->name); ?></div>
                                                                    <small
                                                                        class="text-muted"><?php echo e($address->mobile ?: $customer->mobile); ?></small>
                                                                </td>
                                                                <td><?php echo e(collect([$address->address_line, $address->landmark, $address->city, $address->state, $address->country, $address->pincode])->filter()->implode(', ')); ?>

                                                                </td>
                                                                <td><?php echo e($address->is_default ? 'Yes' : 'No'); ?></td>
                                                            </tr>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <span>No saved shipping addresses.</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="customer-detail-item">
                                                <label>Account Status</label>
                                                <span>
                                                    <?php if(!empty($hasApproval) && $vAp !== 'approved'): ?>
                                                        Registration: <?php echo e(ucfirst($vAp)); ?>.
                                                    <?php else: ?>
                                                        <?php echo e((int) $customer->status === 1 ? 'Active' : 'Inactive (on hold)'); ?>

                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <?php if(!empty($customer->user_type)): ?>
                                            <div class="col-md-6">
                                                <div class="customer-detail-item">
                                                    <label>User type</label>
                                                    <span><?php echo e($customer->user_type); ?></span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if($isWholesalerCustomer): ?>
                                            <div class="col-md-6">
                                                <div class="customer-detail-item">
                                                    <label>GST number</label>
                                                    <span><?php echo e(trim((string) ($customer->gst_number ?? '')) !== '' ? $customer->gst_number : '—'); ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="customer-detail-item">
                                                    <label>GST verification</label>
                                                    <span>
                                                        <?php if(!empty($hasGstVerified) && !empty($customer->gst_verified_at)): ?>
                                                            Verified on
                                                            <?php echo e(\Carbon\Carbon::parse($customer->gst_verified_at)->format('d M Y, h:i A')); ?>

                                                        <?php elseif(!empty($hasGstVerified) && trim((string) ($customer->gst_number ?? '')) !== ''): ?>
                                                            Not verified yet
                                                        <?php else: ?>
                                                            —
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="col-md-6">
                                            <div class="customer-detail-item">
                                                <label>User ID</label>
                                                <span><?php echo e($customer->user_id); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="customer-detail-item activity-highlight">
                                                <label>Total Orders</label>
                                                <span><?php echo e(number_format((int) ($activitySummary->total_orders ?? 0))); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="customer-detail-item activity-highlight">
                                                <label>Total Spent</label>
                                                <span>₹<?php echo e(number_format((float) ($activitySummary->total_spent ?? 0), 2)); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="customer-detail-item">
                                                <label>Completed Orders</label>
                                                <span><?php echo e(number_format((int) ($activitySummary->completed_orders ?? 0))); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="customer-detail-item">
                                                <label>Pending Orders</label>
                                                <span><?php echo e(number_format((int) ($activitySummary->pending_orders ?? 0))); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="customer-detail-item">
                                                <label>Last Order Activity</label>
                                                <span>
                                                    <?php echo e(!empty($activitySummary->last_order_at) ? \Carbon\Carbon::parse($activitySummary->last_order_at)->format('d M Y, h:i A') : 'No order activity yet'); ?>

                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                                        <?php if(!empty($hasApproval) && $vAp === 'pending'): ?>
                                            <form method="POST"
                                                action="<?php echo e(route('admin.customers.approve-registration', $customer->customer_id)); ?>"
                                                class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn btn-success"><i
                                                        class="ri-checkbox-circle-line me-1"></i>Approve registration</button>
                                            </form>
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                                                data-bs-target="#rejectCustomerProfileModal">
                                                <i class="ri-close-circle-line me-1"></i>Reject registration
                                            </button>
                                        <?php endif; ?>
                                        <?php
                                            $gstView = trim((string) ($customer->gst_number ?? ''));
                                            $needGstVerify = $isWholesalerCustomer && $gstView !== '' && empty($customer->gst_verified_at ?? null);
                                        ?>
                                        <?php if(!empty($hasGstVerified) && $needGstVerify): ?>
                                            <form method="POST"
                                                action="<?php echo e(route('admin.customers.verify-gst', $customer->customer_id)); ?>"
                                                class="d-inline"
                                                onsubmit="return confirm('Mark this GST number as verified?');">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn btn-outline-primary"><i
                                                        class="ri-shield-check-line me-1"></i>Verify GST</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if(!empty($hasApproval) && $vAp === 'approved'): ?>
                                            <form method="POST"
                                                action="<?php echo e(route('admin.customers.toggle-status', $customer->customer_id)); ?>"
                                                class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn btn-outline-secondary">
                                                    <i
                                                        class="ri-toggle-line me-1"></i><?php echo e((int) $customer->status === 1 ? 'Deactivate account' : 'Activate account'); ?>

                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <a href="<?php echo e(route('admin.edit-customer', urlencode(Crypt::encrypt($customer->customer_id)))); ?>"
                                            class="btn btn-theme">
                                            <i class="ri-pencil-line me-1"></i>Edit Profile
                                        </a>
                                        <a href="javascript:void(0)" class="btn btn-outline-danger" data-bs-toggle="modal"
                                            data-bs-target="#deleteCustomerModal">
                                            <i class="ri-delete-bin-line me-1"></i>Delete
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header card-header-2 border-0 pb-0">
                            <h5><i class="ri-pulse-line me-2"></i>Recent Customer Activity</h5>
                        </div>
                        <div class="card-body pt-3">
                            <div class="activity-timeline">
                                <?php $__empty_1 = true; $__currentLoopData = $recentActivities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div class="activity-row">
                                        <div
                                            class="activity-dot <?php echo e($activity->activity_type === 'tracking' ? 'activity-dot-warning' : 'activity-dot-success'); ?>">
                                        </div>
                                        <div class="activity-content">
                                            <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                                                <div>
                                                    <h6 class="mb-1"><?php echo e($activity->activity_label); ?> -
                                                        <?php echo e($activity->order_number ?? ('#' . $activity->order_id)); ?></h6>
                                                    <p class="mb-1 text-muted">
                                                        Status:
                                                        <?php echo e(ucfirst(str_replace('_', ' ', $activity->status ?? 'pending'))); ?>

                                                        <?php if(isset($activity->total_amount)): ?>
                                                            | Amount:
                                                            ₹<?php echo e(number_format((float) ($activity->total_amount ?? 0), 2)); ?>

                                                        <?php endif; ?>
                                                    </p>
                                                    <?php if(!empty($activity->description)): ?>
                                                        <small class="text-muted"><?php echo e($activity->description); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                                <small class="text-muted">
                                                    <?php echo e(!empty($activity->activity_at) ? \Carbon\Carbon::parse($activity->activity_at)->format('d M Y, h:i A') : '-'); ?>

                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="text-center text-muted py-3">No customer activity found yet.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="rejectCustomerProfileModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Reject registration</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST"
                                    action="<?php echo e(route('admin.customers.reject-registration', $customer->customer_id)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <div class="modal-body">
                                        <p class="mb-2">Reject registration for <strong><?php echo e($customer->name); ?></strong>?</p>
                                        <label class="form-label small text-muted">Optional note</label>
                                        <textarea class="form-control" name="reason" rows="2" maxlength="500"
                                            placeholder="Reason (optional)"></textarea>
                                    </div>
                                    <div class="modal-footer justify-content-center">
                                        <button type="button" class="btn btn-outline-secondary"
                                            data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Reject registration</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="deleteCustomerModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Delete Customer</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <i class="ri-error-warning-line text-danger" style="font-size: 48px;"></i>
                                    <p class="mt-3 mb-1">This action will deactivate and remove customer access.</p>
                                    <p class="mb-0"><strong><?php echo e($customer->name); ?></strong></p>
                                </div>
                                <div class="modal-footer justify-content-center">
                                    <button type="button" class="btn btn-outline-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <a href="<?php echo e(route('admin.delete-customer', urlencode(Crypt::encrypt($customer->customer_id)))); ?>"
                                        class="btn btn-danger">Yes, Delete</a>
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
        .customer-profile-card {
            background: radial-gradient(circle at top right, rgba(193, 143, 51, .18), #ffffff 62%);
            border: 1px solid #edf3f5;
        }

        .customer-icon-box {
            width: 38px;
            height: 38px;
            border-radius: 9px;
            background: #fff5df;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e3951d;
            font-size: 20px;
            flex-shrink: 0;
        }

        .customer-detail-item {
            padding: 12px 14px;
            background: #f8fafb;
            border: 1px solid #eef3f4;
            border-radius: 10px;
        }

        .customer-detail-item label {
            display: block;
            text-transform: uppercase;
            letter-spacing: .04em;
            font-size: 11px;
            margin-bottom: 4px;
            color: #8f98a6;
            font-weight: 600;
        }

        .customer-detail-item span {
            font-size: 14px;
            color: #1f2b37;
            font-weight: 500;
        }

        .activity-highlight {
            background: linear-gradient(180deg, rgba(193, 143, 51, .08), #f8fafb);
        }

        .activity-timeline {
            position: relative;
        }

        .activity-row {
            display: flex;
            gap: 14px;
            padding: 0 0 18px;
        }

        .activity-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-top: 6px;
            flex-shrink: 0;
            background: #0da487;
            box-shadow: 0 0 0 5px rgba(13, 164, 135, .12);
        }

        .activity-dot-warning {
            background: #f59e0b;
            box-shadow: 0 0 0 5px rgba(245, 158, 11, .14);
        }

        .activity-dot-success {
            background: #16a34a;
            box-shadow: 0 0 0 5px rgba(22, 163, 74, .14);
        }

        .activity-content {
            flex: 1;
            background: #f8fafb;
            border: 1px solid #eef3f4;
            border-radius: 12px;
            padding: 14px;
        }
    </style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/developmentalpha/public_html/swastik-food-machinery.developmentalphawizz.com/resources/views/admin/customers/viewCustomer.blade.php ENDPATH**/ ?>