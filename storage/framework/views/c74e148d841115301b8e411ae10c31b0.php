<?php $__env->startSection('title', 'Edit Pengumuman - Admin'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h2 class="h3 mb-0">✏️ Edit Pengumuman</h2>
                    <p class="text-muted mb-0">Perbarui informasi pengumuman yang dipilih</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="<?php echo e(route('admin.pengumuman.index')); ?>" class="btn btn-secondary shadow-sm">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form action="<?php echo e(route('admin.pengumuman.update', $pengumuman->id)); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label fw-bold">Judul Pengumuman <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control shadow-sm <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="title" 
                                           name="title" 
                                           value="<?php echo e(old('title', $pengumuman->title)); ?>" 
                                           placeholder="Masukkan judul pengumuman">
                                    <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div class="mb-3">
                                    <label for="content" class="form-label fw-bold">Isi Pengumuman <span class="text-danger">*</span></label>
                                    <textarea class="form-control shadow-sm <?php $__errorArgs = ['content'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                              id="content" 
                                              name="content" 
                                              rows="10" 
                                              placeholder="Masukkan isi pengumuman"><?php echo e(old('content', $pengumuman->content)); ?></textarea>
                                    <?php $__errorArgs = ['content'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="category" class="form-label fw-bold">Kategori</label>
                                    <select class="form-select shadow-sm <?php $__errorArgs = ['category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                            id="category" 
                                            name="category">
                                        <option value="">Pilih Kategori</option>
                                        <option value="akademik" <?php echo e(old('category', $pengumuman->category) == 'akademik' ? 'selected' : ''); ?>>Akademik</option>
                                        <option value="administrasi" <?php echo e(old('category', $pengumuman->category) == 'administrasi' ? 'selected' : ''); ?>>Administrasi</option>
                                        <option value="kegiatan" <?php echo e(old('category', $pengumuman->category) == 'kegiatan' ? 'selected' : ''); ?>>Kegiatan</option>
                                        <option value="lainnya" <?php echo e(old('category', $pengumuman->category) == 'lainnya' ? 'selected' : ''); ?>>Lainnya</option>
                                    </select>
                                    <?php $__errorArgs = ['category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div class="mb-3">
                                    <label for="author" class="form-label fw-bold">Penulis</label>
                                    <input type="text" 
                                           class="form-control shadow-sm <?php $__errorArgs = ['author'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="author" 
                                           name="author" 
                                           value="<?php echo e(old('author', $pengumuman->author)); ?>" 
                                           placeholder="Nama penulis pengumuman">
                                    <?php $__errorArgs = ['author'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div class="mb-3">
                                    <label for="publish_date" class="form-label fw-bold">Tanggal Publikasi <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           class="form-control shadow-sm <?php $__errorArgs = ['publish_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="publish_date" 
                                           name="publish_date" 
                                           value="<?php echo e(old('publish_date', $pengumuman->publish_date->format('Y-m-d'))); ?>">
                                    <?php $__errorArgs = ['publish_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div class="mb-3">
                                    <label for="end_date" class="form-label fw-bold">Tanggal Berakhir</label>
                                    <input type="date" 
                                           class="form-control shadow-sm <?php $__errorArgs = ['end_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="end_date" 
                                           name="end_date" 
                                           value="<?php echo e(old('end_date', $pengumuman->end_date ? $pengumuman->end_date->format('Y-m-d') : '')); ?>">
                                    <div class="form-text">Kosongkan jika tidak memiliki tanggal berakhir</div>
                                    <?php $__errorArgs = ['end_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div class="mb-3">
                                    <label for="attachment" class="form-label fw-bold">File Edaran</label>
                                    <input type="file" 
                                           class="form-control shadow-sm <?php $__errorArgs = ['attachment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="attachment" 
                                           name="attachment" 
                                           accept=".pdf,.doc,.docx">
                                    <div class="form-text">Format: PDF, DOC, DOCX. Maksimal ukuran: 10MB</div>
                                    <?php $__errorArgs = ['attachment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    
                                    <?php if($pengumuman->attachment): ?>
                                        <div class="mt-2">
                                            <label class="form-label fw-bold">File Saat Ini:</label><br>
                                            <a href="<?php echo e(asset('storage/' . $pengumuman->attachment)); ?>" target="_blank" class="text-primary">
                                                <i class="fas fa-file-pdf me-1"></i>
                                                <?php echo e(basename($pengumuman->attachment)); ?>

                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="url" class="form-label fw-bold">URL Dokumen (opsional)</label>
                                    <input type="url" 
                                           class="form-control shadow-sm <?php $__errorArgs = ['url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="url" 
                                           name="url" 
                                           value="<?php echo e(old('url', $pengumuman->url)); ?>"
                                           placeholder="https://contoh.com/dokumen">
                                    <div class="form-text">Gunakan ini jika dokumen berada di situs eksternal (contoh: Google Drive, website lain)</div>
                                    <?php $__errorArgs = ['url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    
                                    <?php if($pengumuman->url): ?>
                                        <div class="mt-2">
                                            <label class="form-label fw-bold">URL Saat Ini:</label><br>
                                            <a href="<?php echo e($pengumuman->url); ?>" target="_blank" class="text-primary">
                                                <i class="fas fa-external-link-alt me-1"></i>
                                                <?php echo e($pengumuman->url); ?>

                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               role="switch" 
                                               id="is_active" 
                                               name="is_active" 
                                               value="1" 
                                               <?php echo e(old('is_active', $pengumuman->is_active) ? 'checked' : ''); ?>>
                                        <label class="form-check-label fw-bold" for="is_active">Aktif</label>
                                    </div>
                                    <div class="form-text">Jika tidak dicentang, pengumuman tidak akan ditampilkan</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?php echo e(route('admin.pengumuman.index')); ?>" class="btn btn-secondary shadow-sm">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary shadow-sm">
                                <i class="fas fa-save me-2"></i>Update Pengumuman
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/pengumuman/admin/edit.blade.php ENDPATH**/ ?>