<?php $__env->startSection('content'); ?>
<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title d-flex align-items-center mb-4">
            <h5><i class="ri-image-2-line me-2"></i><?php echo e($title); ?></h5>
            <a href="<?php echo e(route('admin.banners.create')); ?>" class="btn btn-theme btn-sm ms-auto">
                <i class="ri-add-line me-1"></i>Add Banner
            </a>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-checkbox-circle-line me-2"></i><?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card card-table">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table  table-modern align-middle">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Image</th>
                                <th>Title</th>
                                <?php if(\Illuminate\Support\Facades\Schema::hasColumn('banners', 'banner_type')): ?>
                                <th>Section</th>
                                <?php endif; ?>
                                <th>Link</th>
                                <th>Visible from</th>
                                <th>Visible to</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $banners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $banner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($loop->iteration); ?></td>
                                    <td>
                                        <img src="<?php echo e(asset('public/uploads/banners/' . $banner->banner_image)); ?>" alt="banner" style="width:90px;height:54px;object-fit:cover;border-radius:8px;">
                                    </td>
                                    <td><?php echo e($banner->title ?: '-'); ?></td>
                                    <?php if(\Illuminate\Support\Facades\Schema::hasColumn('banners', 'banner_type')): ?>
                                    <td><span class="badge bg-light text-dark text-capitalize"><?php echo e($banner->banner_type ?: 'slider'); ?></span></td>
                                    <?php endif; ?>
                                    <td>
                                        <?php if(!empty($banner->button_link)): ?>
                                            <a href="<?php echo e($banner->button_link); ?>" target="_blank" rel="noopener noreferrer" class="small text-truncate d-inline-block" style="max-width: 200px;"><?php echo e($banner->button_link); ?></a>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-nowrap"><?php echo e($banner->visible_from ? \Illuminate\Support\Carbon::parse($banner->visible_from)->format('M j, Y') : '—'); ?></td>
                                    <td class="text-nowrap"><?php echo e($banner->visible_to ? \Illuminate\Support\Carbon::parse($banner->visible_to)->format('M j, Y') : '—'); ?></td>
                                    <td>
                                        <?php if((int) $banner->status === 1): ?>
                                            <span class="badge badge-soft-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-soft-warning">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <ul class="d-flex gap-2 mb-0 list-unstyled">
                                            <li>
                                                <a href="<?php echo e(route('admin.banners.edit', $banner->id)); ?>" title="Edit">
                                                    <i class="ri-pencil-line"></i>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0)" class="delete-banner-btn" data-form-id="delete-banner-form-<?php echo e($banner->id); ?>" data-banner-name="<?php echo e($banner->title ?: 'this banner'); ?>" title="Delete">
                                                    <i class="ri-delete-bin-line text-danger"></i>
                                                </a>
                                                <form id="delete-banner-form-<?php echo e($banner->id); ?>" method="POST" action="<?php echo e(route('admin.banners.delete', $banner->id)); ?>" class="d-none">
                                                    <?php echo csrf_field(); ?>
                                                </form>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="<?php echo e(\Illuminate\Support\Facades\Schema::hasColumn('banners', 'banner_type') ? 9 : 8); ?>" class="text-center text-muted py-4">No banners found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-banner-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var formId = this.getAttribute('data-form-id');
            var name = this.getAttribute('data-banner-name') || 'this banner';
            var form = document.getElementById(formId);
            if (!form) return;

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete Banner?',
                    text: 'Are you sure you want to delete ' + name + '?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#dc3545'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else if (confirm('Delete ' + name + '?')) {
                form.submit();
            }
        });
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/developmentalpha/public_html/swastik-food-machinery.developmentalphawizz.com/resources/views/admin/banners/index.blade.php ENDPATH**/ ?>