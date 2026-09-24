<?php $__env->startSection('content'); ?>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h1 class="text-2xl font-bold"><?php echo e($ticket->ticket_number); ?></h1>
                            <p class="text-gray-500"><?php echo e($ticket->service->name ?? '-'); ?></p>
                        </div>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            <?php echo e(ucfirst(str_replace('_', ' ', $ticket->status))); ?>

                        </span>
                    </div>

                    <div class="mb-6">
                        <h2 class="text-sm font-semibold text-gray-600 mb-1">Keterangan</h2>
                        <p class="text-gray-800"><?php echo e($ticket->notes); ?></p>
                    </div>

                    <?php if($canDownload): ?>
                        <div class="mb-6">
                            <a href="<?php echo e(route('onlineportal.ticket.download', $ticket->ticket_number)); ?>" class="inline-block bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded">
                                Unduh Hasil Layanan
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if($ticket->files->isNotEmpty()): ?>
                        <div class="mb-6">
                            <h2 class="text-lg font-semibold mb-2">Berkas Terlampir</h2>
                            <ul class="list-disc list-inside text-sm text-gray-700">
                                <?php $__currentLoopData = $ticket->files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($file->file_name); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if($ticket->workflowSteps->isNotEmpty()): ?>
                        <div class="mb-6">
                            <h2 class="text-lg font-semibold mb-2">Progres Layanan</h2>
                            <ul class="space-y-1 text-sm">
                                <?php $__currentLoopData = $ticket->workflowSteps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="flex justify-between border-b py-1">
                                        <span><?php echo e($step->workflowStep->name ?? '-'); ?></span>
                                        <span class="text-gray-500"><?php echo e(ucfirst($step->status)); ?></span>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div>
                        <h2 class="text-lg font-semibold mb-2">Riwayat</h2>
                        <?php if($ticket->logs->isEmpty()): ?>
                            <p class="text-gray-500 text-sm">Belum ada riwayat.</p>
                        <?php else: ?>
                            <ul class="space-y-2 text-sm">
                                <?php $__currentLoopData = $ticket->logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="border-b pb-2">
                                        <span class="text-gray-800"><?php echo e($log->notes ?? ucfirst(str_replace('_', ' ', $log->action))); ?></span>
                                        <span class="block text-xs text-gray-400"><?php echo e($log->created_at->format('d M Y H:i')); ?></span>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <div class="mt-6">
                        <a href="<?php echo e(route('onlineportal.my-tickets')); ?>" class="text-blue-600 hover:underline">&larr; Kembali ke Tiket Saya</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/onlineportal/ticket-detail.blade.php ENDPATH**/ ?>