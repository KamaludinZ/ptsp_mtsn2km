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
    x-bind:class="{
        <?php echo App\Helpers\AssetHelper::js($activeTabClasses); ?>: tab === <?php echo App\Helpers\AssetHelper::js($id); ?>,
        <?php echo App\Helpers\AssetHelper::js($inactiveTabClasses); ?>: tab !== <?php echo App\Helpers\AssetHelper::js($id); ?>,
    }"
    x-on:expand="tab = <?php echo App\Helpers\AssetHelper::js($id); ?>"
    <?php echo e($attributes
            ->merge([
                'aria-labelledby' => $id,
                'id' => $id,
                'role' => 'tabpanel',
                'tabindex' => '0',
                'wire:key' => "{$this->getId()}.{$getStatePath()}." . \Filament\Forms\Components\Tabs\Tab::class . ".tabs.{$id}",
            ], escape: false)
            ->merge($getExtraAttributes(), escape: false)
            ->class(['fi-fo-tabs-tab outline-none'])); ?>

>
    <?php echo e($getChildComponentContainer()); ?>

</div>
<?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\vendor\filament\forms\resources\views\components\tabs\tab.blade.php ENDPATH**/ ?>