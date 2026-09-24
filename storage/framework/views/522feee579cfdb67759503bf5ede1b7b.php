<?php $__env->startSection('content'); ?>
    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold">Kinerja Pelayanan</h1>
                        <form method="GET" class="flex gap-2">
                            <select name="period" onchange="this.form.submit()" class="border rounded px-3 py-2 text-sm">
                                <option value="week" <?php echo e($period === 'week' ? 'selected' : ''); ?>>Minggu Ini</option>
                                <option value="month" <?php echo e($period === 'month' ? 'selected' : ''); ?>>Bulan Ini</option>
                                <option value="quarter" <?php echo e($period === 'quarter' ? 'selected' : ''); ?>>Kuartal Ini</option>
                                <option value="year" <?php echo e($period === 'year' ? 'selected' : ''); ?>>Tahun Ini</option>
                            </select>
                        </form>
                    </div>

                    <h2 class="text-lg font-semibold mb-3">Tiket Layanan</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-blue-50 p-6 rounded-lg shadow">
                            <h3 class="text-sm font-semibold text-gray-600 mb-1">Total Tiket</h3>
                            <p class="text-3xl font-bold text-blue-600"><?php echo e($ticketStats['total']); ?></p>
                        </div>
                        <div class="bg-green-50 p-6 rounded-lg shadow">
                            <h3 class="text-sm font-semibold text-gray-600 mb-1">Selesai</h3>
                            <p class="text-3xl font-bold text-green-600"><?php echo e($ticketStats['completed']); ?></p>
                        </div>
                        <div class="bg-purple-50 p-6 rounded-lg shadow">
                            <h3 class="text-sm font-semibold text-gray-600 mb-1">Rata-rata Waktu Selesai</h3>
                            <p class="text-3xl font-bold text-purple-600"><?php echo e($ticketStats['average_time']); ?> <span class="text-base font-normal">menit</span></p>
                        </div>
                    </div>

                    <h2 class="text-lg font-semibold mb-3">Pengaduan</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="bg-yellow-50 p-6 rounded-lg shadow">
                            <h3 class="text-sm font-semibold text-gray-600 mb-1">Total Pengaduan</h3>
                            <p class="text-3xl font-bold text-yellow-600"><?php echo e($complaintStats['total']); ?></p>
                        </div>
                        <div class="bg-green-50 p-6 rounded-lg shadow">
                            <h3 class="text-sm font-semibold text-gray-600 mb-1">Selesai</h3>
                            <p class="text-3xl font-bold text-green-600"><?php echo e($complaintStats['completed']); ?></p>
                        </div>
                    </div>

                    <?php if($complaintStats['by_type']->isNotEmpty()): ?>
                        <div class="mb-8">
                            <h3 class="text-sm font-semibold text-gray-600 mb-2">Pengaduan per Jenis</h3>
                            <ul class="divide-y divide-gray-100 border rounded-lg">
                                <?php $__currentLoopData = $complaintStats['by_type']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="px-4 py-2 flex justify-between">
                                        <span><?php echo e(ucfirst($row->complaint_type)); ?></span>
                                        <span class="font-semibold"><?php echo e($row->count); ?></span>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <h2 class="text-lg font-semibold mb-3">Survei</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-blue-50 p-6 rounded-lg shadow">
                            <h3 class="text-sm font-semibold text-gray-600 mb-1">Total Responden</h3>
                            <p class="text-3xl font-bold text-blue-600"><?php echo e($surveyStats['total_responses']); ?></p>
                        </div>
                        <div class="bg-green-50 p-6 rounded-lg shadow">
                            <h3 class="text-sm font-semibold text-gray-600 mb-1">Indeks Kepuasan (SKM)</h3>
                            <p class="text-3xl font-bold text-green-600"><?php echo e($surveyStats['satisfaction_index']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('supervision.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/supervision/performance.blade.php ENDPATH**/ ?>