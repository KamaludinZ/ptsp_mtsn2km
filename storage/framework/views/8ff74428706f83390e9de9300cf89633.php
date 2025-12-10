<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $__env->yieldContent('title', 'PTSP MTsN 2 Kota Malang'); ?></title>

    
    <meta name="description" content="<?php echo $__env->yieldContent('description', 'Pelayanan Terpadu Satu Pintu MTsN 2 Kota Malang'); ?>">
    <meta name="keywords" content="<?php echo $__env->yieldContent('keywords', 'PTSP, MTsN 2 Malang, Pelayanan Publik'); ?>">

    
    <?php echo assets_preload([
        'resources/css/bootstrap-custom.css',
        'resources/css/app.css',
        'resources/js/bootstrap-bundle.js'
    ]); ?>


    
    <?php echo asset_css('resources/css/bootstrap-custom.css'); ?>

    <?php echo asset_css('resources/css/app.css'); ?>


    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <?php echo $__env->yieldContent('content'); ?>

    
    <?php echo asset_js('resources/js/bootstrap-bundle.js'); ?>

    <?php echo asset_js('resources/js/app.js'); ?>


    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\layouts\public-production.blade.php ENDPATH**/ ?>