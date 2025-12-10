<?php
    $id = $getId();
    $isContained = $getContainer()->getParentComponent()->isContained();

    $activeTabClasses = \Illuminate\Support\Arr::toCssClasses([
        'fi-active',
        'p-6' => $isContained,
        'mt-6' => ! $isContained,
    ]);

    $inactiveTabClasses = 'invisible absolute h-0 overflow-hidden p-0';
?>

<div
    x-bind:class="tab === <?php echo App\Helpers\AssetHelper::js($id); ?> ? <?php echo App\Helpers\AssetHelper::js($activeTabClasses); ?> : <?php echo App\Helpers\AssetHelper::js($inactiveTabClasses); ?>"
    <?php echo e($attributes
            ->merge([
                'aria-labelledby' => $id,
                'id' => $id,
                'role' => 'tabpanel',
                'tabindex' => '0',
            ], escape: false)
            ->merge($getExtraAttributes(), escape: false)
            ->class(['fi-in-tabs-tab outline-none'])); ?>

>
    <?php echo e($getChildComponentContainer()); ?>

</div>
<?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\vendor\filament\infolists\resources\views\components\tabs\tab.blade.php ENDPATH**/ ?>