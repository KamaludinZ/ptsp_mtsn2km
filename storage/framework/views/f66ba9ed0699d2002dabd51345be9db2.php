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
            <?php echo e(__('Buku Tamu')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="text-center mb-8">
                        <div class="mx-auto bg-indigo-100 dark:bg-indigo-900 w-16 h-16 rounded-full flex items-center justify-center">
                            <i class="fas fa-book text-indigo-600 dark:text-indigo-400 text-2xl"></i>
                        </div>
                        <h1 class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">
                            Buku Tamu
                        </h1>
                        <p class="mt-2 text-lg text-gray-600 dark:text-gray-300">
                            Catatan Kunjungan Tamu MTsN 2 Kota Malang
                        </p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Check-in Form -->
                        <div class="lg:col-span-2">
                            <div class="bg-white dark:bg-gray-700 rounded-xl shadow-lg p-6">
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">
                                    <i class="fas fa-sign-in-alt mr-2 text-blue-600 dark:text-blue-400"></i>
                                    Registrasi Kunjungan
                                </h2>

                                <form class="space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="visitor-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Nama Lengkap <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" id="visitor-name" 
                                                   class="block w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500" 
                                                   placeholder="Masukkan nama lengkap Anda" required>
                                        </div>

                                        <div>
                                            <label for="visitor-institution" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Institusi/Organisasi
                                            </label>
                                            <input type="text" id="visitor-institution" 
                                                   class="block w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500" 
                                                   placeholder="Nama kantor/organisasi (jika ada)">
                                        </div>
                                    </div>

                                    <div>
                                        <label for="visit-purpose" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Tujuan Kunjungan <span class="text-red-500">*</span>
                                        </label>
                                        <select id="visit-purpose" 
                                                class="block w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500" 
                                                required>
                                            <option value="">Pilih Tujuan</option>
                                            <option value="meeting">Pertemuan/Rapat</option>
                                            <option value="consultation">Konsultasi</option>
                                            <option value="document">Pengambilan Dokumen</option>
                                            <option value="information">Informasi</option>
                                            <option value="complaint">Pengaduan</option>
                                            <option value="other">Lainnya</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="visit-description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Deskripsi Kunjungan
                                        </label>
                                        <textarea id="visit-description" rows="3" 
                                                  class="block w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500" 
                                                  placeholder="Jelaskan secara singkat tujuan kunjungan Anda..."></textarea>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="person-to-meet" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Orang yang Dituju
                                            </label>
                                            <select id="person-to-meet" 
                                                    class="block w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                                <option value="">Pilih Penerima Tamu</option>
                                                <option value="principal">Kepala Sekolah</option>
                                                <option value="vice-principal-academic">Wakil Kepala Bidang Akademik</option>
                                                <option value="vice-principal-student">Wakil Kepala Bidang Kesiswaan</option>
                                                <option value="finance">Bendahara</option>
                                                <option value="administration">Tata Usaha</option>
                                                <option value="curriculum">Kurikulum</option>
                                                <option value="facility">Sarana Prasarana</option>
                                                <option value="humas">Hubungan Masyarakat</option>
                                                <option value="library">Perpustakaan</option>
                                                <option value="other">Lainnya</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label for="phone-number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Nomor Telepon
                                            </label>
                                            <input type="tel" id="phone-number" 
                                                   class="block w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500" 
                                                   placeholder="Nomor HP yang bisa dihubungi">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                            Identifikasi Tamu
                                        </label>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="flex items-center">
                                                <input id="visitor-type-public" type="radio" name="visitor-type" value="public" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" checked>
                                                <label for="visitor-type-public" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                                    Tamu Umum (Public)
                                                </label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="visitor-type-staff" type="radio" name="visitor-type" value="staff" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="visitor-type-staff" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                                    Staf/Internal (Staff)
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pt-4">
                                        <button type="submit" 
                                                class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all">
                                            <i class="fas fa-user-check mr-2"></i> Daftar Kunjungan
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div class="mt-8 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-gray-700 dark:to-gray-800 rounded-xl p-6">
                                <div class="flex">
                                    <div class="shrink-0">
                                        <i class="fas fa-info-circle text-green-600 dark:text-green-400 text-xl"></i>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-green-800 dark:text-green-200">
                                            Informasi Kunjungan
                                        </h3>
                                        <div class="mt-2 text-green-700 dark:text-green-300 text-sm space-y-1">
                                            <p>• Pastikan Anda mengisi data dengan benar</p>
                                            <p>• Kartu tamu akan dicetak setelah registrasi</p>
                                            <p>• Tunjukkan kartu tamu kepada petugas keamanan</p>
                                            <p>• Kunjungan maksimal 2 jam kerja</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Visitor Statistics -->
                        <div>
                            <div class="bg-white dark:bg-gray-700 rounded-xl shadow-lg p-6 mb-6">
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                                    <i class="fas fa-chart-bar mr-2 text-purple-600 dark:text-purple-400"></i>
                                    Statistik Hari Ini
                                </h2>
                                
                                <div class="space-y-4">
                                    <div class="bg-blue-50 dark:bg-blue-900 rounded-lg p-4">
                                        <div class="flex justify-between items-center">
                                            <span class="text-blue-800 dark:text-blue-200 font-medium">Tamu Terdaftar</span>
                                            <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">24</span>
                                        </div>
                                    </div>
                                    
                                    <div class="bg-green-50 dark:bg-green-900 rounded-lg p-4">
                                        <div class="flex justify-between items-center">
                                            <span class="text-green-800 dark:text-green-200 font-medium">Belum Checkout</span>
                                            <span class="text-2xl font-bold text-green-600 dark:text-green-400">8</span>
                                        </div>
                                    </div>
                                    
                                    <div class="bg-amber-50 dark:bg-amber-900 rounded-lg p-4">
                                        <div class="flex justify-between items-center">
                                            <span class="text-amber-800 dark:text-amber-200 font-medium">Tujuan Umum</span>
                                            <span class="text-2xl font-bold text-amber-600 dark:text-amber-400">15</span>
                                        </div>
                                    </div>
                                    
                                    <div class="bg-purple-50 dark:bg-purple-900 rounded-lg p-4">
                                        <div class="flex justify-between items-center">
                                            <span class="text-purple-800 dark:text-purple-200 font-medium">Staf/Internal</span>
                                            <span class="text-2xl font-bold text-purple-600 dark:text-purple-400">9</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white dark:bg-gray-700 rounded-xl shadow-lg p-6">
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                                    <i class="fas fa-users mr-2 text-indigo-600 dark:text-indigo-400"></i>
                                    Tamu Saat Ini
                                </h2>
                                
                                <div class="space-y-3 max-h-96 overflow-y-auto">
                                    <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-600 rounded-lg">
                                        <div class="bg-gray-200 border-2 border-dashed rounded-xl w-10 h-10" />
                                        <div class="ml-3 flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                Budi Santoso
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                Dinas Pendidikan
                                            </p>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            09:45
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-600 rounded-lg">
                                        <div class="bg-gray-200 border-2 border-dashed rounded-xl w-10 h-10" />
                                        <div class="ml-3 flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                Nurul Hidayah
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                Orang Tua Siswa
                                            </p>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            10:15
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-600 rounded-lg">
                                        <div class="bg-gray-200 border-2 border-dashed rounded-xl w-10 h-10" />
                                        <div class="ml-3 flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                Andi Permana
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                CV Teknologi Maju
                                            </p>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            10:30
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                                    <a href="#" 
                                       class="text-center block text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                                        Lihat Semua Tamu <i class="fas fa-arrow-right ml-1 text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Visitor Guidelines -->
                    <div class="mt-8 bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-gray-700 dark:to-gray-800 rounded-xl p-6">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                            <i class="fas fa-book-open mr-2 text-indigo-600 dark:text-indigo-400"></i>
                            Panduan Kunjungan
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="bg-white dark:bg-gray-700 rounded-lg p-4 shadow-sm">
                                <div class="flex items-start">
                                    <div class="shrink-0">
                                        <div class="bg-blue-100 dark:bg-blue-900 rounded-lg w-10 h-10 flex items-center justify-center">
                                            <i class="fas fa-id-card text-blue-600 dark:text-blue-400"></i>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="font-medium text-gray-900 dark:text-white">1. Registrasi</h3>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                            Isi formulir dengan data yang sebenar-benarnya
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-white dark:bg-gray-700 rounded-lg p-4 shadow-sm">
                                <div class="flex items-start">
                                    <div class="shrink-0">
                                        <div class="bg-green-100 dark:bg-green-900 rounded-lg w-10 h-10 flex items-center justify-center">
                                            <i class="fas fa-print text-green-600 dark:text-green-400"></i>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="font-medium text-gray-900 dark:text-white">2. Cetak Kartu</h3>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                            Tunjukkan kartu tamu yang telah dicetak
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-white dark:bg-gray-700 rounded-lg p-4 shadow-sm">
                                <div class="flex items-start">
                                    <div class="shrink-0">
                                        <div class="bg-purple-100 dark:bg-purple-900 rounded-lg w-10 h-10 flex items-center justify-center">
                                            <i class="fas fa-user-clock text-purple-600 dark:text-purple-400"></i>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="font-medium text-gray-900 dark:text-white">3. Tunggu</h3>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                            Tunggu panggilan dari penerima tamu
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-600">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">
                                Aturan dan Etika Kunjungan
                            </h3>
                            <ul class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-gray-600 dark:text-gray-300">
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                    <span>Pakai pakaian rapi dan sopan</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                    <span>Menghormati semua civitas akademika</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                    <span>Mematuhi tata tertib kunjungan</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                    <span>Menjaga kebersihan dan ketertiban</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                    <span>Menjaga privasi dan data pribadi</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                    <span>Checkout setelah selesai kunjungan</span>
                                </li>
                            </ul>
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
<?php endif; ?><?php /**PATH C:\ptsponline\PTSP-MTsN-2-KOTA-MALANG\resources\views\frontdesk\visitor-book.blade.php ENDPATH**/ ?>