<ul>
    <?php $__currentLoopData = $headings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $heading): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(array_key_exists($loop->index + 1, $headings) && $headings[$loop->index + 1]['level'] === $heading['level']): ?>
            <li>
                <a href="#<?php echo e($heading['id']); ?>"><?php echo e($heading['text']); ?></a>
            </li>
        <?php else: ?>
        <?php endif; ?>
        <li>
            <?php if($heading[$loop->index + 1] && $heading[$loop->index + 1]['level'] > $heading['level']): ?>
                <ul>
            <?php endif; ?>
                    <a href="#<?php echo e($heading['id']); ?>"><?php echo e($heading['text']); ?></a>
            <?php if($heading[$loop->index + 1] && $heading[$loop->index + 1]['level'] > $heading['level']): ?>
                <ul>
            <?php endif; ?>
        </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\vendor\awcodes\filament-tiptap-editor\resources\views\table-of-contents.blade.php ENDPATH**/ ?>