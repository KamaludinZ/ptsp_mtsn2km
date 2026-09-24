<?php $__env->startSection('content'); ?>
    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-6">Tiket Saya</h1>

                    <?php if($tickets->isEmpty()): ?>
                        <p class="text-gray-500">Anda belum memiliki tiket layanan.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Tiket</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Layanan</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900"><?php echo e($ticket->ticket_number); ?></td>
                                            <td class="px-4 py-3 text-sm text-gray-500"><?php echo e($ticket->service->name ?? '-'); ?></td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    <?php echo e(ucfirst(str_replace('_', ' ', $ticket->status))); ?>

                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500"><?php echo e($ticket->created_at->format('d M Y')); ?></td>
                                            <td class="px-4 py-3 text-sm">
                                                <a href="<?php echo e(route('onlineportal.ticket.detail', $ticket->ticket_number)); ?>" class="text-blue-600 hover:underline">Lihat Detail</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-6">
                            <?php echo e($tickets->links()); ?>

                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/onlineportal/my-tickets.blade.php ENDPATH**/ ?>