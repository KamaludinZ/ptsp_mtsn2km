<div
    <?php echo e($attributes
            ->merge([
                'id' => $getId(),
            ], escape: false)
            ->merge($getExtraAttributes(), escape: false)); ?>

>
    <?php echo e($getChildComponentContainer()); ?>

</div>
<?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\vendor\filament\forms\resources\views\components\grid.blade.php ENDPATH**/ ?>