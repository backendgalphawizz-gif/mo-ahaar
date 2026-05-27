<?php
    $storeSetting = $globalStoreSetting ?? null;
    $faviconUrl = !empty($storeSetting) && !empty($storeSetting->favicon)
        ? asset('public/uploads/settings/' . $storeSetting->favicon)
        : asset('public/assets/images/favicon.png');
    $faviconVersion = !empty($storeSetting) && !empty($storeSetting->updated_at)
        ? $storeSetting->updated_at->timestamp
        : '1';
?>
<link rel="icon" href="<?php echo e($faviconUrl); ?>?v=<?php echo e($faviconVersion); ?>">
<link rel="shortcut icon" href="<?php echo e($faviconUrl); ?>?v=<?php echo e($faviconVersion); ?>">
<?php /**PATH /home/developmentalpha/public_html/swastik-food-machinery.developmentalphawizz.com/resources/views/layouts/favicon-dynamic.blade.php ENDPATH**/ ?>