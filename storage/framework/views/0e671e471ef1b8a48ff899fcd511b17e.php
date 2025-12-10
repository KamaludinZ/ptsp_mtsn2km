<?php $__env->startSection('title', 'Whistleblowing Berhasil - ' . config('app.name', 'PTSP MTsN 2 Kota Malang')); ?>

<?php $__env->startSection('content'); ?>
<!-- Contact Header -->
<div class="bg-blue-600 text-white py-3">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center text-sm">
            <div class="space-y-1">
                <div class="font-medium">Jl. Raya Cemorokandang 77 Kota Malang, Jawa Timur</div>
                <div class="flex flex-wrap justify-center gap-x-6 gap-y-1">
                    <span>(0341) 711500</span>
                    <span>mtsnmalang2adm@gmail.com</span>
                    <span>www.mtsn2kotamalang.sch.id</span>
                </div>
                <div>
                    <span>0851 8336 7500 (PTSP)</span> | <span>0851 8337 5008 (Pengaduan)</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Message Section -->
<section class="bg-gradient-to-br from-green-50 to-indigo-50 dark:from-gray-800 dark:to-gray-900 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 text-center">
            <div class="mx-auto bg-green-100 dark:bg-green-900 w-20 h-20 rounded-full flex items-center justify-center mb-6">
                <i class="fas fa-shield-alt text-green-600 dark:text-green-400 text-4xl"></i>
            </div>
            
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Laporan Whistleblowing Berhasil Dikirim</h1>
            <p class="text-lg text-gray-600 dark:text-gray-300 mb-8 max-w-2xl mx-auto">
                Terima kasih atas laporan yang telah Anda sampaikan. Kami akan menjaga kerahasiaan identitas Anda dan menindaklanjuti laporan ini secara profesional.
            </p>
            
            <div class="bg-blue-50 dark:bg-blue-900 rounded-lg p-6 mb-8 max-w-md mx-auto">
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">Nomor Tiket Laporan</p>
                <p class="text-2xl font-bold text-blue-800 dark:text-blue-200">#<?php echo e($complaint->complaint_number); ?></p>
            </div>
            
            <p class="text-gray-600 dark:text-gray-300 mb-8 max-w-2xl mx-auto">
                Laporan whistleblowing Anda akan segera kami tindaklanjuti. Kami menjamin kerahasiaan informasi dan perlindungan terhadap pelapor.
            </p>
            
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="<?php echo e(route('home')); ?>" 
                   class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-home mr-2"></i> Kembali ke Beranda
                </a>
                <a href="<?php echo e(route('supervision.complaint.track.form')); ?>" 
                   class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-search mr-2"></i> Lacak Laporan
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Information Section -->
<div class="py-12 bg-white dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Perlindungan Whistleblower</h2>
            <p class="text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                Kami menjamin kerahasiaan identitas pelapor dan melindungi dari segala bentuk represaliasi
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-gray-800 dark:to-gray-800 rounded-xl p-6 text-center shadow">
                <div class="mx-auto bg-blue-100 dark:bg-blue-900 w-16 h-16 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-user-secret text-blue-600 dark:text-blue-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Kerahasiaan</h3>
                <p class="text-gray-600 dark:text-gray-300">Identitas pelapor dirahasiakan</p>
            </div>
            
            <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-gray-800 dark:to-gray-800 rounded-xl p-6 text-center shadow">
                <div class="mx-auto bg-green-100 dark:bg-green-900 w-16 h-16 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-shield-alt text-green-600 dark:text-green-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Perlindungan</h3>
                <p class="text-gray-600 dark:text-gray-300">Dilindungi dari represaliasi</p>
            </div>
            
            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-gray-800 dark:to-gray-800 rounded-xl p-6 text-center shadow">
                <div class="mx-auto bg-yellow-100 dark:bg-yellow-900 w-16 h-16 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-clock text-yellow-600 dark:text-yellow-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Waktu Penyelesaian</h3>
                <p class="text-gray-600 dark:text-gray-300">14-30 hari kerja</p>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\supervision\whistleblowing-success.blade.php ENDPATH**/ ?>