<?php $__env->startSection('content'); ?>


<div class="page-wrapper compact-wrapper" id="pageWrapper">

    <!-- Page Body start -->
    <div class="page-body-wrapper">
        <!-- Page Sidebar Start-->

        <!-- Page Sidebar Ends-->

        <div class="page-body">

            <!-- New Product Add Start -->
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-sm-8 m-auto">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="card-header-2">
                                            <h5>Category Information</h5>
                                        </div>
                                        <?php if(session('success')): ?>
                                            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
                                        <?php endif; ?>
                                        <?php if(session('error')): ?>
                                            <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
                                        <?php endif; ?>
                                        <?php if($errors->any()): ?>
                                            <div class="alert alert-danger">
                                                <ul class="mb-0">
                                                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <li><?php echo e($error); ?></li>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                        <form action="<?php echo e(route('admin.store-category')); ?>" method="POST" enctype="multipart/form-data" id="categoryForm">
                                            <?php echo csrf_field(); ?>
                                            <div class="theme-form theme-form-2 mega-form">
                                                <div class="mb-4 row align-items-center">
                                                    <label class="form-label-title col-sm-3 mb-0">Category Name</label>
                                                    <div class="col-sm-9">
                                                        <input class="form-control" id="category_name" type="text" name="category_name" placeholder="Category Name" value="<?php echo e(old('category_name')); ?>">
                                                        <p class="errors" id="err_category_name"></p>
                                                    </div>
                                                </div>

                                                <div class="mb-4 row align-items-center mt-5">
                                                    <label class="col-sm-3 col-form-label form-label-title">Category Image</label>
                                                    <div class="form-group col-sm-9">
                                                        <div class="input-group">
                                                            <input type="file" class="form-control" id="category_image" name="category_image" accept="image/*">
                                                        </div>
                                                        <small class="text-danger" id="image-warning">Upload image only (jpeg, png, jpg, gif, svg).</small>
                                                    </div>
                                                </div>
                                                <div class="mb-4 row align-items-center mt-5">
                                                    <label class="col-sm-3 col-form-label form-label-title">Category Description</label>
                                                    <div class="form-group col-sm-9">
                                                        <div class="input-group">
                                                            <textarea class="form-control" id="category_description" name="category_description" placeholder="Category Description"><?php echo e(old('category_description')); ?></textarea>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mb-4 row align-items-center mt-5">
                                                    <label class="form-label-title col-sm-3 mb-0"></label>
                                                    <div class="col-sm-9 d-flex gap-2">
                                                        <button type="button" class="btn btn-solid" onclick="createCategory()">Add Category</button>
                                                         <button onclick="history.back()" class="btn btn-outline-secondary">   Back </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- Container-fluid End -->
    </div>
    <!-- Page Body End -->
</div>


<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/developmentalpha/public_html/swastik-food-machinery.developmentalphawizz.com/resources/views/admin/categories/addCategory.blade.php ENDPATH**/ ?>