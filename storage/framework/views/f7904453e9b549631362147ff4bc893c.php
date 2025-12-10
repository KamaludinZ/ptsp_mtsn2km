<?php $__env->startSection('title', 'Detail User'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Detail User</h1>
            <p class="text-gray-600">Informasi lengkap user</p>
        </div>
        <div class="flex gap-2">
            <a href="<?php echo e(route('suadmin.users.edit', $user)); ?>" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit User
            </a>
            <a href="<?php echo e(route('suadmin.users.index')); ?>" class="btn btn-ghost">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if(session('success')): ?>
    <div class="alert alert-success shadow-lg mb-4" role="alert">
        <div>
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span><?php echo e(session('success')); ?></span>
        </div>
    </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
    <div class="alert alert-error shadow-lg mb-4" role="alert">
        <div>
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span><?php echo e(session('error')); ?></span>
        </div>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Info Card -->
        <div class="lg:col-span-1">
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body text-center">
                    <div class="avatar placeholder mb-4">
                        <div class="bg-primary text-primary-content rounded-full w-24">
                            <span class="text-3xl"><?php echo e(strtoupper(substr($user->name, 0, 2))); ?></span>
                        </div>
                    </div>
                    <h2 class="card-title justify-center"><?php echo e($user->name); ?></h2>
                    <p class="text-gray-600"><?php echo e($user->email); ?></p>

                    <!-- Status Badge -->
                    <?php if($user->is_active): ?>
                        <div class="badge badge-success gap-2 mt-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-4 h-4 stroke-current" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Aktif
                        </div>
                    <?php else: ?>
                        <div class="badge badge-error gap-2 mt-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-4 h-4 stroke-current" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Nonaktif
                        </div>
                    <?php endif; ?>

                    <!-- Roles -->
                    <div class="divider"></div>
                    <div class="text-left">
                        <h3 class="font-semibold mb-2">Role:</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php $__empty_1 = true; $__currentLoopData = $user->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <span class="badge badge-primary"><?php echo e(ucwords(str_replace('-', ' ', $role->name))); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <span class="text-gray-500 text-sm">Tidak ada role</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="divider"></div>
                    <div class="card-actions flex-col">
                        <?php if($user->id !== auth()->id() && !$user->hasRole('admin')): ?>
                            <form action="<?php echo e(route('admin.users.toggle-status', $user)); ?>" method="POST" class="w-full">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-outline btn-sm w-full">
                                    <?php if($user->is_active): ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                        Nonaktifkan User
                                    <?php else: ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Aktifkan User
                                    <?php endif; ?>
                                </button>
                            </form>

                            <button type="button" onclick="resetPasswordModal.showModal()" class="btn btn-outline btn-warning btn-sm w-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                                Reset Password
                            </button>

                            <button type="button" onclick="deleteUserModal.showModal()" class="btn btn-outline btn-error btn-sm w-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Hapus User
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details -->
        <div class="lg:col-span-2">
            <!-- Basic Information -->
            <div class="card bg-base-100 shadow-xl mb-6">
                <div class="card-body">
                    <h2 class="card-title text-2xl mb-4">Informasi Dasar</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Nama Lengkap</label>
                            <p class="text-lg"><?php echo e($user->name); ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Email</label>
                            <p class="text-lg"><?php echo e($user->email); ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Tipe User</label>
                            <p class="text-lg"><?php echo e(ucwords($user->user_type)); ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Kode Registrasi</label>
                            <p class="text-lg"><?php echo e($user->registration_code ?? '-'); ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Email Terverifikasi</label>
                            <p class="text-lg">
                                <?php if($user->email_verified_at): ?>
                                    <span class="badge badge-success">Ya</span>
                                    <span class="text-sm text-gray-500">(<?php echo e($user->email_verified_at->format('d M Y H:i')); ?>)</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Belum</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Status</label>
                            <p class="text-lg">
                                <?php if($user->is_active): ?>
                                    <span class="badge badge-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-error">Nonaktif</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Terdaftar Sejak</label>
                            <p class="text-lg"><?php echo e($user->created_at->format('d M Y H:i')); ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Terakhir Diupdate</label>
                            <p class="text-lg"><?php echo e($user->updated_at->format('d M Y H:i')); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permissions -->
            <div class="card bg-base-100 shadow-xl mb-6">
                <div class="card-body">
                    <h2 class="card-title text-2xl mb-4">Permissions</h2>
                    <?php if($user->permissions->count() > 0): ?>
                        <div class="flex flex-wrap gap-2">
                            <?php $__currentLoopData = $user->permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="badge badge-outline"><?php echo e($permission->name); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500">Tidak ada permission langsung. Permission diatur melalui role.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Activity Statistics -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title text-2xl mb-4">Statistik Aktivitas</h2>
                    <div class="stats shadow w-full">
                        <div class="stat">
                            <div class="stat-figure text-primary" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            </div>
                            <div class="stat-title">Total Ticket</div>
                            <div class="stat-value text-primary"><?php echo e($user->tickets->count()); ?></div>
                            <div class="stat-desc">Ticket yang dibuat</div>
                        </div>

                        <div class="stat">
                            <div class="stat-figure text-secondary" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div class="stat-title">Ticket Ditugaskan</div>
                            <div class="stat-value text-secondary"><?php echo e($user->assignedTickets->count()); ?></div>
                            <div class="stat-desc">Ticket yang ditugaskan ke user ini</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<dialog id="resetPasswordModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">Reset Password User</h3>
        <form action="<?php echo e(route('suadmin.users.reset-password', $user)); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="form-control w-full mb-4">
                <label class="label" for="new_password">
                    <span class="label-text font-semibold">Password Baru <span class="text-error">*</span></span>
                </label>
                <input type="password" id="new_password" name="new_password" class="input input-bordered w-full" placeholder="Masukkan password baru" required aria-required="true">
            </div>
            <div class="form-control w-full mb-4">
                <label class="label" for="new_password_confirmation">
                    <span class="label-text font-semibold">Konfirmasi Password Baru <span class="text-error">*</span></span>
                </label>
                <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="input input-bordered w-full" placeholder="Konfirmasi password baru" required aria-required="true">
            </div>
            <div class="modal-action">
                <button type="button" class="btn" onclick="resetPasswordModal.close()">Batal</button>
                <button type="submit" class="btn btn-warning">Reset Password</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<!-- Delete User Modal -->
<dialog id="deleteUserModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">Hapus User</h3>
        <p class="mb-4">Apakah Anda yakin ingin menghapus user <strong><?php echo e($user->name); ?></strong>? Tindakan ini tidak dapat dibatalkan.</p>
        <form action="<?php echo e(route('suadmin.users.destroy', $user)); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>
            <div class="modal-action">
                <button type="button" class="btn" onclick="deleteUserModal.close()">Batal</button>
                <button type="submit" class="btn btn-error">Hapus User</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\admin\settings\users\show.blade.php ENDPATH**/ ?>