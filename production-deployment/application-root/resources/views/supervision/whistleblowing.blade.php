<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Whistleblowing') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="text-center mb-8">
                        <div class="mx-auto bg-red-100 dark:bg-red-900 w-16 h-16 rounded-full flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400 text-2xl"></i>
                        </div>
                        <h1 class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">
                            Sistem Pelaporan Whistleblowing
                        </h1>
                        <p class="mt-2 text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                            Laporkan dugaan pelanggaran, penyalahgunaan wewenang, atau perilaku tidak etis secara aman dan terlindungi
                        </p>
                    </div>

                    <!-- Confidentiality Guarantee -->
                    <div class="bg-linear-to-br from-red-50 to-orange-50 dark:from-gray-700 dark:to-gray-800 rounded-xl p-6 mb-8">
                        <div class="flex flex-col md:flex-row items-center gap-6">
                            <div class="shrink-0">
                                <div class="bg-red-100 dark:bg-red-900 rounded-full p-4">
                                    <i class="fas fa-shield-alt text-red-600 dark:text-red-400 text-3xl"></i>
                                </div>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                                    <i class="fas fa-lock mr-2"></i> Kerahasiaan Terjamin
                                </h2>
                                <p class="text-gray-700 dark:text-gray-300">
                                    Kami menjamin kerahasiaan identitas pelapor dan melindungi dari segala bentuk represaliasi. 
                                    Laporan Anda akan ditangani dengan profesional dan rahasia.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Whistleblowing Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="bg-white dark:bg-gray-700 rounded-xl p-6 shadow">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                                <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 mr-2"></i>
                                Apa itu Whistleblowing?
                            </h3>
                            <p class="text-gray-600 dark:text-gray-300">
                                Whistleblowing adalah mekanisme pelaporan dugaan pelanggaran yang dilakukan secara terbuka dan bertanggung jawab 
                                oleh pegawai atau pihak lain yang mengetahui adanya dugaan pelanggaran.
                            </p>
                        </div>

                        <div class="bg-white dark:bg-gray-700 rounded-xl p-6 shadow">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                                <i class="fas fa-question-circle text-green-600 dark:text-green-400 mr-2"></i>
                                Jenis Pelanggaran
                            </h3>
                            <ul class="text-gray-600 dark:text-gray-300 space-y-2">
                                <li class="flex items-center">
                                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                    Korupsi dan Gratifikasi
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                    Nepotisme dan Kolusi
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                    Pelanggaran Kode Etik
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                    Penyalahgunaan Wewenang
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Whistleblowing Form -->
                    <div class="bg-white dark:bg-gray-700 rounded-xl shadow">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-600">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                Formulir Whistleblowing
                            </h3>
                            <p class="text-gray-600 dark:text-gray-300 mt-1">
                                Lengkapi formulir di bawah ini dengan sebenar-benarnya
                            </p>
                        </div>

                        <form class="p-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="violation-category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Kategori Pelanggaran <span class="text-red-500">*</span>
                                    </label>
                                    <select id="violation-category" 
                                            class="block w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500" 
                                            required>
                                        <option value="">Pilih Kategori</option>
                                        <option value="corruption">Korupsi</option>
                                        <option value="gratification">Gratifikasi</option>
                                        <option value="nepotism">Nepotisme/Kolusi</option>
                                        <option value="misconduct">Pelanggaran Etika</option>
                                        <option value="misuse">Penyalahgunaan Wewenang</option>
                                        <option value="other">Lainnya</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="incident-date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Tanggal Kejadian
                                    </label>
                                    <input type="date" id="incident-date" 
                                           class="block w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <div>
                                <label for="incident-description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Deskripsi Kejadian <span class="text-red-500">*</span>
                                </label>
                                <textarea id="incident-description" rows="5" 
                                          class="block w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500" 
                                          placeholder="Jelaskan secara detail kejadian pelanggaran..." required></textarea>
                            </div>

                            <div>
                                <label for="evidence" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Bukti Pendukung
                                </label>
                                <div class="flex items-center justify-center w-full">
                                    <label for="evidence" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600">
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                            <i class="fas fa-cloud-upload-alt text-gray-400 dark:text-gray-500 text-3xl mb-2"></i>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                <span class="font-semibold">Klik untuk mengunggah</span> atau seret file
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                PNG, JPG, PDF, DOCX (MAX. 10MB)
                                            </p>
                                        </div>
                                        <input id="evidence" type="file" class="hidden" multiple />
                                    </label>
                                </div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    Anda dapat mengunggah beberapa file sebagai bukti pendukung
                                </p>
                            </div>

                            <div class="border-t border-gray-200 dark:border-gray-600 pt-6">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                    Identitas Pelapor
                                </h4>
                                
                                <div class="space-y-4">
                                    <div class="flex items-center">
                                        <input id="anonymous-report" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                        <label for="anonymous-report" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                            Laporkan secara anonim
                                        </label>
                                    </div>
                                    
                                    <div id="reporter-identity" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="reporter-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Nama Lengkap
                                            </label>
                                            <input type="text" id="reporter-name" 
                                                   class="block w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        
                                        <div>
                                            <label for="reporter-position" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Jabatan/Posisi
                                            </label>
                                            <input type="text" id="reporter-position" 
                                                   class="block w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        
                                        <div>
                                            <label for="reporter-email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Email
                                            </label>
                                            <input type="email" id="reporter-email" 
                                                   class="block w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        
                                        <div>
                                            <label for="reporter-phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Nomor Telepon
                                            </label>
                                            <input type="tel" id="reporter-phone" 
                                                   class="block w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-gray-200 dark:border-gray-600 pt-6">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                    Preferensi Kontak
                                </h4>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Bagaimana cara kami menghubungi Anda? (Jika tidak anonim)
                                        </label>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                            <div class="flex items-center">
                                                <input id="contact-email" type="checkbox" value="email" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="contact-email" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Email</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="contact-phone" type="checkbox" value="phone" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="contact-phone" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Telepon</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="contact-visit" type="checkbox" value="visit" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="contact-visit" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Kunjungan</label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="bg-yellow-50 dark:bg-yellow-900 rounded-lg p-4">
                                        <div class="flex">
                                            <i class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400 mt-0.5"></i>
                                            <div class="ml-3">
                                                <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                                    Penting: Perlindungan Whistleblower
                                                </h4>
                                                <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                                                    Kami melindungi pelapor dari segala bentuk represaliasi dan menjaga kerahasiaan identitas sesuai undang-undang.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-6 border-t border-gray-200 dark:border-gray-600">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Dengan mengirimkan laporan ini, Anda menyetujui 
                                    <a href="#" class="text-blue-600 hover:underline dark:text-blue-400">Syarat & Ketentuan</a> 
                                    serta <a href="#" class="text-blue-600 hover:underline dark:text-blue-400">Kebijakan Privasi</a>.
                                </p>
                                <button type="submit" 
                                        class="w-full sm:w-auto px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                                    <i class="fas fa-paper-plane mr-2"></i> Kirim Laporan
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Protection Information -->
                    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-linear-to-br from-green-50 to-emerald-50 dark:from-gray-700 dark:to-gray-800 rounded-xl p-6">
                            <div class="flex">
                                <div class="shrink-0">
                                    <i class="fas fa-user-shield text-green-600 dark:text-green-400 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                        Perlindungan Terhadap Pelapor
                                    </h3>
                                    <p class="text-gray-700 dark:text-gray-300 text-sm">
                                        Identitas pelapor dilindungi secara mutlak dan tidak akan dibocorkan kepada pihak manapun.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-linear-to-br from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-800 rounded-xl p-6">
                            <div class="flex">
                                <div class="shrink-0">
                                    <i class="fas fa-balance-scale text-blue-600 dark:text-blue-400 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                        Dasar Hukum
                                    </h3>
                                    <p class="text-gray-700 dark:text-gray-300 text-sm">
                                        Laporan dikelola sesuai UU No. 30 Tahun 2019 tentang Perlindungan Whistleblower.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>