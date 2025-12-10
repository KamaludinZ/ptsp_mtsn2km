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
            <?php echo e(__('Detail Layanan')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Breadcrumb -->
                    <nav class="flex mb-6" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="<?php echo e(route('onlineportal.service.catalog')); ?>" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                                    <i class="fas fa-home mr-2"></i>
                                    Katalog Layanan
                                </a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <i class="fas fa-chevron-right text-gray-400 mx-2 text-sm"></i>
                                    <span class="ml-1 text-sm font-medium text-gray-500 dark:text-gray-400">Detail Layanan</span>
                                </div>
                            </li>
                        </ol>
                    </nav>

                    <!-- Service Header -->
                    <div class="mb-8">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
                            <div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    LAY-001
                                </span>
                                <h1 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                    Surat Keterangan Siswa Aktif
                                </h1>
                                <p class="mt-3 text-lg text-gray-600 dark:text-gray-300 max-w-3xl">
                                    Surat keterangan resmi untuk siswa aktif di MTsN 2 Kota Malang yang dapat digunakan untuk berbagai keperluan seperti beasiswa, pindah sekolah, atau keperluan administrasi lainnya.
                                </p>
                            </div>
                            <div class="shrink-0">
                                <div class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 text-sm font-semibold px-3 py-1 rounded-full">
                                    <i class="fas fa-check-circle mr-1"></i> Aktif
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-wrap gap-4">
                            <div class="flex items-center text-gray-600 dark:text-gray-400">
                                <i class="fas fa-user mr-2"></i>
                                <span>Pemohon: Siswa</span>
                            </div>
                            <div class="flex items-center text-gray-600 dark:text-gray-400">
                                <i class="fas fa-clock mr-2"></i>
                                <span>Waktu Pengerjaan: 1 Hari Kerja</span>
                            </div>
                            <div class="flex items-center text-gray-600 dark:text-gray-400">
                                <i class="fas fa-coins mr-2"></i>
                                <span>Biaya: Gratis</span>
                            </div>
                        </div>
                    </div>

                    <!-- 14 Components of Service Standard (Permen PANRB 15/2014) -->
                    <div class="mb-10">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                            Standar Pelayanan Sesuai Permen PANRB 15/2014
                        </h2>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Component 1: Dasar Hukum -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-5 border border-gray-200 dark:border-gray-600">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900 rounded-lg p-3">
                                        <i class="fas fa-gavel text-blue-600 dark:text-blue-400"></i>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">1. Dasar Hukum</h3>
                                        <ul class="mt-2 space-y-1 text-gray-600 dark:text-gray-300 text-sm">
                                            <li class="flex items-start">
                                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2 text-xs"></i>
                                                UU No. 20 Tahun 2003 tentang Sistem Pendidikan Nasional
                                            </li>
                                            <li class="flex items-start">
                                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2 text-xs"></i>
                                                Permendikbud No. 13 Tahun 2020 tentang Organisasi dan Tata Kerja MTsN
                                            </li>
                                            <li class="flex items-start">
                                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2 text-xs"></i>
                                                Permen PANRB No. 15 Tahun 2014 tentang Pedoman Pelayanan Publik
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Component 2: Persyaratan -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-5 border border-gray-200 dark:border-gray-600">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 bg-green-100 dark:bg-green-900 rounded-lg p-3">
                                        <i class="fas fa-list-alt text-green-600 dark:text-green-400"></i>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">2. Persyaratan</h3>
                                        <ul class="mt-2 space-y-2 text-gray-600 dark:text-gray-300 text-sm">
                                            <li class="flex items-start">
                                                <i class="fas fa-dot-circle text-blue-500 mt-1.5 mr-2 text-xs"></i>
                                                Fotocopy Kartu Keluarga (KK) - 1 lembar
                                            </li>
                                            <li class="flex items-start">
                                                <i class="fas fa-dot-circle text-blue-500 mt-1.5 mr-2 text-xs"></i>
                                                Fotocopy KTP Orang Tua/Wali - 1 lembar
                                            </li>
                                            <li class="flex items-start">
                                                <i class="fas fa-dot-circle text-blue-500 mt-1.5 mr-2 text-xs"></i>
                                                Surat Permohonan dari Orang Tua/Wali (format tersedia)
                                            </li>
                                            <li class="flex items-start">
                                                <i class="fas fa-dot-circle text-blue-500 mt-1.5 mr-2 text-xs"></i>
                                                Pas Foto 3x4 terbaru - 2 lembar
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Component 3: Sistem, Mekanisme & Prosedur -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-5 border border-gray-200 dark:border-gray-600">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 bg-purple-100 dark:bg-purple-900 rounded-lg p-3">
                                        <i class="fas fa-sitemap text-purple-600 dark:text-purple-400"></i>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">3. Sistem, Mekanisme & Prosedur</h3>
                                        <ol class="mt-2 space-y-2 text-gray-600 dark:text-gray-300 text-sm list-decimal list-inside">
                                            <li>Mengisi formulir permohonan secara online atau datang langsung ke PTSP</li>
                                            <li>Menyerahkan dokumen persyaratan yang diperlukan</li>
                                            <li>Petugas melakukan verifikasi administrasi</li>
                                            <li>Kepala Sekolah/TU melakukan persetujuan</li>
                                            <li>Surat dicetak dan ditandatangani</li>
                                            <li>Pemohon mengambil surat atau dikirim via pos/email</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>

                            <!-- Component 4: Jangka Waktu Penyelesaian -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-5 border border-gray-200 dark:border-gray-600">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 bg-amber-100 dark:bg-amber-900 rounded-lg p-3">
                                        <i class="fas fa-clock text-amber-600 dark:text-amber-400"></i>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">4. Jangka Waktu Penyelesaian</h3>
                                        <div class="mt-2">
                                            <div class="flex items-center mb-2">
                                                <span class="text-gray-600 dark:text-gray-300 text-sm">Waktu Normal:</span>
                                                <span class="ml-2 font-semibold text-gray-900 dark:text-white">1 Hari Kerja</span>
                                            </div>
                                            <div class="flex items-center">
                                                <span class="text-gray-600 dark:text-gray-300 text-sm">Waktu Maksimal:</span>
                                                <span class="ml-2 font-semibold text-gray-900 dark:text-white">3 Hari Kerja</span>
                                            </div>
                                            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                                * Tidak termasuk hari libur nasional dan cuti bersama
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Component 5: Biaya/Tarif -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-5 border border-gray-200 dark:border-gray-600">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 bg-red-100 dark:bg-red-900 rounded-lg p-3">
                                        <i class="fas fa-money-bill-wave text-red-600 dark:text-red-400"></i>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">5. Biaya/Tarif</h3>
                                        <div class="mt-2">
                                            <div class="flex items-center mb-1">
                                                <span class="text-gray-600 dark:text-gray-300 text-sm">Biaya Administrasi:</span>
                                                <span class="ml-2 font-semibold text-green-600 dark:text-green-400">Gratis (FREE)</span>
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                                Layanan ini tidak dikenakan biaya apapun
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Component 6: Produk Pelayanan -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-5 border border-gray-200 dark:border-gray-600">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 bg-cyan-100 dark:bg-cyan-900 rounded-lg p-3">
                                        <i class="fas fa-file-contract text-cyan-600 dark:text-cyan-400"></i>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">6. Produk Pelayanan</h3>
                                        <div class="mt-2">
                                            <p class="text-gray-600 dark:text-gray-300 text-sm mb-2">
                                                Hasil akhir dari pelayanan ini adalah:
                                            </p>
                                            <ul class="space-y-1 text-gray-600 dark:text-gray-300 text-sm">
                                                <li class="flex items-start">
                                                    <i class="fas fa-file-alt text-blue-500 mt-1 mr-2 text-xs"></i>
                                                    Surat Keterangan Siswa Aktif (berstempel basah)
                                                </li>
                                                <li class="flex items-start">
                                                    <i class="fas fa-file-pdf text-red-500 mt-1 mr-2 text-xs"></i>
                                                    Softcopy dalam format PDF (opsional)
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-between items-center pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="text-center sm:text-left">
                            <p class="text-gray-600 dark:text-gray-400">
                                Punya pertanyaan tentang layanan ini?
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Hubungi kami di (0341) 123456 atau email info@mtsn2malang.sch.id
                            </p>
                        </div>
                        <div class="flex gap-3">
                            <a href="<?php echo e(route('onlineportal.service.catalog')); ?>" 
                               class="px-5 py-2.5 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali
                            </a>
                            <a href="#" 
                               class="px-5 py-2.5 text-sm font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                                <i class="fas fa-file-medical mr-1"></i> Ajukan Layanan
                            </a>
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
<?php endif; ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\onlineportal\service-details.blade.php ENDPATH**/ ?>