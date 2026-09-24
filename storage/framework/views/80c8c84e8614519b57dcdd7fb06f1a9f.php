<?php $__env->startSection('content'); ?>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-6">Registrasi Layanan Walk-in</h1>

                    <?php if($errors->any()): ?>
                        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                            <ul class="list-disc list-inside text-sm">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo e(route('frontdesk.service.submit')); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>

                        <div class="mb-4">
                            <label for="service_id" class="block text-sm font-medium text-gray-700">Layanan</label>
                            <select name="service_id" id="service_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">-- Pilih Layanan --</option>
                                <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($service->id); ?>" <?php echo e(old('service_id') == $service->id ? 'selected' : ''); ?>>
                                        <?php echo e($service->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="applicant_name" class="block text-sm font-medium text-gray-700">Nama Pemohon</label>
                            <input type="text" name="applicant_name" id="applicant_name" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="<?php echo e(old('applicant_name')); ?>">
                        </div>

                        <div class="mb-4">
                            <label for="applicant_email" class="block text-sm font-medium text-gray-700">Email (opsional)</label>
                            <input type="email" name="applicant_email" id="applicant_email"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="<?php echo e(old('applicant_email')); ?>">
                        </div>

                        <div class="mb-4">
                            <label for="applicant_phone" class="block text-sm font-medium text-gray-700">Nomor Telepon/WhatsApp</label>
                            <input type="text" name="applicant_phone" id="applicant_phone" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="<?php echo e(old('applicant_phone')); ?>">
                        </div>

                        <div class="mb-4">
                            <label for="applicant_type" class="block text-sm font-medium text-gray-700">Tipe Pemohon</label>
                            <select name="applicant_type" id="applicant_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <?php $__currentLoopData = ['guru' => 'Guru', 'pegawai' => 'Pegawai', 'siswa' => 'Siswa', 'walimurid' => 'Wali Murid', 'alumni' => 'Alumni', 'instansi' => 'Instansi', 'umum' => 'Umum']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>" <?php echo e(old('applicant_type') == $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Keterangan Pengajuan</label>
                            <textarea name="description" id="description" rows="4" required
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><?php echo e(old('description')); ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="urgency_level" class="block text-sm font-medium text-gray-700">Tingkat Urgensi</label>
                            <select name="urgency_level" id="urgency_level" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="normal">Normal</option>
                                <option value="high">Tinggi</option>
                                <option value="urgent">Mendesak</option>
                            </select>
                        </div>

                        <div class="mb-6">
                            <label for="files" class="block text-sm font-medium text-gray-700">Berkas Pendukung (opsional)</label>
                            <input type="file" name="files[]" id="files" multiple
                                   class="mt-1 block w-full text-sm">
                        </div>

                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                            Ajukan Layanan
                        </button>
                        <a href="<?php echo e(route('frontdesk.dashboard')); ?>" class="ml-2 text-gray-600 hover:underline">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontdesk.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/puskomdev/ptsp_mtsn2km/resources/views/frontdesk/service-application.blade.php ENDPATH**/ ?>