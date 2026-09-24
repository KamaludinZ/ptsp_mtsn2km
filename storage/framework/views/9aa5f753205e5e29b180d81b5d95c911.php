<?php $__env->startSection('content'); ?>
    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-6">Manajemen Survey</h1>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div class="bg-blue-50 p-6 rounded-lg shadow">
                            <h2 class="text-sm font-semibold text-gray-600 mb-1">Total Survey</h2>
                            <p class="text-3xl font-bold text-blue-600"><?php echo e($stats['total_surveys']); ?></p>
                        </div>
                        <div class="bg-green-50 p-6 rounded-lg shadow">
                            <h2 class="text-sm font-semibold text-gray-600 mb-1">Total Responden</h2>
                            <p class="text-3xl font-bold text-green-600"><?php echo e($stats['total_responses']); ?></p>
                        </div>
                        <div class="bg-purple-50 p-6 rounded-lg shadow">
                            <h2 class="text-sm font-semibold text-gray-600 mb-1">Responden SKM</h2>
                            <p class="text-3xl font-bold text-purple-600"><?php echo e($stats['skm_responses']); ?></p>
                        </div>
                        <div class="bg-orange-50 p-6 rounded-lg shadow">
                            <h2 class="text-sm font-semibold text-gray-600 mb-1">Responden SPAK</h2>
                            <p class="text-3xl font-bold text-orange-600"><?php echo e($stats['spak_responses']); ?></p>
                        </div>
                    </div>

                    <h2 class="text-lg font-semibold mb-4">Daftar Survey</h2>

                    <?php if($surveys->isEmpty()): ?>
                        <p class="text-gray-500">Belum ada survey.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Jumlah Soal</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php $__currentLoopData = $surveys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $survey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td class="px-4 py-3"><?php echo e($survey->name); ?></td>
                                            <td class="px-4 py-3"><?php echo e(strtoupper($survey->type)); ?></td>
                                            <td class="px-4 py-3">
                                                <?php echo e($survey->start_date?->format('d M Y')); ?>

                                                &ndash;
                                                <?php echo e($survey->end_date ? $survey->end_date->format('d M Y') : 'Berjalan'); ?>

                                            </td>
                                            <td class="px-4 py-3"><?php echo e($survey->questions->count()); ?></td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo e($survey->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                                                    <?php echo e($survey->is_active ? 'Aktif' : 'Nonaktif'); ?>

                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <a href="<?php echo e(route('supervision.survey.results', $survey->id)); ?>" class="text-blue-600 hover:underline">
                                                    Lihat Hasil
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('supervision.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/supervision/survey-management.blade.php ENDPATH**/ ?>