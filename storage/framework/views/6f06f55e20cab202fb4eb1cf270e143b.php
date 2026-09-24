<?php $__env->startSection('title', 'Edit Role'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-2">
            <a href="<?php echo e(route('admin.roles.index')); ?>" class="btn btn-ghost btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Edit Role: <?php echo e(ucwords(str_replace(['-', '_'], ' ', $role->name))); ?></h1>
        </div>
        <p class="text-gray-600">Ubah informasi role dan permissions</p>
    </div>

    <!-- Alert Messages -->
    <?php if($errors->any()): ?>
    <div class="alert alert-error shadow-lg mb-4">
        <div>
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <div>
                <h3 class="font-bold">Terdapat kesalahan!</h3>
                <ul class="list-disc list-inside">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <form action="<?php echo e(route('admin.roles.update', $role)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Role Info -->
            <div class="lg:col-span-1">
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h2 class="card-title">Informasi Role</h2>

                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-semibold">Nama Role <span class="text-error">*</span></span>
                            </label>
                            <input type="text" name="name" value="<?php echo e(old('name', $role->name)); ?>" class="input input-bordered w-full <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="contoh: customer-service" required <?php echo e($role->name === 'admin' ? 'readonly' : ''); ?>>
                            <label class="label">
                                <span class="label-text-alt text-gray-500">Gunakan huruf kecil dan tanda hubung (-)</span>
                            </label>
                            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <label class="label">
                                <span class="label-text-alt text-error"><?php echo e($message); ?></span>
                            </label>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <?php if($role->name === 'admin'): ?>
                        <div class="alert alert-warning">
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 w-6 h-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            <span class="text-sm">Role Admin tidak dapat diubah namanya</span>
                        </div>
                        <?php endif; ?>

                        <!-- Role Stats -->
                        <div class="stats stats-vertical shadow mt-4">
                            <div class="stat">
                                <div class="stat-title">Permissions</div>
                                <div class="stat-value text-primary text-2xl"><?php echo e($role->permissions->count()); ?></div>
                                <div class="stat-desc">dari <?php echo e($permissions->flatten()->count()); ?> total</div>
                            </div>
                            <div class="stat">
                                <div class="stat-title">Users</div>
                                <div class="stat-value text-secondary text-2xl"><?php echo e($role->users->count()); ?></div>
                                <div class="stat-desc">menggunakan role ini</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permissions -->
            <div class="lg:col-span-2">
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="card-title">Permissions</h2>
                            <div class="flex gap-2">
                                <button type="button" onclick="selectAllPermissions()" class="btn btn-sm btn-outline">
                                    Pilih Semua
                                </button>
                                <button type="button" onclick="deselectAllPermissions()" class="btn btn-sm btn-outline">
                                    Hapus Semua
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module => $modulePermissions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="card bg-base-200">
                                <div class="card-body p-4">
                                    <h3 class="font-bold text-lg capitalize mb-3 flex items-center justify-between">
                                        <span><?php echo e(str_replace('_', ' ', $module)); ?></span>
                                        <div class="form-control">
                                            <label class="label cursor-pointer gap-2">
                                                <span class="label-text text-xs">Semua</span>
                                                <input type="checkbox" class="checkbox checkbox-sm module-checkbox" data-module="<?php echo e($module); ?>" onchange="toggleModule(this, '<?php echo e($module); ?>')">
                                            </label>
                                        </div>
                                    </h3>
                                    <div class="space-y-2">
                                        <?php $__currentLoopData = $modulePermissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="form-control">
                                            <label class="label cursor-pointer justify-start gap-3">
                                                <input type="checkbox" name="permissions[]" value="<?php echo e($permission->name); ?>" class="checkbox checkbox-primary checkbox-sm permission-checkbox module-<?php echo e($module); ?>" <?php echo e(in_array($permission->name, $rolePermissions) ? 'checked' : ''); ?>>
                                                <span class="label-text"><?php echo e($permission->name); ?></span>
                                            </label>
                                        </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card-actions justify-end px-6 pb-6">
                        <a href="<?php echo e(route('admin.roles.index')); ?>" class="btn btn-ghost">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Update Role
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    function selectAllPermissions() {
        document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
            checkbox.checked = true;
        });
        document.querySelectorAll('.module-checkbox').forEach(checkbox => {
            checkbox.checked = true;
        });
    }

    function deselectAllPermissions() {
        document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        document.querySelectorAll('.module-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
    }

    function toggleModule(checkbox, module) {
        const moduleCheckboxes = document.querySelectorAll(`.module-${module}`);
        moduleCheckboxes.forEach(cb => {
            cb.checked = checkbox.checked;
        });
    }

    // Update module checkbox when individual permissions change & on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Update module checkboxes based on current selections
        document.querySelectorAll('.module-checkbox').forEach(moduleCheckbox => {
            const module = moduleCheckbox.dataset.module;
            const moduleCheckboxes = document.querySelectorAll(`.module-${module}`);
            const allChecked = Array.from(moduleCheckboxes).every(cb => cb.checked);
            moduleCheckbox.checked = allChecked;
        });

        // Add change listener
        document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const moduleClass = Array.from(this.classList).find(c => c.startsWith('module-'));
                if (moduleClass) {
                    const module = moduleClass.replace('module-', '');
                    const moduleCheckboxes = document.querySelectorAll(`.${moduleClass}`);
                    const moduleCheckbox = document.querySelector(`[data-module="${module}"]`);

                    if (moduleCheckbox) {
                        const allChecked = Array.from(moduleCheckboxes).every(cb => cb.checked);
                        moduleCheckbox.checked = allChecked;
                    }
                }
            });
        });
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/admin/settings/roles/edit.blade.php ENDPATH**/ ?>