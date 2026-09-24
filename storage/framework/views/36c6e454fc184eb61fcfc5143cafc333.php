<?php if($paginator->hasPages()): ?>
    <div class="join">
        
        <?php if($paginator->onFirstPage()): ?>
            <button class="join-item btn btn-disabled" aria-disabled="true" aria-label="<?php echo app('translator')->get('pagination.previous'); ?>">«</button>
        <?php else: ?>
            <a href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev" class="join-item btn" aria-label="<?php echo app('translator')->get('pagination.previous'); ?>">«</a>
        <?php endif; ?>

        
        <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            
            <?php if(is_string($element)): ?>
                <button class="join-item btn btn-disabled" aria-disabled="true"><?php echo e($element); ?></button>
            <?php endif; ?>

            
            <?php if(is_array($element)): ?>
                <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($page == $paginator->currentPage()): ?>
                        <button class="join-item btn btn-active" aria-current="page"><?php echo e($page); ?></button>
                    <?php else: ?>
                        <a href="<?php echo e($url); ?>" class="join-item btn"><?php echo e($page); ?></a>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        
        <?php if($paginator->hasMorePages()): ?>
            <a href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next" class="join-item btn" aria-label="<?php echo app('translator')->get('pagination.next'); ?>">»</a>
        <?php else: ?>
            <button class="join-item btn btn-disabled" aria-disabled="true" aria-label="<?php echo app('translator')->get('pagination.next'); ?>">»</button>
        <?php endif; ?>
    </div>
<?php endif; ?><?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/vendor/pagination/daisyui.blade.php ENDPATH**/ ?>