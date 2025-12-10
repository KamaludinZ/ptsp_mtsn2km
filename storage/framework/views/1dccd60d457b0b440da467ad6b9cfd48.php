<?php
    $id = $getId();
    $isContained = $getContainer()->getParentComponent()->isContained();

    $activeStepClasses = \Illuminate\Support\Arr::toCssClasses([
        'fi-active',
        'p-6' => $isContained,
        'mt-6' => ! $isContained,
    ]);

    $inactiveStepClasses = 'invisible absolute h-0 overflow-hidden p-0';
?>

<div
    x-bind:tabindex="$el.querySelector('[autofocus]') ? '-1' : '0'"
    x-bind:class="{
        <?php echo App\Helpers\AssetHelper::js($activeStepClasses); ?>: step === <?php echo App\Helpers\AssetHelper::js($id); ?>,
        <?php echo App\Helpers\AssetHelper::js($inactiveStepClasses); ?>: step !== <?php echo App\Helpers\AssetHelper::js($id); ?>,
    }"
    x-on:expand="
        if (! isStepAccessible(<?php echo App\Helpers\AssetHelper::js($id); ?>)) {
            return
        }

        step = <?php echo App\Helpers\AssetHelper::js($id); ?>
    "
    x-ref="step-<?php echo e($id); ?>"
    <?php echo e($attributes
            ->merge([
                'aria-labelledby' => $id,
                'id' => $id,
                'role' => 'tabpanel',
            ], escape: false)
            ->merge($getExtraAttributes(), escape: false)
            ->class(['fi-fo-wizard-step outline-none'])); ?>

>
    <?php echo e($getChildComponentContainer()); ?>

</div>
<?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\vendor\filament\forms\resources\views\components\wizard\step.blade.php ENDPATH**/ ?>