<div class="mb-4">
    <label for="title" class="block text-sm font-medium text-gray-700">Judul</label>
    <input type="text" name="title" id="title" required 
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
           placeholder="Masukkan judul singkat" 
           value="<?php echo e(old('title')); ?>">
    <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>

<div class="mb-4">
    <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
    <textarea name="description" id="description" required 
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
              rows="5" placeholder="Berikan deskripsi detail..."><?php echo e(old('description')); ?></textarea>
    <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>

<div class="mb-4">
    <label class="flex items-center">
        <input type="checkbox" name="anonymous" id="anonymous-<?php echo e($is_whistleblowing ?? false ? 'whistleblowing' : 'dumas'); ?>" value="1" 
               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 anonymous-checkbox"
               <?php echo e(old('anonymous') ? 'checked' : ''); ?>>
        <span class="ml-2 text-sm text-gray-700">Kirim secara anonim</span>
    </label>
</div>

<div id="identity-section-<?php echo e($is_whistleblowing ?? false ? 'whistleblowing' : 'dumas'); ?>" class="<?php echo e(old('anonymous') ? 'hidden' : ''); ?>">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <label for="complainant_name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
            <input type="text" name="complainant_name" id="complainant_name" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                   placeholder="Nama lengkap Anda" 
                   value="<?php echo e(old('complainant_name') ?? (Auth::user() ? Auth::user()->name : '')); ?>">
            <?php $__errorArgs = ['complainant_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        
        <div>
            <label for="complainant_email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="complainant_email" id="complainant_email" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                   placeholder="Alamat email Anda" 
                   value="<?php echo e(old('complainant_email') ?? (Auth::user() ? Auth::user()->email : '')); ?>">
            <?php $__errorArgs = ['complainant_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
    </div>
    
    <div class="mb-4">
        <label for="complainant_contact" class="block text-sm font-medium text-gray-700">Nomor Kontak (Opsional)</label>
        <input type="text" name="complainant_contact" id="complainant_contact" 
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
               placeholder="Nomor telepon atau kontak lainnya" 
               value="<?php echo e(old('complainant_contact')); ?>">
        <?php $__errorArgs = ['complainant_contact'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
</div><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\supervision\partials\complaint-form-fields.blade.php ENDPATH**/ ?>