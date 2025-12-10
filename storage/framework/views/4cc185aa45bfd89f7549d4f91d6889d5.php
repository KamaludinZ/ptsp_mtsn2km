<?php if($paginator->hasPages()): ?>
    <nav class="d-flex justify-content-center mt-4">
        <ul class="pagination mb-0">
            
            <?php if($paginator->onFirstPage()): ?>
                <li class="page-item disabled">
                    <span class="page-link text-muted border-secondary-subtle bg-light-subtle rounded-3">
                        <i class="fas fa-chevron-left"></i> Sebelumnya
                    </span>
                </li>
            <?php else: ?>
                <li class="page-item">
                    <a class="page-link text-body-secondary border-secondary-subtle bg-light-subtle rounded-3" href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev" aria-label="Previous">
                        <i class="fas fa-chevron-left"></i> Sebelumnya
                    </a>
                </li>
            <?php endif; ?>

            
            <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                
                <?php if(is_string($element)): ?>
                    <li class="page-item disabled">
                        <span class="page-link text-muted border-secondary-subtle bg-light-subtle">...</span>
                    </li>
                <?php endif; ?>

                
                <?php if(is_array($element)): ?>
                    <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($page == $paginator->currentPage()): ?>
                            <li class="page-item active">
                                <span class="page-link bg-primary border-primary text-white rounded-3"><?php echo e($page); ?></span>
                            </li>
                        <?php else: ?>
                            <li class="page-item">
                                <a class="page-link text-body-secondary border-secondary-subtle bg-light-subtle rounded-3" href="<?php echo e($url); ?>"><?php echo e($page); ?></a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            
            <?php if($paginator->hasMorePages()): ?>
                <li class="page-item">
                    <a class="page-link text-body-secondary border-secondary-subtle bg-light-subtle rounded-3" href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next" aria-label="Next">
                        Selanjutnya <i class="fas fa-chevron-right ms-1"></i>
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link text-muted border-secondary-subtle bg-light-subtle rounded-3">
                        Selanjutnya <i class="fas fa-chevron-right ms-1"></i>
                    </span>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\vendor\pagination\custom-pagination.blade.php ENDPATH**/ ?>