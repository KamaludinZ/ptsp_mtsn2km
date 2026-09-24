<?php $__env->startSection('title', 'Edit User'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Edit User</h1>
            <p class="text-gray-600">Perbarui informasi user</p>
        </div>
        <a href="<?php echo e(route('admin.users.index')); ?>" class="btn btn-ghost">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
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

    <?php if($errors->any()): ?>
    <div class="alert alert-error shadow-lg mb-4" role="alert">
        <div>
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <div>
                <strong>Terdapat kesalahan:</strong>
                <ul class="list-disc list-inside mt-2">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Edit Form -->
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <form action="<?php echo e(route('admin.users.update', $user)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div class="form-control w-full">
                        <label class="label" for="name">
                            <span class="label-text font-semibold">Nama Lengkap <span class="text-error">*</span></span>
                        </label>
                        <input type="text" id="name" name="name" value="<?php echo e(old('name', $user->name)); ?>" class="input input-bordered w-full <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Masukkan nama lengkap" required aria-required="true" aria-describedby="name-error">
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <label class="label">
                                <span class="label-text-alt text-error" id="name-error"><?php echo e($message); ?></span>
                            </label>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Email -->
                    <div class="form-control w-full">
                        <label class="label" for="email">
                            <span class="label-text font-semibold">Email <span class="text-error">*</span></span>
                        </label>
                        <input type="email" id="email" name="email" value="<?php echo e(old('email', $user->email)); ?>" class="input input-bordered w-full <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Masukkan email" required aria-required="true" aria-describedby="email-error">
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <label class="label">
                                <span class="label-text-alt text-error" id="email-error"><?php echo e($message); ?></span>
                            </label>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- User Type -->
                    <div class="form-control w-full">
                        <label class="label" for="user_type">
                            <span class="label-text font-semibold">Tipe User <span class="text-error">*</span></span>
                        </label>
                        <select id="user_type" name="user_type" class="select select-bordered w-full <?php $__errorArgs = ['user_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> select-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required aria-required="true" aria-describedby="user_type-error">
                            <option value="">Pilih tipe user</option>
                            <?php $__currentLoopData = $userTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>" <?php echo e(old('user_type', $user->user_type) === $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['user_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <label class="label">
                                <span class="label-text-alt text-error" id="user_type-error"><?php echo e($message); ?></span>
                            </label>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Role -->
                    <div class="form-control w-full">
                        <label class="label" for="role">
                            <span class="label-text font-semibold">Role <span class="text-error">*</span></span>
                        </label>
                        <select id="role" name="role" class="select select-bordered w-full <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> select-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required aria-required="true" aria-describedby="role-error">
                            <option value="">Pilih role</option>
                            <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($role->name); ?>" <?php echo e(in_array($role->name, $userRoles) ? 'selected' : ''); ?>><?php echo e(ucwords(str_replace('-', ' ', $role->name))); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <label class="label">
                                <span class="label-text-alt text-error" id="role-error"><?php echo e($message); ?></span>
                            </label>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Registration Code -->
                    <div class="form-control w-full">
                        <label class="label" for="registration_code">
                            <span class="label-text font-semibold">Kode Registrasi</span>
                        </label>
                        <input type="text" id="registration_code" name="registration_code" value="<?php echo e(old('registration_code', $user->registration_code)); ?>" class="input input-bordered w-full" placeholder="Masukkan kode registrasi (opsional)" aria-describedby="registration_code-help">
                        <label class="label">
                            <span class="label-text-alt text-gray-500" id="registration_code-help">Kode registrasi untuk validasi (opsional)</span>
                        </label>
                    </div>

                    <!-- Status -->
                    <div class="form-control w-full">
                        <label class="label cursor-pointer justify-start gap-4">
                            <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-primary" <?php echo e(old('is_active', $user->is_active) ? 'checked' : ''); ?> aria-describedby="is_active-help">
                            <span class="label-text font-semibold">User Aktif</span>
                        </label>
                        <label class="label">
                            <span class="label-text-alt text-gray-500" id="is_active-help">Centang untuk mengaktifkan user</span>
                        </label>
                    </div>
                </div>

                <!-- Password Section -->
                <div class="divider"></div>
                <h3 class="text-xl font-semibold mb-4">Ubah Password (Opsional)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Password -->
                    <div class="form-control w-full">
                        <label class="label" for="password">
                            <span class="label-text font-semibold">Password Baru</span>
                        </label>
                        <input type="password" id="password" name="password" class="input input-bordered w-full <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Masukkan password baru" aria-describedby="password-help password-error">
                        <label class="label">
                            <span class="label-text-alt text-gray-500" id="password-help">Kosongkan jika tidak ingin mengubah password</span>
                        </label>
                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <label class="label">
                                <span class="label-text-alt text-error" id="password-error"><?php echo e($message); ?></span>
                            </label>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-control w-full">
                        <label class="label" for="password_confirmation">
                            <span class="label-text font-semibold">Konfirmasi Password Baru</span>
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="input input-bordered w-full" placeholder="Konfirmasi password baru" aria-describedby="password_confirmation-help">
                        <label class="label">
                            <span class="label-text-alt text-gray-500" id="password_confirmation-help">Masukkan ulang password baru</span>
                        </label>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card-actions justify-end mt-6">
                    <a href="<?php echo e(route('admin.users.index')); ?>" class="btn btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/admin/settings/users/edit.blade.php ENDPATH**/ ?>