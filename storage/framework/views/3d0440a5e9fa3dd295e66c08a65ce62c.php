<?php $__env->startSection('title', 'Security Scan Results'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid p-4">
    <!-- Page Header -->
    <div class="flex flex-wrap justify-between items-center mb-4">
        <div>
            <h4 class="text-2xl font-bold text-base-content">Security Scan Results</h4>
            <div class="text-sm breadcrumbs">
                <ul>
                    <li><a href="<?php echo e(route('admin.dashboard')); ?>">Dashboard</a></li>
                    <li><a href="<?php echo e(route('admin.security.dashboard')); ?>">Security</a></li>
                    <li>Scan Results</li>
                </ul>
            </div>
        </div>
    </div>

    <?php if($results['timestamp'] !== 'Never'): ?>
        <div class="card bg-base-100 shadow-xl mb-8">
            <div class="card-body">
                <h2 class="card-title">Last Scan on <?php echo e(\Carbon\Carbon::parse($results['timestamp'])->format('d M Y H:i')); ?></h2>
                <div class="flex items-center gap-4">
                    <div class="text-5xl font-bold text-primary"><?php echo e($results['overall_score']); ?>%</div>
                    <div>
                        <p class="text-base-content/70">Overall Security Score</p>
                        <?php if($results['overall_score'] >= 90): ?>
                            <span class="badge badge-success">Excellent</span>
                        <?php elseif($results['overall_score'] >= 70): ?>
                            <span class="badge badge-warning">Good</span>
                        <?php else: ?>
                            <span class="badge badge-error">Needs Attention</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <?php $__currentLoopData = $results['checks']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $checkName => $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h3 class="card-title text-xl capitalize"><?php echo e(str_replace('_', ' ', $checkName)); ?></h3>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="badge 
                                <?php if($check['status'] === 'pass'): ?> badge-success
                                <?php elseif($check['status'] === 'warning'): ?> badge-warning
                                <?php elseif($check['status'] === 'fail'): ?> badge-error
                                <?php else: ?> badge-info <?php endif; ?>">
                                <?php echo e(ucfirst($check['status'])); ?>

                            </span>
                            <p class="text-base-content/70"><?php echo e($check['message']); ?></p>
                        </div>
                        <?php if(isset($check['issues']) && count($check['issues']) > 0): ?>
                            <ul class="list-disc list-inside mt-4 text-error">
                                <?php $__currentLoopData = $check['issues']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $issue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($issue); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>No security scan has been run yet. Please run a scan from the Security Dashboard.</span>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/admin/security/scan-results.blade.php ENDPATH**/ ?>