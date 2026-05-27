<?php $__env->startSection('content'); ?>

<style>
    .status-switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 24px;
    }

    .status-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .status-slider {
        position: absolute;
        cursor: pointer;
        inset: 0;
        background-color: #d4d7dd;
        transition: .25s;
        border-radius: 24px;
    }

    .status-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        top: 3px;
        background-color: #fff;
        transition: .25s;
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .25);
    }

    .status-switch input:checked+.status-slider {
        background-color: #0da487;
    }

    .status-switch input:checked+.status-slider:before {
        transform: translateX(22px);
    }
</style>

<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title d-flex align-items-center flex-wrap gap-2 mb-4">
            <h5 class="mb-0"><i class="ri-percent-line me-2"></i>GST Tax Management</h5>
            <a href="<?php echo e(route('admin.gst-taxes.create')); ?>" class="btn btn-theme btn-sm ms-auto">
                <i class="ri-add-line me-1"></i>Add GST Tax
            </a>
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

        
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-2">
                <form method="GET" action="<?php echo e(route('admin.gst-taxes.index')); ?>" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control form-control-sm" style="max-width:280px"
                        placeholder="Search by name…" value="<?php echo e($search); ?>">
                    <button class="btn btn-sm btn-outline-secondary" type="submit">Search</button>
                    <?php if($search): ?>
                    <a href="<?php echo e(route('admin.gst-taxes.index')); ?>" class="btn btn-sm btn-outline-danger">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="card card-table border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table all-package theme-table align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Percentage</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $taxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr id="gst-row-<?php echo e($tax->id); ?>">
                                <td><?php echo e($taxes->firstItem() + $loop->index); ?></td>
                                <td class="fw-medium"><?php echo e($tax->name); ?></td>
                                <td><?php echo e(number_format((float) $tax->percentage, 2)); ?>%</td>
                                <td>
                                    <div class="form-check form-switch mb-0 p-0" title="Toggle status">
                                        <label class="status-switch" title="Activate / deactivate account">
                                            <input class="form-check-input gst-status-toggle" type="checkbox"
                                                data-id="<?php echo e($tax->id); ?>"
                                                data-url="<?php echo e(route('admin.gst-taxes.toggle-status', $tax->id)); ?>" <?php echo e($tax->status === 1 ? 'checked' : ''); ?>>
                                            <span class="status-slider"></span>

                                        </label>
                                    </div>
                                </td>
                                <td class="text-muted small"><?php echo e($tax->created_at->format('M j, Y')); ?></td>
                                <td>
                                    <ul class="d-flex gap-2 mb-0 list-unstyled">
                                        <li>
                                            <a href="<?php echo e(route('admin.gst-taxes.edit', $tax->id)); ?>" title="Edit">
                                                <i class="ri-pencil-line"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0)" class="delete-gst-btn"
                                                data-form-id="delete-gst-form-<?php echo e($tax->id); ?>"
                                                data-name="<?php echo e($tax->name); ?>" title="Delete">
                                                <i class="ri-delete-bin-line text-danger"></i>
                                            </a>
                                            <form id="delete-gst-form-<?php echo e($tax->id); ?>" method="POST"
                                                action="<?php echo e(route('admin.gst-taxes.destroy', $tax->id)); ?>"
                                                class="d-none">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                            </form>
                                        </li>
                                    </ul>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No GST taxes found.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if($taxes->hasPages()): ?>
                <div class="mt-3">
                    <?php echo e($taxes->links()); ?>

                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {

        // Delete confirmation
        document.querySelectorAll('.delete-gst-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var name = btn.getAttribute('data-name');
                if (confirm('Delete "' + name + '"? Products using this GST slab will be set to no GST.')) {
                    document.getElementById(btn.getAttribute('data-form-id')).submit();
                }
            });
        });

        // AJAX status toggle
        document.querySelectorAll('.gst-status-toggle').forEach(function (toggle) {
            toggle.addEventListener('change', function () {
                var id = this.getAttribute('data-id');
                var url = this.getAttribute('data-url');
                var el = this;

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (!data.success) {
                            el.checked = !el.checked; // revert
                        }
                    })
                    .catch(function () {
                        el.checked = !el.checked; // revert on error
                    });
            });
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/developmentalpha/public_html/swastik-food-machinery.developmentalphawizz.com/resources/views/admin/gst-taxes/index.blade.php ENDPATH**/ ?>