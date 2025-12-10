<?php $__env->startSection('title', 'Detail Layanan - ' . $service->name); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Detail Layanan: <?php echo e($service->name); ?></h4>
                    <div>
                        <a href="<?php echo e(route('suadmin.services.edit', $service)); ?>" class="btn btn-light me-2">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="<?php echo e(route('suadmin.services.index')); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Layanan</label>
                                <p class="form-control-plaintext"><?php echo e($service->name); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Slug</label>
                                <p class="form-control-plaintext"><?php echo e($service->slug); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Kategori</label>
                                <p class="form-control-plaintext"><?php echo e($service->category->name ?? 'Tidak ada kategori'); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Mode</label>
                                <span class="badge 
                                    <?php if($service->mode == 'online'): ?> bg-success
                                    <?php elseif($service->mode == 'offline'): ?> bg-warning
                                    <?php elseif($service->mode == 'both'): ?> bg-info
                                    <?php else: ?> bg-secondary <?php endif; ?>">
                                    <?php echo e(ucfirst($service->mode)); ?>

                                </span>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status Aktif</label>
                                <p class="form-control-plaintext">
                                    <?php if($service->is_active): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Tidak Aktif</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Produk Digital</label>
                                <p class="form-control-plaintext">
                                    <?php if($service->is_digital_product): ?>
                                        <span class="badge bg-success">Ya</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Tidak</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Estimasi Hari</label>
                                <p class="form-control-plaintext"><?php echo e($service->estimated_days ?? 'Tidak ditentukan'); ?></p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Deskripsi</label>
                                <p class="form-control-plaintext"><?php echo e($service->description ?? '-'); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Dibuat Tanggal</label>
                                <p class="form-control-plaintext"><?php echo e($service->created_at->format('d M Y H:i')); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Diperbarui Tanggal</label>
                                <p class="form-control-plaintext"><?php echo e($service->updated_at->format('d M Y H:i')); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h5 class="mt-4">14 Komponen Standar Pelayanan</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">1. Dasar Hukum</label>
                                <p class="form-control-plaintext"><?php echo e($service->dasar_hukum ?? '-'); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">2. Persyaratan</label>
                                <p class="form-control-plaintext"><?php echo e($service->persyaratan ?? '-'); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">3. Sistem, Mekanisme, Prosedur</label>
                                <p class="form-control-plaintext"><?php echo e($service->mekanisme ?? '-'); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">4. Jangka Waktu Penyelesaian</label>
                                <p class="form-control-plaintext"><?php echo e($service->jangka_waktu ?? '-'); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">5. Biaya/Tarif</label>
                                <p class="form-control-plaintext"><?php echo e($service->biaya_tarif ?? '-'); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">6. Produk Pelayanan</label>
                                <p class="form-control-plaintext"><?php echo e($service->produk_layanan ?? '-'); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">7. Sarana & Prasarana</label>
                                <p class="form-control-plaintext"><?php echo e($service->sarpras ?? '-'); ?></p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">8. Kompetensi Pelaksana</label>
                                <p class="form-control-plaintext"><?php echo e($service->kompetensi_pelaksana ?? '-'); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">9. Pengawasan Internal</label>
                                <p class="form-control-plaintext"><?php echo e($service->pengawasan_internal ?? '-'); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">10. Penanganan Pengaduan</label>
                                <p class="form-control-plaintext"><?php echo e($service->penanganan_pengaduan ?? '-'); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">11. Jumlah Pelaksana</label>
                                <p class="form-control-plaintext"><?php echo e($service->jumlah_pelaksana ?? '-'); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">12. Jaminan Pelayanan</label>
                                <p class="form-control-plaintext"><?php echo e($service->jaminan_pelayanan ?? '-'); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">13. Jaminan Keamanan</label>
                                <p class="form-control-plaintext"><?php echo e($service->jaminan_keamanan ?? '-'); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">14. Evaluasi Kinerja</label>
                                <p class="form-control-plaintext"><?php echo e($service->evaluasi_kinerja ?? '-'); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end mt-4">
                        <a href="<?php echo e(route('suadmin.services.edit', $service)); ?>" class="btn btn-primary me-2">
                            <i class="fas fa-edit"></i> Edit Layanan
                        </a>
                        <a href="<?php echo e(route('suadmin.services.index')); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\admin\services\show.blade.php ENDPATH**/ ?>