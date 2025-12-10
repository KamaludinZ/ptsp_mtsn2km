<?php $__env->startSection('title', 'Detail Whistle Blowing - ' . $complaint->complaint_number); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">🛡️ Detail Laporan Whistleblowing</h1>
            <p class="text-muted mb-0">Nomor Tiket: <strong><?php echo e($complaint->complaint_number); ?></strong></p>
        </div>
        <div>
            <a href="<?php echo e(route('suadmin.whistleblowing.index')); ?>" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <!-- Complaint Details Card -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary bg-opacity-10 border-0 pt-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-primary">
                            <i class="fas fa-shield-alt me-2"></i>Detail Pelanggaran
                        </h5>
                        <span class="badge bg-primary text-white px-3 py-2">
                            <?php echo e(ucfirst(str_replace('_', ' ', $complaint->status))); ?>

                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h3 class="fw-bold mb-3"><?php echo e($complaint->title); ?></h3>
                        <div class="d-flex flex-wrap gap-3 mb-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar text-primary me-2"></i>
                                <span><?php echo e($complaint->created_at->format('d M Y H:i')); ?></span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-secret text-warning me-2"></i>
                                <span>Anonymous Report</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-map-marker-alt text-info me-2"></i>
                                <span><?php echo e($complaint->location ?? 'N/A'); ?></span>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <div class="d-flex">
                                <i class="fas fa-exclamation-triangle text-info me-3 fs-4"></i>
                                <div>
                                    <h6 class="alert-heading">Deskripsi Laporan</h6>
                                    <p class="mb-0"><?php echo e($complaint->description); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Incident Details -->
                        <div class="mb-4">
                            <h5 class="mb-3">📋 Detail Insiden</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Tanggal Kejadian</label>
                                        <p class="mb-0"><?php echo e($complaint->incident_date ? $complaint->incident_date->format('d M Y') : 'Tidak disebutkan'); ?></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Lokasi Kejadian</label>
                                        <p class="mb-0"><?php echo e($complaint->incident_location ?? 'Tidak disebutkan'); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Pihak Terlibat</label>
                                        <p class="mb-0"><?php echo e($complaint->involved_parties ?? 'Tidak disebutkan'); ?></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Tingkat Kerahasiaan</label>
                                        <p class="mb-0">
                                            <span class="badge bg-success">
                                                <?php if($complaint->is_confidential): ?> Rahasia <?php else: ?> Tidak Rahasia <?php endif; ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Evidence Files -->
                        <?php if($complaint->evidence_files): ?>
                        <div class="mb-4">
                            <h5 class="mb-3">📁 Bukti-Bukti</h5>
                            <div class="row">
                                <?php $__currentLoopData = json_decode($complaint->evidence_files); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card border h-100 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                                            <p class="card-text small mb-1"><?php echo e(basename($file)); ?></p>
                                            <a href="<?php echo e(Storage::url($file)); ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-download me-1"></i>Lihat
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Panel -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-amber-600 text-white border-0 pt-4 pb-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-sliders-h me-2"></i>Tindakan
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('suadmin.whistleblowing.update-status', $complaint->id)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="pending" <?php echo e($complaint->status == 'pending' ? 'selected' : ''); ?>>Pending</option>
                                <option value="processing" <?php echo e($complaint->status == 'processing' ? 'selected' : ''); ?>>Processing</option>
                                <option value="completed" <?php echo e($complaint->status == 'completed' ? 'selected' : ''); ?>>Completed</option>
                                <option value="rejected" <?php echo e($complaint->status == 'rejected' ? 'selected' : ''); ?>>Rejected</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Catatan Tanggapan</label>
                            <textarea name="response_notes" class="form-control" rows="4" 
                                      placeholder="Tambahkan catatan tanggapan terhadap laporan ini..."><?php echo e($complaint->response_notes); ?></textarea>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Status
                            </button>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <!-- Report Info -->
                    <div class="mb-3">
                        <h6 class="fw-bold mb-3">📋 Informasi Laporan</h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <small class="text-muted d-block">Tipe Laporan</small>
                                <span class="badge bg-warning">Whistleblowing</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Tanggal dibuat</small>
                                <span><?php echo e($complaint->created_at->format('d/m/Y')); ?></span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Terakhir diperbarui</small>
                                <span><?php echo e($complaint->updated_at->format('d/m/Y H:i')); ?></span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Ditangani oleh</small>
                                <span><?php echo e($complaint->assignee->name ?? 'Belum ditangani'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Actions -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-surface border-0 pt-4 pb-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cogs me-2"></i>Tindakan Lainnya
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="mailto:<?php echo e($complaint->email); ?>" class="btn btn-outline-primary">
                            <i class="fas fa-envelope me-2"></i>Kontak Pelapor
                        </a>
                        <button class="btn btn-outline-info" onclick="printReport(<?php echo e($complaint->id); ?>)">
                            <i class="fas fa-print me-2"></i>Cetak Laporan
                        </button>
                        <a href="<?php echo e(route('complaints.export', $complaint->id)); ?>" class="btn btn-outline-success">
                            <i class="fas fa-file-export me-2"></i>Ekspor
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function printReport(reportId) {
    // Implementasi fungsi cetak laporan
    alert('Fungsi cetak laporan untuk ID: ' + reportId);
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\admin\complaints\whistleblowing\show.blade.php ENDPATH**/ ?>