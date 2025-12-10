<?php $__env->startSection('title', 'Detail Pengaduan: ' . $complaint->complaint_number); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Detail Pengaduan</h4>
                
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('suadmin.dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('suadmin.complaints.index')); ?>">Pengaduan</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Nomor Pengaduan: <?php echo e($complaint->complaint_number); ?></h4>
                    <div>
                        <a href="<?php echo e(route('suadmin.complaints.edit', $complaint)); ?>" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <a href="<?php echo e(route('suadmin.complaints.index')); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Jenis</label>
                                <p class="mb-0">
                                    <span class="badge bg-<?php echo e($complaint->type == 'pengaduan' ? 'danger' : 'success'); ?>">
                                        <?php echo e(ucfirst($complaint->type)); ?>

                                    </span>
                                </p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Subjek</label>
                                <p class="mb-0"><?php echo e($complaint->subject); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Kategori</label>
                                <p class="mb-0"><?php echo e($complaint->category ? ucfirst($complaint->category) : 'Tidak Ada'); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Prioritas</label>
                                <p class="mb-0">
                                    <span class="priority-badge 
                                        <?php echo e($complaint->priority == 'low' ? 'low-priority' : ''); ?>

                                        <?php echo e($complaint->priority == 'normal' ? 'normal-priority' : ''); ?>

                                        <?php echo e($complaint->priority == 'high' ? 'high-priority' : ''); ?>

                                        <?php echo e($complaint->priority == 'urgent' ? 'urgent-priority' : ''); ?>

                                    ">
                                        <?php echo e(ucfirst($complaint->priority)); ?>

                                    </span>
                                </p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <p class="mb-0">
                                    <span class="status-badge 
                                        <?php echo e($complaint->status == 'pending' ? 'pending' : ''); ?>

                                        <?php echo e($complaint->status == 'in_review' ? 'in_review' : ''); ?>

                                        <?php echo e($complaint->status == 'resolved' ? 'resolved' : ''); ?>

                                        <?php echo e($complaint->status == 'closed' ? 'closed' : ''); ?>

                                    ">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $complaint->status))); ?>

                                    </span>
                                </p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Pelapor</label>
                                <p class="mb-0"><?php echo e($complaint->reporter_name ?: 'Tidak disebutkan'); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email Pelapor</label>
                                <p class="mb-0"><?php echo e($complaint->reporter_email ?: 'Tidak disebutkan'); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Telepon Pelapor</label>
                                <p class="mb-0"><?php echo e($complaint->reporter_phone ?: 'Tidak disebutkan'); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Layanan</label>
                                <p class="mb-0"><?php echo e($complaint->service ? $complaint->service->name : 'Tidak Ada'); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ditugaskan Ke</label>
                                <p class="mb-0"><?php echo e($complaint->assignedTo ? $complaint->assignedTo->name : 'Belum Ditugaskan'); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi</label>
                        <p class="mb-0"><?php echo e($complaint->description); ?></p>
                    </div>
                    
                    <?php if($complaint->response): ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Respon</label>
                        <p class="mb-0"><?php echo e($complaint->response); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Dibuat Tanggal</label>
                                <p class="mb-0"><?php echo e($complaint->created_at->format('d M Y H:i')); ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Diperbarui Tanggal</label>
                                <p class="mb-0"><?php echo e($complaint->updated_at->format('d M Y H:i')); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <?php if($complaint->is_whistleblowing): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Ini adalah pengaduan whistleblowing
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .priority-badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .low-priority { background-color: #dbeafe; color: #1e40af; }
    .normal-priority { background-color: #d1fae5; color: #065f46; }
    .high-priority { background-color: #fef3c7; color: #92400e; }
    .urgent-priority { background-color: #fee2e2; color: #b91c1c; }
    
    .status-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .pending { background-color: #fef3c7; color: #92400e; }
    .in_review { background-color: #dbeafe; color: #1e40af; }
    .resolved { background-color: #d1fae5; color: #065f46; }
    .closed { background-color: #e5e7eb; color: #374151; }
</style>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\admin\complaints\show.blade.php ENDPATH**/ ?>