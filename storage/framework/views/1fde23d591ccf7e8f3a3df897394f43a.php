<?php $__env->startSection('content'); ?>
    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold">Laporan Kinerja</h1>
                        <form method="GET" class="flex gap-2">
                            <select name="period" onchange="this.form.submit()" class="border rounded px-3 py-2 text-sm">
                                <option value="week" <?php echo e($period === 'week' ? 'selected' : ''); ?>>Minggu Ini</option>
                                <option value="month" <?php echo e($period === 'month' ? 'selected' : ''); ?>>Bulan Ini</option>
                                <option value="quarter" <?php echo e($period === 'quarter' ? 'selected' : ''); ?>>Kuartal Ini</option>
                                <option value="year" <?php echo e($period === 'year' ? 'selected' : ''); ?>>Tahun Ini</option>
                            </select>
                        </form>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-blue-50 p-6 rounded-lg shadow">
                            <h2 class="text-sm font-semibold text-gray-600 mb-1">Total Tiket</h2>
                            <p class="text-3xl font-bold text-blue-600"><?php echo e($reportData['total_tickets']); ?></p>
                        </div>
                        <div class="bg-green-50 p-6 rounded-lg shadow">
                            <h2 class="text-sm font-semibold text-gray-600 mb-1">Selesai</h2>
                            <p class="text-3xl font-bold text-green-600"><?php echo e($reportData['completed_tickets']); ?></p>
                        </div>
                        <div class="bg-purple-50 p-6 rounded-lg shadow">
                            <h2 class="text-sm font-semibold text-gray-600 mb-1">Rata-rata Waktu Selesai</h2>
                            <p class="text-3xl font-bold text-purple-600"><?php echo e($reportData['average_completion_time']); ?> <span class="text-base font-normal">menit</span></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h2 class="text-lg font-semibold mb-3">Tiket per Layanan</h2>
                            <?php if($reportData['tickets_by_service']->isEmpty()): ?>
                                <p class="text-gray-500 text-sm">Tidak ada data.</p>
                            <?php else: ?>
                                <ul class="divide-y divide-gray-100 border rounded-lg">
                                    <?php $__currentLoopData = $reportData['tickets_by_service']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li class="px-4 py-2 flex justify-between">
                                            <span><?php echo e($row->service->name ?? 'Tidak diketahui'); ?></span>
                                            <span class="font-semibold"><?php echo e($row->count); ?></span>
                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold mb-3">Tiket per Status</h2>
                            <?php if($reportData['tickets_by_status']->isEmpty()): ?>
                                <p class="text-gray-500 text-sm">Tidak ada data.</p>
                            <?php else: ?>
                                <ul class="divide-y divide-gray-100 border rounded-lg">
                                    <?php $__currentLoopData = $reportData['tickets_by_status']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li class="px-4 py-2 flex justify-between">
                                            <span><?php echo e(ucfirst(str_replace('_', ' ', $row->status))); ?></span>
                                            <span class="font-semibold"><?php echo e($row->count); ?></span>
                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('backoffice.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/backoffice/reports.blade.php ENDPATH**/ ?>