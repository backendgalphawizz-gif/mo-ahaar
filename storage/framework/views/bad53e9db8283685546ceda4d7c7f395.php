<?php $__env->startSection('content'); ?>

<style>
    table tbody tr td a .ri-pencil-line {
        color: #e3951d;

    }

    table tbody tr td a.btn-outline-primary:hover .ri-eye-line {
        color: #fff !important;
    }
</style>
<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title d-flex align-items-center mb-4">
            <h5><i class="ri-list-check me-2"></i><?php echo e($title); ?></h5>
        </div>

        <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" action="<?php echo e(route('admin.static-pages.index')); ?>" class="row g-2 align-items-end">
                    <div class="col-md-8">
                        <!-- Vendor selection removed -->
                    </div>
                    <div class="col-md-4 text-md-end">
                        <!-- Vendor load button removed -->
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table  table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">S.No.</th>
                                <th class="ps-3">Title</th>
                                <th>Slug</th>
                                <th>Content Preview</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="ps-3 fw-semibold"><?php echo e($loop->iteration); ?></td>
                                <td class="ps-3 fw-semibold"><?php echo e($page->title); ?></td>
                                <td><span class="text-muted"><?php echo e($page->slug); ?></span></td>
                                <td>
                                    <span class="text-muted"><?php echo e(\Illuminate\Support\Str::limit(strip_tags($page->content), 90)); ?></span>
                                </td>
                                <td>
                                    <?php if($page->status): ?>
                                    <span class="badge badge-soft-success">Published</span>
                                    <?php else: ?>
                                    <span class="badge  badge-soft-warning">Draft</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="<?php echo e(route('admin.static-pages.edit', ['id' => $page->static_page_id])); ?>"
                                        class=" me-1">
                                        <i class="ri-pencil-line"></i> 
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No static pages found.</td>
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
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/developmentalpha/public_html/swastik-food-machinery.developmentalphawizz.com/resources/views/admin/static-pages/index.blade.php ENDPATH**/ ?>