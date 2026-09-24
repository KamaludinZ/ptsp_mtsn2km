<?php $__env->startSection('title', 'Manajemen Pengumuman'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h2 class="h3 mb-0">📢 Manajemen Pengumuman</h2>
                    <p class="text-muted mb-0">Kelola semua pengumuman yang tersedia</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="<?php echo e(route('admin.pengumuman.create')); ?>" class="btn btn-primary shadow-sm">
                        <i class="fas fa-plus-circle me-2"></i>Tambah Pengumuman Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-amber-600 text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-table me-2"></i> Daftar Pengumuman</h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="?status=aktif">Hanya Aktif</a></li>
                                <li><a class="dropdown-item" href="?status=non-aktif">Hanya Non-Aktif</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo e(route('admin.pengumuman.index')); ?>">Tampilkan Semua</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Kategori</label>
                            <select class="form-select shadow-sm" id="categoryFilter">
                                <option value="">Semua Kategori</option>
                                <?php
                                    $categories = $pengumumen->pluck('category')->unique()->filter()->values();
                                ?>
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($category); ?>" <?php echo e(request('category') == $category ? 'selected' : ''); ?>>
                                        <?php echo e($category); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Cari Pengumuman</label>
                            <div class="input-group shadow-sm">
                                <input type="text" class="form-control" id="searchInput" placeholder="Cari judul pengumuman..." value="<?php echo e(request('search')); ?>">
                                <button class="btn btn-outline-secondary" type="button" id="searchButton">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-primary w-100 shadow-sm" id="applyFilters">
                                <i class="fas fa-filter me-2"></i>Terapkan Filter
                            </button>
                        </div>
                    </div>

                    <!-- Announcements Table -->
                    <div class="table-responsive rounded-3 shadow-sm">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr class="border-top border-bottom">
                                    <th>Judul</th>
                                    <th>Kategori</th>
                                    <th>Tanggal Publikasi</th>
                                    <th>Tanggal Berakhir</th>
                                    <th>Status</th>
                                    <th>Penulis</th>
                                    <th>File Edaran</th>
                                    <th>URL Eksternal</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $pengumumen; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pengumuman): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr class="align-middle border-bottom">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 me-3">
                                                    <i class="fas fa-bullhorn text-amber-600"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold"><?php echo e($pengumuman->title); ?></h6>
                                                    <small class="text-muted"><?php echo e(Str::limit(strip_tags($pengumuman->content), 50)); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo e($pengumuman->category ?? 'Umum'); ?></span>
                                        </td>
                                        <td><?php echo e($pengumuman->publish_date->format('d M Y')); ?></td>
                                        <td>
                                            <?php if($pengumuman->end_date): ?>
                                                <?php echo e($pengumuman->end_date->format('d M Y')); ?>

                                            <?php else: ?>
                                                <span class="text-muted">Tidak ada</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($pengumuman->is_active): ?>
                                                <span class="badge bg-success-subtle text-success">
                                                    <i class="fas fa-check-circle me-1"></i> Aktif
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger">
                                                    <i class="fas fa-times-circle me-1"></i> Tidak Aktif
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($pengumuman->author ?? 'N/A'); ?></td>
                                        <td>
                                            <?php if($pengumuman->attachment): ?>
                                                <a href="<?php echo e(asset('storage/' . $pengumuman->attachment)); ?>" 
                                                   target="_blank" 
                                                   class="btn btn-outline-info btn-sm shadow-sm" 
                                                   title="Lihat File">
                                                    <i class="fas fa-file"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($pengumuman->url): ?>
                                                <a href="<?php echo e($pengumuman->url); ?>" 
                                                   target="_blank" 
                                                   class="btn btn-outline-success btn-sm shadow-sm" 
                                                   title="Lihat URL">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group" role="group">
                                                <a href="<?php echo e(route('pengumuman.show', $pengumuman->id)); ?>" 
                                                   class="btn btn-outline-primary btn-sm shadow-sm" 
                                                   target="_blank"
                                                   title="Lihat detail pengumuman">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?php echo e(route('admin.pengumuman.edit', $pengumuman->id)); ?>" 
                                                   class="btn btn-outline-warning btn-sm shadow-sm" 
                                                   title="Edit pengumuman">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="<?php echo e(route('admin.pengumuman.destroy', $pengumuman->id)); ?>" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengumuman ini?')"> 
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" 
                                                            class="btn btn-outline-danger btn-sm shadow-sm" 
                                                            title="Hapus pengumuman">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center justify-content-center">
                                                <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted">Tidak Ada Pengumuman Ditemukan</h5>
                                                <p class="text-muted">Silakan tambahkan pengumuman baru atau sesuaikan filter pencarian Anda</p>
                                                <a href="<?php echo e(route('admin.pengumuman.create')); ?>" class="btn btn-primary mt-2">
                                                    <i class="fas fa-plus me-2"></i>Tambah Pengumuman
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        <?php echo e($pengumumen->withQueryString()->links()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Apply filters
    document.getElementById('applyFilters').addEventListener('click', function() {
        const category = document.getElementById('categoryFilter').value;
        const search = document.getElementById('searchInput').value;
        
        let url = '<?php echo e(route('admin.pengumuman.index')); ?>';
        const params = [];
        
        if (category) params.push('category=' + category);
        if (search) params.push('search=' + encodeURIComponent(search));
        
        if (params.length > 0) {
            url += '?' + params.join('&');
        }
        
        window.location.href = url;
    });
    
    // Also trigger filter when pressing Enter in search input
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('applyFilters').click();
        }
    });
    
    // Also trigger filter when click search button
    document.getElementById('searchButton').addEventListener('click', function() {
        document.getElementById('applyFilters').click();
    });
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/pengumuman/admin/index.blade.php ENDPATH**/ ?>