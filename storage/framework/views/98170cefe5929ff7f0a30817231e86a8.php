<?php $__env->startSection('title', 'Manajemen User'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid p-4">
    <!-- Page Header -->
    <div class="flex flex-wrap justify-between items-center mb-4">
        <div>
            <h4 class="text-2xl font-bold text-base-content">Manajemen User</h4>
            <div class="text-sm breadcrumbs">
                <ul>
                    <li><a href="<?php echo e(route('admin.dashboard')); ?>">Dashboard</a></li>
                    <li><a href="<?php echo e(route('admin.settings.index')); ?>">Pengaturan</a></li>
                    <li>Manajemen User</li>
                </ul>
            </div>
        </div>
        <div class="mt-3 md:mt-0">
            <a href="<?php echo e(route('admin.users.create')); ?>" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus mr-2"></i>Tambah User Baru
            </a>
        </div>
    </div>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h4 class="card-title mb-4">Daftar User</h4>
            
            <!-- Filter Form -->
            <form method="GET" action="<?php echo e(route('admin.users.index')); ?>" class="mb-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label for="role" class="label">
                            <span class="label-text">Role</span>
                        </label>
                        <select name="role" id="role" class="select select-bordered w-full">
                            <option value="">Semua Role</option>
                            <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($role->name); ?>" <?php echo e(request('role') == $role->name ? 'selected' : ''); ?>><?php echo e($role->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label for="user_type" class="label">
                            <span class="label-text">Tipe User</span>
                        </label>
                        <select name="user_type" id="user_type" class="select select-bordered w-full">
                            <option value="">Semua Tipe</option>
                            <?php $__currentLoopData = $userTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>" <?php echo e(request('user_type') == $key ? 'selected' : ''); ?>><?php echo e($value); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label for="is_active" class="label">
                            <span class="label-text">Status</span>
                        </label>
                        <select name="is_active" id="is_active" class="select select-bordered w-full">
                            <option value="">Semua Status</option>
                            <option value="1" <?php echo e(request('is_active') == '1' ? 'selected' : ''); ?>>Aktif</option>
                            <option value="0" <?php echo e(request('is_active') == '0' ? 'selected' : ''); ?>>Non-Aktif</option>
                        </select>
                    </div>
                    <div>
                        <label for="search" class="label">
                            <span class="label-text">Cari</span>
                        </label>
                        <div class="input-group">
                            <input type="text" name="search" id="search" class="input input-bordered w-full" placeholder="Cari nama/email..." value="<?php echo e(request('search')); ?>">
                            <button class="btn btn-primary" type="submit">Cari</button>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end mt-4">
                    <button class="btn btn-primary" type="submit">Terapkan Filter</button>
                </div>
            </form>

            <!-- Users Table -->
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Tipe User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($loop->iteration); ?></td>
                                <td><?php echo e($user->name); ?></td>
                                <td><?php echo e($user->email); ?></td>
                                <td><span class="badge badge-info"><?php echo e($userTypes[$user->user_type] ?? $user->user_type); ?></span></td>
                                <td>
                                    <?php $__currentLoopData = $user->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="badge badge-primary"><?php echo e($role->name); ?></span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </td>
                                <td>
                                    <?php if($user->is_active): ?>
                                        <span class="badge badge-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge badge-error">Non-Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($user->created_at->format('d M Y')); ?></td>
                                <td class="text-right">
                                    <div class="dropdown dropdown-end">
                                        <label tabindex="0" class="btn btn-ghost btn-xs">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </label>
                                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-32">
                                            <li>
                                                <a href="<?php echo e(route('admin.users.show', $user)); ?>">
                                                    <i class="fas fa-eye"></i> Lihat
                                                </a>
                                            </li>
                                            <li>
                                                <a href="<?php echo e(route('admin.users.edit', $user)); ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <form action="<?php echo e(route('admin.users.destroy', $user)); ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
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
                                <td colspan="8" class="text-center py-16">
                                    <i class="fas fa-users fa-5x text-base-content/20 mb-3"></i>
                                    <h5 class="text-lg font-bold text-base-content/70">Tidak ada user ditemukan.</h5>
                                    <p class="text-base-content/50">Silakan tambahkan user baru atau sesuaikan filter pencarian Anda</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-center mt-4">
                <?php echo e($users->withQueryString()->links('vendor.pagination.daisyui')); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/admin/settings/users/index.blade.php ENDPATH**/ ?>