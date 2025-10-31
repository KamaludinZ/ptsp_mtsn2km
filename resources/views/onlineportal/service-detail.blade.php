<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Detail Layanan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Back to Services Link -->
                    <div class="mb-6">
                        <a href="{{ route('onlineportal.service.catalog') }}" 
                           class="inline-flex items-center text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Katalog Layanan
                        </a>
                    </div>

                    <!-- Service Header -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-800 rounded-xl p-6 mb-8">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-3 mb-4">
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 text-sm font-medium rounded-full">
                                        LAY-001
                                    </span>
                                    <span class="px-3 py-1 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 text-sm font-medium rounded-full">
                                        <i class="fas fa-check-circle mr-1"></i> Aktif
                                    </span>
                                    <span class="px-3 py-1 bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 text-sm font-medium rounded-full">
                                        <i class="fas fa-user mr-1"></i> Untuk Siswa
                                    </span>
                                </div>
                                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-3">
                                    Surat Keterangan Siswa Aktif
                                </h1>
                                <p class="text-lg text-gray-700 dark:text-gray-300">
                                    Surat keterangan resmi untuk siswa aktif di MTsN 2 Kota Malang yang dapat digunakan untuk berbagai keperluan administrasi.
                                </p>
                            </div>
                            <div class="flex-shrink-0">
                                <button class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all">
                                    <i class="fas fa-file-medical mr-2"></i> Ajukan Layanan
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 14 Components of Service Standard (Permen PANRB 15/2014) -->
                    <div class="mb-10">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                            <i class="fas fa-list mr-2 text-blue-600 dark:text-blue-400"></i>
                            Standar Pelayanan Sesuai Permen PANRB 15/2014
                        </h2>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Component 1: Dasar Hukum -->
                            <div class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 shadow-sm">
                                <div class="p-5 border-b border-gray-200 dark:border-gray-600">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                        1. Dasar Hukum
                                    </h3>
                                </div>
                                <div class="p-5">
                                    <ul class="space-y-2 text-gray-600 dark:text-gray-300 text-sm">
                                        <li class="flex items-start">
                                            <i class="fas fa-gavel text-blue-500 mt-1 mr-3"></i>
                                            <span>UU No. 20 Tahun 2003 tentang Sistem Pendidikan Nasional</span>
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-gavel text-blue-500 mt-1 mr-3"></i>
                                            <span>Permendikbud No. 13 Tahun 2020 tentang Organisasi dan Tata Kerja MTsN</span>
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-gavel text-blue-500 mt-1 mr-3"></i>
                                            <span>Permen PANRB No. 15 Tahun 2014 tentang Pedoman Pelayanan Publik</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Component 2: Persyaratan -->
                            <div class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 shadow-sm">
                                <div class="p-5 border-b border-gray-200 dark:border-gray-600">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                        2. Persyaratan
                                    </h3>
                                </div>
                                <div class="p-5">
                                    <div class="space-y-3">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-file-upload text-green-500 mt-1"></i>
                                            </div>
                                            <div class="ml-3">
                                                <h4 class="font-medium text-gray-900 dark:text-white">Fotocopy Kartu Keluarga (KK)</h4>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">1 lembar</p>
                                            </div>
                                        </div>
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-file-upload text-green-500 mt-1"></i>
                                            </div>
                                            <div class="ml-3">
                                                <h4 class="font-medium text-gray-900 dark:text-white">Fotocopy KTP Orang Tua/Wali</h4>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">1 lembar</p>
                                            </div>
                                        </div>
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-file-upload text-green-500 mt-1"></i>
                                            </div>
                                            <div class="ml-3">
                                                <h4 class="font-medium text-gray-900 dark:text-white">Surat Permohonan dari Orang Tua/Wali</h4>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Format tersedia</p>
                                            </div>
                                        </div>
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-file-upload text-green-500 mt-1"></i>
                                            </div>
                                            <div class="ml-3">
                                                <h4 class="font-medium text-gray-900 dark:text-white">Pas Foto 3x4 terbaru</h4>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">2 lembar berwarna</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Component 3: Sistem, Mekanisme & Prosedur -->
                            <div class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 shadow-sm">
                                <div class="p-5 border-b border-gray-200 dark:border-gray-600">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                        3. Sistem, Mekanisme & Prosedur
                                    </h3>
                                </div>
                                <div class="p-5">
                                    <ol class="space-y-3 text-gray-600 dark:text-gray-300 text-sm list-decimal list-inside">
                                        <li>Mengisi formulir permohonan secara online atau datang langsung ke PTSP</li>
                                        <li>Menyerahkan dokumen persyaratan yang diperlukan</li>
                                        <li>Petugas melakukan verifikasi administrasi</li>
                                        <li>Kepala Sekolah/TU melakukan persetujuan</li>
                                        <li>Surat dicetak dan ditandatangani</li>
                                        <li>Pemohon mengambil surat atau dikirim via pos/email</li>
                                    </ol>
                                </div>
                            </div>

                            <!-- Component 4: Jangka Waktu Penyelesaian -->
                            <div class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 shadow-sm">
                                <div class="p-5 border-b border-gray-200 dark:border-gray-600">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                        4. Jangka Waktu Penyelesaian
                                    </h3>
                                </div>
                                <div class="p-5">
                                    <div class="space-y-4">
                                        <div class="flex justify-between">
                                            <div>
                                                <p class="text-gray-600 dark:text-gray-400">Waktu Normal</p>
                                                <p class="text-lg font-bold text-gray-900 dark:text-white">1 Hari Kerja</p>
                                            </div>
                                            <div>
                                                <p class="text-gray-600 dark:text-gray-400">Waktu Maksimal</p>
                                                <p class="text-lg font-bold text-gray-900 dark:text-white">3 Hari Kerja</p>
                                            </div>
                                        </div>
                                        <div class="bg-yellow-50 dark:bg-yellow-900 rounded-lg p-3">
                                            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                                <i class="fas fa-info-circle mr-2"></i> Tidak termasuk hari libur nasional dan cuti bersama
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Component 5: Biaya/Tarif -->
                            <div class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 shadow-sm">
                                <div class="p-5 border-b border-gray-200 dark:border-gray-600">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                        5. Biaya/Tarif
                                    </h3>
                                </div>
                                <div class="p-5">
                                    <div class="text-center py-4">
                                        <div class="text-3xl font-bold text-green-600 dark:text-green-400 mb-2">Gratis (FREE)</div>
                                        <p class="text-gray-600 dark:text-gray-300">Layanan ini tidak dikenakan biaya apapun</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Component 6: Produk Pelayanan -->
                            <div class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 shadow-sm">
                                <div class="p-5 border-b border-gray-200 dark:border-gray-600">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                        6. Produk Pelayanan
                                    </h3>
                                </div>
                                <div class="p-5">
                                    <div class="space-y-3">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-file-contract text-purple-500"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="font-medium text-gray-900 dark:text-white">Surat Keterangan Siswa Aktif</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Berstempel basah dan ditandatangani</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-file-pdf text-red-500"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="font-medium text-gray-900 dark:text-white">Softcopy dalam format PDF</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Opsional, dapat dikirim via email</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Component 7: Sarana & Prasarana -->
                            <div class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 shadow-sm">
                                <div class="p-5 border-b border-gray-200 dark:border-gray-600">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                        7. Sarana & Prasarana
                                    </h3>
                                </div>
                                <div class="p-5">
                                    <ul class="space-y-2 text-gray-600 dark:text-gray-300 text-sm">
                                        <li class="flex items-start">
                                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                            Ruang tunggu yang nyaman dan dilengkapi AC
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                            Meja pelayanan dengan petugas ramah
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                            Area bebas rokok dan nyaman
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                            Akses internet gratis untuk pengunjung
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                            Toilet umum yang bersih dan terawat
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Component 8: Kompetensi Pelaksana -->
                            <div class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 shadow-sm">
                                <div class="p-5 border-b border-gray-200 dark:border-gray-600">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                        8. Kompetensi Pelaksana
                                    </h3>
                                </div>
                                <div class="p-5">
                                    <ul class="space-y-3 text-gray-600 dark:text-gray-300 text-sm">
                                        <li class="flex items-start">
                                            <i class="fas fa-graduation-cap text-blue-500 mt-1 mr-3"></i>
                                            Pelatihan pelayanan prima untuk petugas PTSP
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-graduation-cap text-blue-500 mt-1 mr-3"></i>
                                            Sertifikasi kompetensi layanan publik
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-graduation-cap text-blue-500 mt-1 mr-3"></i>
                                            Workshop etika pelayanan dan komunikasi efektif
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-graduation-cap text-blue-500 mt-1 mr-3"></i>
                                            Pelatihan penggunaan sistem informasi pelayanan
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information Tabs -->
                    <div class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 shadow-sm">
                        <div class="border-b border-gray-200 dark:border-gray-600">
                            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                                <button data-tab-target="#pengawasan" class="tab-button py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                                    9. Pengawasan Internal
                                </button>
                                <button data-tab-target="#pengaduan" class="tab-button py-4 px-1 border-b-2 font-medium text-sm border-blue-500 text-blue-600 dark:text-blue-400" aria-current="page">
                                    10. Penanganan Pengaduan
                                </button>
                                <button data-tab-target="#jumlah-pelaksana" class="tab-button py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                                    11. Jumlah Pelaksana
                                </button>
                                <button data-tab-target="#jaminan-pelayanan" class="tab-button py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                                    12. Jaminan Pelayanan
                                </button>
                            </nav>
                        </div>

                        <div class="p-6">
                            <!-- Tab Panes -->
                            <div id="pengawasan" class="tab-pane hidden">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                                    Pengawasan Internal
                                </h3>
                                <div class="prose dark:prose-invert max-w-none">
                                    <p class="text-gray-600 dark:text-gray-300">
                                        Proses pengawasan internal dilakukan secara berkala oleh Kepala Tata Usaha dan Tim Monitoring Pelayanan untuk memastikan setiap layanan berjalan sesuai dengan standar yang telah ditetapkan.
                                    </p>
                                    <ul class="mt-4 space-y-2 text-gray-600 dark:text-gray-300">
                                        <li>Monitoring harian terhadap proses pelayanan</li>
                                        <li>Evaluasi bulanan kinerja petugas pelayanan</li>
                                        <li>Review triwulan terhadap kepuasan pengguna layanan</li>
                                        <li>Audit semester terhadap kualitas layanan secara keseluruhan</li>
                                    </ul>
                                </div>
                            </div>

                            <div id="pengaduan" class="tab-pane">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                                    Penanganan Pengaduan
                                </h3>
                                <div class="prose dark:prose-invert max-w-none">
                                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                                        Kami menyediakan berbagai saluran pengaduan untuk menampung keluhan, saran, dan aspirasi dari seluruh stakeholder. Setiap pengaduan akan ditindaklanjuti sesuai dengan SOP yang berlaku.
                                    </p>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                        <div class="bg-blue-50 dark:bg-blue-900 rounded-lg p-4">
                                            <div class="text-blue-800 dark:text-blue-200">
                                                <i class="fas fa-envelope-open-text text-2xl mb-2"></i>
                                                <h4 class="font-bold">Email</h4>
                                                <p class="text-sm mt-1">pengaduan@mtsn2malang.sch.id</p>
                                            </div>
                                        </div>
                                        <div class="bg-green-50 dark:bg-green-900 rounded-lg p-4">
                                            <div class="text-green-800 dark:text-green-200">
                                                <i class="fas fa-phone-alt text-2xl mb-2"></i>
                                                <h4 class="font-bold">Hotline</h4>
                                                <p class="text-sm mt-1">(0341) 123456</p>
                                            </div>
                                        </div>
                                        <div class="bg-purple-50 dark:bg-purple-900 rounded-lg p-4">
                                            <div class="text-purple-800 dark:text-purple-200">
                                                <i class="fas fa-comments text-2xl mb-2"></i>
                                                <h4 class="font-bold">Chat Live</h4>
                                                <p class="text-sm mt-1">Senin-Jumat: 08.00-15.00</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <h4 class="text-md font-bold text-gray-900 dark:text-white mb-2">
                                        Proses Penanganan Pengaduan:
                                    </h4>
                                    <ol class="mt-2 space-y-2 text-gray-600 dark:text-gray-300 list-decimal list-inside">
                                        <li>Rekam pengaduan dan berikan nomor tiket pengaduan</li>
                                        <li>Distribusikan pengaduan ke unit terkait</li>
                                        <li>Tindaklanjuti pengaduan sesuai SOP</li>
                                        <li>Verifikasi hasil tindak lanjut</li>
                                        <li>Beri respon kepada pelapor</li>
                                        <li>Rekam dalam database dan arsip</li>
                                    </ol>
                                </div>
                            </div>

                            <div id="jumlah-pelaksana" class="tab-pane hidden">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                                    Jumlah Pelaksana
                                </h3>
                                <div class="prose dark:prose-invert max-w-none">
                                    <p class="text-gray-600 dark:text-gray-300">
                                        Layanan ini dilayani oleh tim berikut:
                                    </p>
                                    <ul class="mt-4 space-y-2 text-gray-600 dark:text-gray-300">
                                        <li><span class="font-medium">1 Kepala Sekolah</span> - Pengambilan keputusan akhir</li>
                                        <li><span class="font-medium">1 Kepala Tata Usaha</span> - Koordinasi dan supervisi</li>
                                        <li><span class="font-medium">2 Petugas PTSP</span> - Penerimaan dan verifikasi administrasi</li>
                                        <li><span class="font-medium">1 Operator</span> - Pembuatan dan pencetakan dokumen</li>
                                    </ul>
                                    <p class="mt-4 text-gray-600 dark:text-gray-300">
                                        Total: 5 orang pelaksana yang terlibat dalam proses pelayanan ini.
                                    </p>
                                </div>
                            </div>

                            <div id="jaminan-pelayanan" class="tab-pane hidden">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                                    Jaminan Pelayanan
                                </h3>
                                <div class="prose dark:prose-invert max-w-none">
                                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                                        Kami memberikan jaminan pelayanan sebagai berikut:
                                    </p>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="bg-green-50 dark:bg-green-900 rounded-lg p-4">
                                            <h4 class="font-bold text-green-800 dark:text-green-200 mb-2">
                                                <i class="fas fa-thumbs-up mr-2"></i> Kualitas Pelayanan
                                            </h4>
                                            <ul class="text-green-700 dark:text-green-300 space-y-1 text-sm">
                                                <li>• Pelayanan sesuai SOP</li>
                                                <li>• Petugas terlatih dan profesional</li>
                                                <li>• Dokumen resmi dengan legalitas jelas</li>
                                            </ul>
                                        </div>
                                        
                                        <div class="bg-blue-50 dark:bg-blue-900 rounded-lg p-4">
                                            <h4 class="font-bold text-blue-800 dark:text-blue-200 mb-2">
                                                <i class="fas fa-clock mr-2"></i> Ketepatan Waktu
                                            </h4>
                                            <ul class="text-blue-700 dark:text-blue-300 space-y-1 text-sm">
                                                <li>• Pelayanan selesai sesuai SLA</li>
                                                <li>• Notifikasi jika melebihi waktu normal</li>
                                                <li>• Sanksi internal jika pelanggaran waktu</li>
                                            </ul>
                                        </div>
                                        
                                        <div class="bg-yellow-50 dark:bg-yellow-900 rounded-lg p-4">
                                            <h4 class="font-bold text-yellow-800 dark:text-yellow-200 mb-2">
                                                <i class="fas fa-shield-alt mr-2"></i> Keamanan Data
                                            </h4>
                                            <ul class="text-yellow-700 dark:text-yellow-300 space-y-1 text-sm">
                                                <li>• Kerahasiaan data pelanggan</li>
                                                <li>• Sistem keamanan data digital</li>
                                                <li>• SOP penanganan informasi</li>
                                            </ul>
                                        </div>
                                        
                                        <div class="bg-purple-50 dark:bg-purple-900 rounded-lg p-4">
                                            <h4 class="font-bold text-purple-800 dark:text-purple-200 mb-2">
                                                <i class="fas fa-hand-holding-usd mr-2"></i> Keadilan Pelayanan
                                            </h4>
                                            <ul class="text-purple-700 dark:text-purple-300 space-y-1 text-sm">
                                                <li>• Tanpa diskriminasi</li>
                                                <li>• Layanan sama untuk semua pengguna</li>
                                                <li>• Transparansi proses</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="text-gray-600 dark:text-gray-300">
                            <p>Diperbarui terakhir: 30 Oktober 2025</p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <button class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600">
                                <i class="fas fa-print mr-2"></i> Cetak Standar Pelayanan
                            </button>
                            <button class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <i class="fas fa-file-medical mr-2"></i> Ajukan Layanan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for tabs -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab functionality
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabPanes = document.querySelectorAll('.tab-pane');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active classes
                    tabButtons.forEach(btn => btn.classList.remove('text-blue-600', 'dark:text-blue-400', 'border-blue-500'));
                    tabButtons.forEach(btn => btn.classList.add('text-gray-500', 'dark:text-gray-400', 'border-transparent', 'hover:text-gray-700', 'hover:border-gray-300', 'dark:hover:text-gray-300'));
                    
                    // Add active class to clicked button
                    this.classList.remove('text-gray-500', 'dark:text-gray-400', 'border-transparent', 'hover:text-gray-700', 'hover:border-gray-300', 'dark:hover:text-gray-300');
                    this.classList.add('text-blue-600', 'dark:text-blue-400', 'border-blue-500');
                    
                    // Hide all panes
                    tabPanes.forEach(pane => pane.classList.add('hidden'));
                    
                    // Show target pane
                    const target = this.getAttribute('data-tab-target');
                    document.querySelector(target).classList.remove('hidden');
                });
            });
        });
    </script>
</x-app-layout>