<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <?php echo e(__('Dashboard')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-linear-to-br from-blue-50 to-blue-100 dark:from-gray-700 dark:to-gray-800 rounded-xl p-6 shadow">
                            <div class="flex items-center">
                                <div class="p-3 rounded-lg bg-blue-100 dark:bg-blue-900">
                                    <i class="fas fa-ticket-alt text-blue-600 dark:text-blue-400 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Tiket Aktif</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">0</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-linear-to-br from-green-50 to-green-100 dark:from-gray-700 dark:to-gray-800 rounded-xl p-6 shadow">
                            <div class="flex items-center">
                                <div class="p-3 rounded-lg bg-green-100 dark:bg-green-900">
                                    <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Selesai Diproses</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">0</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-linear-to-br from-purple-50 to-purple-100 dark:from-gray-700 dark:to-gray-800 rounded-xl p-6 shadow">
                            <div class="flex items-center">
                                <div class="p-3 rounded-lg bg-purple-100 dark:bg-purple-900">
                                    <i class="fas fa-history text-purple-600 dark:text-purple-400 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Riwayat Permohonan</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">0</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Welcome Message -->
                        <div class="bg-linear-to-br from-indigo-50 to-purple-50 dark:from-gray-700 dark:to-gray-800 rounded-xl p-6 shadow">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                                Selamat Datang, <?php echo e(Auth::user()->name); ?>!
                            </h3>
                            <p class="text-gray-700 dark:text-gray-300 mb-4">
                                Anda masuk sebagai <span class="font-semibold"><?php echo e(ucfirst(Auth::user()->user_type)); ?></span>. 
                                Gunakan dashboard ini untuk mengelola permohonan layanan Anda.
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    <i class="fas fa-user mr-1"></i> <?php echo e(Auth::user()->user_type); ?>

                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    <i class="fas fa-calendar mr-1"></i> <?php echo e(Auth::user()->created_at->format('d M Y')); ?>

                                </span>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="bg-white dark:bg-gray-700 rounded-xl p-6 shadow">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                                Aksi Cepat
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <a href="<?php echo e(route('onlineportal.service.catalog')); ?>" 
                                   class="flex flex-col items-center justify-center p-4 bg-blue-50 dark:bg-blue-900 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-800 transition-colors">
                                    <i class="fas fa-file-medical text-blue-600 dark:text-blue-400 text-2xl mb-2"></i>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white text-center">Ajukan Layanan</span>
                                </a>
                                
                                <a href="<?php echo e(route('onlineportal.my.services')); ?>" 
                                   class="flex flex-col items-center justify-center p-4 bg-green-50 dark:bg-green-900 rounded-lg hover:bg-green-100 dark:hover:bg-green-800 transition-colors">
                                    <i class="fas fa-search text-green-600 dark:text-green-400 text-2xl mb-2"></i>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white text-center">Lacak Permohonan</span>
                                </a>
                                
                                <a href="<?php echo e(route('supervision.complaints.dashboard')); ?>" 
                                   class="flex flex-col items-center justify-center p-4 bg-yellow-50 dark:bg-yellow-900 rounded-lg hover:bg-yellow-100 dark:hover:bg-yellow-800 transition-colors">
                                    <i class="fas fa-exclamation-circle text-yellow-600 dark:text-yellow-400 text-2xl mb-2"></i>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white text-center">Pengaduan</span>
                                </a>
                                
                                <a href="<?php echo e(route('supervision.surveys.dashboard')); ?>" 
                                   class="flex flex-col items-center justify-center p-4 bg-purple-50 dark:bg-purple-900 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-800 transition-colors">
                                    <i class="fas fa-poll text-purple-600 dark:text-purple-400 text-2xl mb-2"></i>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white text-center">Survei Kepuasan</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Services Overview -->
                    <div class="mt-8 bg-white dark:bg-gray-700 rounded-xl p-6 shadow">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                            Layanan yang Tersedia untuk Anda
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php $__currentLoopData = App\Models\Service::whereJsonContains('user_types_allowed', Auth::user()->user_type)->limit(6)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                    <h4 class="font-semibold text-gray-900 dark:text-white"><?php echo e($service->name); ?></h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1 line-clamp-2"><?php echo e(Str::limit($service->description, 60)); ?></p>
                                    <div class="mt-3 flex justify-between items-center">
                                        <span class="text-xs px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded">
                                            <?php echo e($service->code); ?>

                                        </span>
                                        <a href="<?php echo e(route('onlineportal.service.details', $service->id)); ?>" 
                                           class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                            Lihat Detail
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <div class="mt-4 text-center">
                            <a href="<?php echo e(route('onlineportal.service.catalog')); ?>" 
                               class="inline-flex items-center text-blue-600 dark:text-blue-400 hover:underline">
                                Lihat Semua Layanan
                                <i class="fas fa-arrow-right ml-1 text-xs"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Performance Metrics -->
                    <div class="mt-8 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-gray-700 dark:to-gray-800 rounded-xl p-6 shadow">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                            Kinerja Pelayanan Bulan Ini
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="text-center p-4 bg-white dark:bg-gray-700 rounded-lg shadow">
                                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">98%</p>
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Tingkat Kepuasan</p>
                            </div>
                            <div class="text-center p-4 bg-white dark:bg-gray-700 rounded-lg shadow">
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">24 Jam</p>
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Waktu Respon</p>
                            </div>
                            <div class="text-center p-4 bg-white dark:bg-gray-700 rounded-lg shadow">
                                <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">95%</p>
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Tepat Waktu</p>
                            </div>
                            <div class="text-center p-4 bg-white dark:bg-gray-700 rounded-lg shadow">
                                <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">15+</p>
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Jenis Layanan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\dashboard.blade.php ENDPATH**/ ?>