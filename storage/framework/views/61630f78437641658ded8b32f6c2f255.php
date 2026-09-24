<?php $__env->startSection('content'); ?>
    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <?php if(session('success')): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"><?php echo e(session('success')); ?></div>
            <?php endif; ?>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h1 class="text-2xl font-bold"><?php echo e($ticket->ticket_number); ?></h1>
                            <p class="text-gray-500"><?php echo e($ticket->service->name ?? '-'); ?> &middot; <?php echo e($ticket->user->name ?? '-'); ?></p>
                        </div>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            <?php echo e(ucfirst(str_replace('_', ' ', $ticket->status))); ?>

                        </span>
                    </div>
                    <p class="text-gray-700 mb-2"><strong>Petugas:</strong> <?php echo e($ticket->assignedTo->name ?? 'Belum ditugaskan'); ?></p>
                    <p class="text-gray-700"><strong>Keterangan:</strong> <?php echo e($ticket->notes); ?></p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold mb-3">Tugaskan Tiket</h2>
                        <form method="POST" action="<?php echo e(route('backoffice.tickets.assign', $ticket)); ?>">
                            <?php echo csrf_field(); ?>
                            <select name="assigned_to" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm mb-3">
                                <option value="">-- Pilih Petugas --</option>
                                <?php $__currentLoopData = $availableStaff; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $staff): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($staff->id); ?>"><?php echo e($staff->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <input type="text" name="notes" placeholder="Catatan (opsional)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm mb-3">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">Tugaskan</button>
                        </form>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold mb-3">Ubah Status</h2>
                        <form method="POST" action="<?php echo e(route('backoffice.tickets.update-status', $ticket)); ?>">
                            <?php echo csrf_field(); ?>
                            <select name="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm mb-3">
                                <?php $__currentLoopData = ['submitted' => 'Diajukan', 'verified' => 'Diverifikasi', 'in_process' => 'Diproses', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>" <?php echo e($ticket->status === $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <textarea name="notes" required placeholder="Keterangan perubahan status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm mb-3"></textarea>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">Perbarui Status</button>
                        </form>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold mb-3">Tambah Catatan</h2>
                        <form method="POST" action="<?php echo e(route('backoffice.tickets.add-note', $ticket)); ?>">
                            <?php echo csrf_field(); ?>
                            <textarea name="note" required placeholder="Catatan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm mb-3"></textarea>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">Simpan Catatan</button>
                        </form>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold mb-3">Unggah Berkas</h2>
                        <form method="POST" action="<?php echo e(route('backoffice.tickets.upload-file', $ticket)); ?>" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <input type="file" name="file" required class="mt-1 block w-full text-sm mb-3">
                            <input type="text" name="description" placeholder="Keterangan (opsional)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm mb-3">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">Unggah</button>
                        </form>
                    </div>
                </div>
            </div>

            <?php if($ticket->files->isNotEmpty()): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold mb-3">Berkas</h2>
                        <ul class="space-y-1 text-sm">
                            <?php $__currentLoopData = $ticket->files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>
                                    <a href="<?php echo e(route('backoffice.tickets.download-file', [$ticket, $file])); ?>" class="text-blue-600 hover:underline"><?php echo e($file->file_name); ?></a>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-semibold mb-3">Riwayat</h2>
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
            </div>

            <a href="<?php echo e(route('backoffice.tickets.queue')); ?>" class="text-blue-600 hover:underline">&larr; Kembali ke Antrian</a>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('backoffice.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/backoffice/ticket-detail.blade.php ENDPATH**/ ?>