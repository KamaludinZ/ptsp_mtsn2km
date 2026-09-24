<?php $__env->startSection('title', 'Kategori Layanan'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Manajemen Kategori Layanan</h4>
                    <a href="<?php echo e(route('admin.service-categories.create')); ?>" class="btn btn-light">
                        <i class="fas fa-plus"></i> Tambah Kategori
                    </a>
                </div>
                <div class="card-body">
                    <!-- Filter -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <label class="form-label">Cari Kategori</label>
                            <input type="text" class="form-control" id="searchInput" placeholder="Cari nama kategori..." value="<?php echo e(request('search')); ?>">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button class="btn btn-primary w-100" id="applyFilters">Terapkan Filter</button>
                        </div>
                    </div>

                    <!-- Categories Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nama</th>
                                    <th>Slug</th>
                                    <th>Deskripsi</th>
                                    <th>Parent</th>
                                    <th>Urutan</th>
                                    <th>Aktif</th>
                                    <th>Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <?php if($category->parent): ?>
                                                <span class="ms-<?php echo e($category->depth * 2); ?>"></span>— 
                                            <?php endif; ?>
                                            <?php echo e($category->name); ?>

                                        </td>
                                        <td><?php echo e($category->slug); ?></td>
                                        <td><?php echo e(Str::limit($category->description, 50)); ?></td>
                                        <td><?php echo e($category->parent->name ?? 'N/A'); ?></td>
                                        <td><?php echo e($category->sort_order); ?></td>
                                        <td>
                                            <?php if($category->is_active): ?>
                                                <span class="badge bg-success">Ya</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Tidak</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($category->created_at->format('d M Y')); ?></td>
                                        <td>
                                            <a href="<?php echo e(route('admin.service-categories.show', $category)); ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo e(route('admin.service-categories.edit', $category)); ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="<?php echo e(route('admin.service-categories.destroy', $category)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Tidak ada kategori ditemukan</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        <?php echo e($categories->links()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('applyFilters').addEventListener('click', function() {
        const search = document.getElementById('searchInput').value;
        
        let url = '<?php echo e(route('admin.service-categories.index')); ?>';
        const params = [];
        
        if (search) params.push('search=' + encodeURIComponent(search));
        
        if (params.length > 0) {
            url += '?' + params.join('&');
        }
        
        window.location.href = url;
    });
    
    // Trigger filter when pressing Enter in search input
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('applyFilters').click();
        }
    });
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/admin/service_categories/index.blade.php ENDPATH**/ ?>