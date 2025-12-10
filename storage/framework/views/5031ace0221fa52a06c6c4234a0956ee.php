<?php $__env->startSection('title', 'Manajemen Kategori Layanan'); ?>

<?php $__env->startSection('content'); ?>
<div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="flex justify-between items-center mb-4">
                <a href="<?php echo e(route('admin.service-categories.create')); ?>" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>Tambah Kategori
                </a>
                
                <form method="GET" class="flex gap-2">
                    <div class="form-control">
                        <div class="input-group">
                            <input type="text" 
                                   name="search" 
                                   class="input input-bordered" 
                                   placeholder="Cari kategori..."
                                   value="<?php echo e(request('search')); ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    
                    <select name="status" class="select select-bordered">
                        <option value="">Semua Status</option>
                        <option value="active" <?php echo e(request('status') == 'active' ? 'selected' : ''); ?>>Aktif</option>
                        <option value="inactive" <?php echo e(request('status') == 'inactive' ? 'selected' : ''); ?>>Non-Aktif</option>
                    </select>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i>
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Deskripsi</th>
                            <th>Parent</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($loop->iteration + ($categories->currentPage() - 1) * $categories->perPage()); ?></td>
                            <td><?php echo e($category->name); ?></td>
                            <td><?php echo e(Str::limit($category->description, 50)); ?></td>
                            <td><?php echo e($category->parent->name ?? '-'); ?></td>
                            <td>
                                <span class="badge 
                                    <?php if($category->is_active): ?> badge-success 
                                    <?php else: ?> badge-secondary <?php endif; ?>">
                                    <?php echo e($category->is_active ? 'Aktif' : 'Non-Aktif'); ?>

                                </span>
                            </td>
                            <td><?php echo e($category->created_at->format('d/m/Y')); ?></td>
                            <td>
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="btn btn-ghost btn-xs">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </label>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-32">
                                        <li>
                                            <a href="<?php echo e(route('admin.service-categories.show', $category)); ?>">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo e(route('admin.service-categories.edit', $category)); ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        </li>
                                        <li>
                                            <form action="<?php echo e(route('admin.service-categories.destroy', $category)); ?>" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="text-error">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center py-16">
                                <i class="fas fa-inbox fa-5x text-base-content/20 mb-3"></i>
                                <p class="text-lg font-bold text-base-content/70">Tidak ada kategori ditemukan</p>
                                <p class="text-base-content/50">Silakan tambahkan kategori baru atau sesuaikan filter pencarian Anda</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-center mt-4">
                <?php echo e($categories->links('vendor.pagination.daisyui')); ?>

            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\admin\service-categories\index.blade.php ENDPATH**/ ?>