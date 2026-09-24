<?php $__env->startSection('title', 'Security Logs'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid p-4">
    <!-- Page Header -->
    <div class="flex flex-wrap justify-between items-center mb-4">
        <div>
            <h4 class="text-2xl font-bold text-base-content">Security Logs</h4>
            <div class="text-sm breadcrumbs">
                <ul>
                    <li><a href="<?php echo e(route('admin.dashboard')); ?>">Dashboard</a></li>
                    <li><a href="<?php echo e(route('admin.security.dashboard')); ?>">Security</a></li>
                    <li>Logs</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h4 class="card-title mb-4">Recent Security Events</h4>
            
            <!-- Filter Form -->
            <form method="GET" action="<?php echo e(route('admin.security.logs')); ?>" class="mb-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label for="search" class="label">
                            <span class="label-text">Cari</span>
                        </label>
                        <div class="input-group">
                            <input type="text" name="search" id="search" class="input input-bordered w-full" placeholder="Cari log..." value="<?php echo e(request('search')); ?>">
                            <button class="btn btn-primary" type="submit">Cari</button>
                        </div>
                    </div>
                    <div>
                        <label for="type" class="label">
                            <span class="label-text">Tipe Log</span>
                        </label>
                        <select name="type" id="type" class="select select-bordered w-full">
                            <option value="">Semua Tipe</option>
                            <option value="info" <?php echo e(request('type') == 'info' ? 'selected' : ''); ?>>Info</option>
                            <option value="warning" <?php echo e(request('type') == 'warning' ? 'selected' : ''); ?>>Warning</option>
                            <option value="error" <?php echo e(request('type') == 'error' ? 'selected' : ''); ?>>Error</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button class="btn btn-primary w-full" type="submit">Terapkan Filter</button>
                    </div>
                </div>
            </form>

            <!-- Security Logs Table -->
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Level</th>
                            <th>Message</th>
                            <th>Context</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($log['timestamp']); ?></td>
                                <td>
                                    <span class="badge 
                                        <?php if($log['level'] == 'info'): ?> badge-info
                                        <?php elseif($log['level'] == 'warning'): ?> badge-warning
                                        <?php elseif($log['level'] == 'error'): ?> badge-error
                                        <?php else: ?> badge-neutral <?php endif; ?>">
                                        <?php echo e(ucfirst($log['level'])); ?>

                                    </span>
                                </td>
                                <td><?php echo e($log['message']); ?></td>
                                <td>
                                    <?php if(isset($log['context'])): ?>
                                        <pre class="whitespace-pre-wrap text-xs bg-base-200 p-2 rounded"><?php echo e(json_encode($log['context'], JSON_PRETTY_PRINT)); ?></pre>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="text-center py-16">
                                    <i class="fas fa-file-alt fa-5x text-base-content/20 mb-3"></i>
                                    <h5 class="text-lg font-bold text-base-content/70">Tidak ada log keamanan ditemukan.</h5>
                                    <p class="text-base-content/50">Log akan ditampilkan di sini ketika tersedia.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination (if applicable) -->
            
            
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/admin/security/logs.blade.php ENDPATH**/ ?>