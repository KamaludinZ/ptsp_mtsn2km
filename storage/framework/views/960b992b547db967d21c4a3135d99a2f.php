<?php $__env->startSection('title', 'Edit Dokumen Hasil - ' . $ticket->ticket_number); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Edit Dokumen Hasil</h1>
                <a href="<?php echo e(route('suadmin.tickets.show', $ticket)); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="<?php echo e(route('suadmin.tickets.output.update', [$ticket, $output])); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Jenis Output</label>
                                    <select name="output_type" class="form-select" required>
                                        <option value="digital" <?php echo e($output->output_type == 'digital' ? 'selected' : ''); ?>>Digital</option>
                                        <option value="physical" <?php echo e($output->output_type == 'physical' ? 'selected' : ''); ?>>Fisik</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">File Saat Ini</label>
                                    <br>
                                    <?php if($output->file_path): ?>
                                        <a href="<?php echo e(Storage::url($output->file_path)); ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-file-download me-1"></i>Unduh File
                                        </a>
                                    <?php else: ?>
                                        <em>Tidak ada file</em>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Unggah Ulang File (Opsional)</label>
                            <input type="file" name="output_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            <div class="form-text">Kosongkan jika tidak ingin mengganti file</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="output_description" class="form-control" rows="3"><?php echo e(old('output_description', $output->output_description)); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_delivered" id="is_delivered" 
                                               <?php echo e($output->is_delivered ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_delivered">
                                            Sudah Dikirim/Diserahkan
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Pengiriman</label>
                                    <input type="date" name="delivery_date" class="form-control" 
                                           value="<?php echo e(old('delivery_date', $output->delivery_date)); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Dikirim Kepada</label>
                            <select name="delivered_to" class="form-select">
                                <option value="">Pilih Penerima</option>
                                <?php $__currentLoopData = $ticket->service->users ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($user->id); ?>" 
                                        <?php echo e(old('delivered_to', $output->delivered_to) == $user->id ? 'selected' : ''); ?>>
                                        <?php echo e($user->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?php echo e(route('suadmin.tickets.show', $ticket)); ?>" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\admin\tickets\edit_output.blade.php ENDPATH**/ ?>