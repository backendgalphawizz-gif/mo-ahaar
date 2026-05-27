<?php $__env->startSection('content'); ?>

<style>
    select , .btn-theme , .btn-outline-secondary{
        height: 38px !important;
    }
</style>
<div class="page-body">
    <div class="container-fluid">
        <div class="card card-table">
            <div class="card-body">
                <div class="title-header option-title d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                    <div>
                        <h5 class="mb-0"><?php echo e($title); ?></h5>
                        <small class="text-muted">Manage customer support tickets, assignments, and replies.</small>
                    </div>
                </div>

                <?php if(session('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo e(session('success')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if(session('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo e(session('error')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="GET" action="<?php echo e(route('admin.tickets.index')); ?>" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" value="<?php echo e(request('search')); ?>" style="height: 38px;" placeholder="Search subject or user">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="status">
                            <option value="">All statuses</option>
                            <?php $__currentLoopData = \App\Models\Ticket::statusOptions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($status); ?>" <?php echo e(request('status') === $status ? 'selected' : ''); ?>><?php echo e(ucwords(str_replace('_', ' ', $status))); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="type">
                            <option value="">All types</option>
                            <?php $__currentLoopData = \App\Models\Ticket::typeOptions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($type); ?>" <?php echo e(request('type') === $type ? 'selected' : ''); ?>><?php echo e(ucfirst($type)); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="user_id">
                            <option value="">All users</option>
                            <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($customer->user_id); ?>" <?php echo e((string) request('user_id') === (string) $customer->user_id ? 'selected' : ''); ?>>
                                    <?php echo e($customer->name ?: 'User #' . $customer->user_id); ?><?php echo e($customer->email ? ' (' . $customer->email . ')' : ''); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-theme flex-fill">Filter</button>
                        <a href="<?php echo e(route('admin.tickets.index')); ?>" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table all-package table-modern text-start">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Subject</th>
                                <th>User</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Assigned To</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e(($tickets->firstItem() ?? 0) + $loop->index); ?></td>
                                    <td>
                                        <div class="fw-semibold"><?php echo e($ticket->subject); ?></div>
                                        <small class="text-muted"><?php echo e(\Illuminate\Support\Str::limit($ticket->description, 80)); ?></small>
                                    </td>
                                    <td><?php echo e($ticket->user?->name ?: 'User #' . $ticket->user_id); ?></td>
                                    <td><?php echo e(ucfirst($ticket->type)); ?></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo e(ucwords(str_replace('_', ' ', $ticket->status))); ?></span></td>
                                    <td><span class="badge bg-secondary"><?php echo e(ucfirst($ticket->priority)); ?></span></td>
                                    <td><?php echo e($ticket->assignedTo?->name ?: 'Unassigned'); ?></td>
                                    <td><?php echo e(optional($ticket->created_at)->format('d M Y h:i A')); ?></td>
                                    <td>
                                        <a href="<?php echo e(route('admin.tickets.show', $ticket->id)); ?>" class="btn btn-sm btn-theme">Open</a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">No tickets found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if($tickets->hasPages()): ?>
                    <div class="mt-3">
                        <?php echo e($tickets->links('pagination::bootstrap-5')); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/developmentalpha/public_html/swastik-food-machinery.developmentalphawizz.com/resources/views/admin/tickets/index.blade.php ENDPATH**/ ?>