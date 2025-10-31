<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Formulir Pengaduan Masyarakat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="text-center mb-8">
                        <div class="mx-auto bg-red-100 dark:bg-red-900 w-16 h-16 rounded-full flex items-center justify-center">
                            <i class="fas fa-exclamation-circle text-red-600 dark:text-red-400 text-2xl"></i>
                        </div>
                        <h1 class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">
                            Formulir Pengaduan Masyarakat
                        </h1>
                        <p class="mt-2 text-lg text-gray-600 dark:text-gray-300">
                            Sampaikan keluhan, saran, atau pengaduan Anda secara anonim atau terbuka
                        </p>
                    </div>

                    <!-- Complaint Types -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                        <div class="border border-red-200 dark:border-red-800 rounded-xl p-6 bg-red-50 dark:bg-red-900/20">
                            <div class="text-center">
                                <div class="mx-auto bg-red-100 dark:bg-red-900 rounded-full p-4 w-16 h-16 flex items-center justify-center">
                                    <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400 text-2xl"></i>
                                </div>
                                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Pengaduan</h3>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                    Laporkan keluhan atau masalah pelayanan yang Anda alami
                                </p>
                            </div>
                        </div>

                        <div class="border border-green-200 dark:border-green-800 rounded-xl p-6 bg-green-50 dark:bg-green-900/20">
                            <div class="text-center">
                                <div class="mx-auto bg-green-100 dark:bg-green-900 rounded-full p-4 w-16 h-16 flex items-center justify-center">
                                    <i class="fas fa-lightbulb text-green-600 dark:text-green-400 text-2xl"></i>
                                </div>
                                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Saran/Masukan</h3>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                    Berikan ide atau masukan untuk meningkatkan kualitas pelayanan
                                </p>
                            </div>
                        </div>

                        <div class="border border-yellow-200 dark:border-yellow-800 rounded-xl p-6 bg-yellow-50 dark:bg-yellow-900/20">
                            <div class="text-center">
                                <div class="mx-auto bg-yellow-100 dark:bg-yellow-900 rounded-full p-4 w-16 h-16 flex items-center justify-center">
                                    <i class="fas fa-bell text-yellow-600 dark:text-yellow-400 text-2xl"></i>
                                </div>
                                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Whistleblowing</h3>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                    Laporkan dugaan pelanggaran atau tindakan tidak etis secara rahasia
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Complaint Form -->
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-800 rounded-xl p-8">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                            <i class="fas fa-file-medical mr-2"></i> Formulir Pengaduan
                        </h2>
                        
                        <form class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="complaint_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Jenis Pengaduan <span class="text-red-500">*</span>
                                    </label>
                                    <select id="complaint_type" 
                                            class="block w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500" 
                                            required>
                                        <option value="">Pilih Jenis Pengaduan</option>
                                        <option value="complaint">Pengaduan</option>
                                        <option value="suggestion">Saran/Masukan</option>
                                        <option value="whistleblowing">Whistleblowing</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="related_service" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Layanan Terkait (Opsional)
                                    </label>
                                    <select id="related_service" 
                                            class="block w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Pilih Layanan</option>
                                        <option value="1">Surat Keterangan Siswa Aktif</option>
                                        <option value="2">Legalisir Ijazah</option>
                                        <option value="3">Surat Permohonan Izin Kegiatan</option>
                                        <option value="4">Surat Rekomendasi Beasiswa</option>
                                        <option value="5">Surat Keterangan Kelakuan Baik</option>
                                        <option value="6">Surat Keterangan Pindah Sekolah</option>
                                        <option value="7">Lainnya</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Judul <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="title" 
                                       class="block w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500" 
                                       placeholder="Masukkan judul pengaduan" required>
                            </div>
                            
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Deskripsi <span class="text-red-500">*</span>
                                </label>
                                <textarea id="description" rows="6" 
                                          class="block w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500" 
                                          placeholder="Jelaskan secara detail pengaduan Anda..." required></textarea>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="attachment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Lampiran (Opsional)
                                    </label>
                                    <div class="flex items-center justify-center w-full">
                                        <label for="attachment" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600">
                                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                <i class="fas fa-cloud-upload-alt text-gray-400 dark:text-gray-500 text-3xl mb-2"></i>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    <span class="font-semibold">Klik untuk mengunggah</span> atau seret file
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG, PDF (MAX. 10MB)</p>
                                            </div>
                                            <input id="attachment" type="file" class="hidden" />
                                        </label>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Identitas Pelapor
                                    </label>
                                    <div class="space-y-4">
                                        <div class="flex items-center">
                                            <input id="anonymous" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                            <label for="anonymous" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                                Kirim secara anonim
                                            </label>
                                        </div>
                                        
                                        <div id="identity_fields">
                                            <div class="mb-3">
                                                <label for="complainant_name" class="block text-xs text-gray-500 dark:text-gray-400 mb-1">
                                                    Nama Lengkap
                                                </label>
                                                <input type="text" id="complainant_name" 
                                                       class="block w-full p-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500" 
                                                       placeholder="Nama lengkap Anda">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="complainant_email" class="block text-xs text-gray-500 dark:text-gray-400 mb-1">
                                                    Email
                                                </label>
                                                <input type="email" id="complainant_email" 
                                                       class="block w-full p-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500" 
                                                       placeholder="Alamat email Anda">
                                            </div>
                                            
                                            <div>
                                                <label for="complainant_contact" class="block text-xs text-gray-500 dark:text-gray-400 mb-1">
                                                    Nomor Telepon (Opsional)
                                                </label>
                                                <input type="tel" id="complainant_contact" 
                                                       class="block w-full p-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500" 
                                                       placeholder="Nomor telepon yang bisa dihubungi">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Dengan mengirimkan pengaduan ini, Anda menyetujui 
                                    <a href="#" class="text-blue-600 dark:text-blue-400 hover:underline">Syarat dan Ketentuan</a> serta 
                                    <a href="#" class="text-blue-600 dark:text-blue-400 hover:underline">Kebijakan Privasi</a> kami.
                                </p>
                                <button type="submit" 
                                        class="w-full sm:w-auto px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                                    <i class="fas fa-paper-plane mr-2"></i> Kirim Pengaduan
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Information Section -->
                    <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-blue-50 dark:bg-blue-900 rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200 mb-3">
                                <i class="fas fa-shield-alt mr-2"></i> Perlindungan Whistleblower
                            </h3>
                            <p class="text-blue-700 dark:text-blue-300 text-sm">
                                Kami menjamin kerahasiaan identitas pelapor dalam kasus whistleblowing dan melindungi dari segala bentuk represaliasi.
                            </p>
                        </div>
                        
                        <div class="bg-green-50 dark:bg-green-900 rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-green-800 dark:text-green-200 mb-3">
                                <i class="fas fa-clock mr-2"></i> Waktu Respons
                            </h3>
                            <p class="text-green-700 dark:text-green-300 text-sm">
                                Pengaduan akan ditindaklanjuti dalam waktu maksimal 5 hari kerja sejak tanggal diterima.
                            </p>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="mt-10 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-gray-700 dark:to-gray-800 rounded-xl p-6">
                        <h3 class="text-xl font-bold text-center text-gray-900 dark:text-white mb-6">
                            Statistik Pengaduan 2025
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-center">
                            <div>
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">1,247</div>
                                <div class="text-sm text-gray-600 dark:text-gray-300">Total Pengaduan</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-green-600 dark:text-green-400">98%</div>
                                <div class="text-sm text-gray-600 dark:text-gray-300">Ditindaklanjuti</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">7</div>
                                <div class="text-sm text-gray-600 dark:text-gray-300">Hari Rata-rata</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">95%</div>
                                <div class="text-sm text-gray-600 dark:text-gray-300">Puas Ditangani</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>