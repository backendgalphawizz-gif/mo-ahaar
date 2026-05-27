<?php $__env->startSection('content'); ?>
<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
            <div>
                <h5 class="mb-0"><?php echo e($title); ?></h5>
                <small class="text-muted">Ticket #<?php echo e($ticket->id); ?> by <?php echo e($ticket->user?->name ?: 'User #' . $ticket->user_id); ?></small>
            </div>
            <a href="<?php echo e(route('admin.tickets.index')); ?>" class="btn btn-outline-secondary btn-sm">Back to tickets</a>
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

        <?php if($errors->any()): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Please fix the highlighted fields and submit again.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-3 text-muted">Ticket Summary</h6>
                        <div class="mb-2"><strong>Subject:</strong> <?php echo e($ticket->subject); ?></div>
                        <div class="mb-2"><strong>Type:</strong> <?php echo e(ucfirst($ticket->type)); ?></div>
                        <div class="mb-2"><strong>Status:</strong> <?php echo e(ucwords(str_replace('_', ' ', $ticket->status))); ?></div>
                        <div class="mb-2"><strong>Priority:</strong> <?php echo e(ucfirst($ticket->priority)); ?></div>
                        <div class="mb-2"><strong>User:</strong> <?php echo e($ticket->user?->name ?: 'User #' . $ticket->user_id); ?></div>
                        <div class="mb-2"><strong>Email:</strong> <?php echo e($ticket->user?->email ?: 'N/A'); ?></div>
                        <div class="mb-0"><strong>Created:</strong> <?php echo e(optional($ticket->created_at)->format('d M Y h:i A')); ?></div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <h6 class="mb-3 text-muted">Update Ticket</h6>
                        <form method="POST" action="<?php echo e(route('admin.tickets.update', $ticket->id)); ?>">
                            <?php echo csrf_field(); ?>
                            <div class="mb-3">
                                <label class="form-label-title">Status</label>
                                <select name="status" class="form-select <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                    <?php $__currentLoopData = \App\Models\Ticket::statusOptions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($status); ?>" <?php echo e(old('status', $ticket->status) === $status ? 'selected' : ''); ?>><?php echo e(ucwords(str_replace('_', ' ', $status))); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label-title">Priority</label>
                                <select name="priority" class="form-select <?php $__errorArgs = ['priority'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                    <?php $__currentLoopData = \App\Models\Ticket::priorityOptions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priority): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($priority); ?>" <?php echo e(old('priority', $ticket->priority) === $priority ? 'selected' : ''); ?>><?php echo e(ucfirst($priority)); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label-title">Type</label>
                                <select name="type" class="form-select <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                    <?php $__currentLoopData = \App\Models\Ticket::typeOptions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($type); ?>" <?php echo e(old('type', $ticket->type) === $type ? 'selected' : ''); ?>><?php echo e(ucfirst($type)); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label-title">Assign To</label>
                                <select name="assigned_to" class="form-select <?php $__errorArgs = ['assigned_to'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="">Unassigned</option>
                                    <?php $__currentLoopData = $supportAgents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($agent->user_id); ?>" <?php echo e((string) old('assigned_to', $ticket->assigned_to) === (string) $agent->user_id ? 'selected' : ''); ?>>
                                            <?php echo e($agent->name ?: 'Admin #' . $agent->user_id); ?><?php echo e($agent->email ? ' (' . $agent->email . ')' : ''); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-theme w-100">Save Ticket</button>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <h6 class="mb-3 text-muted">Original Attachments</h6>
                        <?php $__empty_1 = true; $__currentLoopData = $ticket->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="mb-2">
                                <a href="<?php echo e(url('public/' . $attachment->file_path)); ?>" target="_blank" rel="noopener"><?php echo e(basename($attachment->file_path)); ?></a>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-muted mb-0">No ticket attachments uploaded.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="mb-3 text-muted">Description</h6>
                        <p class="mb-0" style="white-space: pre-line;"><?php echo e($ticket->description); ?></p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="mb-3 text-muted">Conversation</h6>
                        <?php $__empty_1 = true; $__currentLoopData = $ticket->replies->where('is_internal', false); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="border rounded p-3 mb-3 <?php echo e($reply->is_admin ? 'bg-light' : ''); ?>">
                                <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                                    <div>
                                        <strong class="text-dark"><?php echo e($reply->user?->name ?: 'User #' . $reply->user_id); ?></strong>
                                        <span class="badge <?php echo e($reply->is_admin ? 'bg-primary' : 'bg-secondary'); ?> ms-2"><?php echo e($reply->is_admin ? 'Admin' : 'User'); ?></span>
                                    </div>
                                    <small class="text-muted "><?php echo e(optional($reply->created_at)->format('d M Y h:i A')); ?></small>
                                </div>
                                <div style="white-space: pre-line;" class="text-dark"><?php echo e($reply->message); ?></div>
                                <?php if($reply->attachment): ?>
                                    <div class="mt-2">
                                        <a href="<?php echo e(url('public/' . $reply->attachment)); ?>" target="_blank" rel="noopener">View attachment</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-muted mb-0">No replies yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="mb-3 text-muted">Reply to Ticket</h6>
                        <form method="POST" action="<?php echo e(route('admin.tickets.reply', $ticket->id)); ?>" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="mb-3">
                                <textarea name="message" rows="5" class="form-control <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Write your response" required><?php echo e(old('message')); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <input type="file" name="attachment" class="form-control <?php $__errorArgs = ['attachment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <small class="text-muted">Allowed: jpg, png, webp, pdf, doc, docx, txt. Max 5MB.</small>
                            </div>
                            <button type="submit" class="btn btn-theme">Send Reply</button>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-3 text-muted">Internal Notes</h6>
                        <?php $__empty_1 = true; $__currentLoopData = $ticket->replies->where('is_internal', true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="border rounded p-3 mb-3 bg-warning bg-opacity-10">
                                <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                                    <strong><?php echo e($reply->user?->name ?: 'User #' . $reply->user_id); ?></strong>
                                    <small class="text-muted"><?php echo e(optional($reply->created_at)->format('d M Y h:i A')); ?></small>
                                </div>
                                <div style="white-space: pre-line;"><?php echo e($reply->message); ?></div>
                                <?php if($reply->attachment): ?>
                                    <div class="mt-2">
                                        <a href="<?php echo e(url('public/' . $reply->attachment)); ?>" target="_blank" rel="noopener">View attachment</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-muted">No internal notes yet.</p>
                        <?php endif; ?>

                        <form method="POST" action="<?php echo e(route('admin.tickets.internal-note', $ticket->id)); ?>" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="mb-3">
                                <textarea name="message" rows="4" class="form-control" placeholder="Add an internal note" required></textarea>
                            </div>
                            <div class="mb-3">
                                <input type="file" name="attachment" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-outline-dark">Save Internal Note</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/developmentalpha/public_html/swastik-food-machinery.developmentalphawizz.com/resources/views/admin/tickets/show.blade.php ENDPATH**/ ?>