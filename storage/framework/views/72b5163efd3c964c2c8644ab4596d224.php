<?php extract((new \Illuminate\Support\Collection($attributes->getAttributes()))->mapWithKeys(function ($value, $key) { return [Illuminate\Support\Str::camel(str_replace([':', '.'], ' ', $key)) => $value]; })->all(), EXTR_SKIP); ?>
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['statePath']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['statePath']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php if (isset($component)) { $__componentOriginal9d0e0d3dae5be2b4072c8372b2a70ecf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9d0e0d3dae5be2b4072c8372b2a70ecf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-tiptap-editor::components.tools.paragraph','data' => ['statePath' => $statePath]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-tiptap-editor::tools.paragraph'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['state-path' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statePath)]); ?>

<?php echo e($slot ?? ""); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9d0e0d3dae5be2b4072c8372b2a70ecf)): ?>
<?php $attributes = $__attributesOriginal9d0e0d3dae5be2b4072c8372b2a70ecf; ?>
<?php unset($__attributesOriginal9d0e0d3dae5be2b4072c8372b2a70ecf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9d0e0d3dae5be2b4072c8372b2a70ecf)): ?>
<?php $component = $__componentOriginal9d0e0d3dae5be2b4072c8372b2a70ecf; ?>
<?php unset($__componentOriginal9d0e0d3dae5be2b4072c8372b2a70ecf); ?>
<?php endif; ?><?php /**PATH /home/puskomdev/ptsp_mtsn2km/storage/framework/views/c92af5f54e9d8b6980d2d148d4961e50.blade.php ENDPATH**/ ?>