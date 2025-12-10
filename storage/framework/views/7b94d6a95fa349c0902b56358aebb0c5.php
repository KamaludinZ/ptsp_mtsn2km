<?php $__env->startSection('title', 'Detail Kategori Layanan - ' . $serviceCategory->name); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Detail Kategori Layanan: <?php echo e($serviceCategory->name); ?></h4>
                    <div>
                        <a href="<?php echo e(route('suadmin.service-categories.edit', $serviceCategory)); ?>" class="btn btn-light me-2">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="<?php echo e(route('suadmin.service-categories.index')); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Kategori</label>
                                <p class="form-control-plaintext"><?php echo e($serviceCategory->name); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Slug</label>
                                <p class="form-control-plaintext"><?php echo e($serviceCategory->slug); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Parent Category</label>
                                <p class="form-control-plaintext"><?php echo e($serviceCategory->parent->name ?? 'Tidak ada parent'); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Urutan</label>
                                <p class="form-control-plaintext"><?php echo e($serviceCategory->sort_order); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status Aktif</label>
                                <p class="form-control-plaintext">
                                    <?php if($serviceCategory->is_active): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Tidak Aktif</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Icon</label>
                                <p class="form-control-plaintext"><?php echo e($serviceCategory->icon ?? 'Tidak ada icon'); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Deskripsi</label>
                                <p class="form-control-plaintext"><?php echo e($serviceCategory->description ?? '-'); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Dibuat Tanggal</label>
                                <p class="form-control-plaintext"><?php echo e($serviceCategory->created_at->format('d M Y H:i')); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Diperbarui Tanggal</label>
                                <p class="form-control-plaintext"><?php echo e($serviceCategory->updated_at->format('d M Y H:i')); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end mt-4">
                        <a href="<?php echo e(route('suadmin.service-categories.edit', $serviceCategory)); ?>" class="btn btn-primary me-2">
                            <i class="fas fa-edit"></i> Edit Kategori
                        </a>
                        <a href="<?php echo e(route('suadmin.service-categories.index')); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\admin\service_categories\show.blade.php ENDPATH**/ ?>