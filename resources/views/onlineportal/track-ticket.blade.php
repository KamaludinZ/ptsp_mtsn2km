@extends('layouts.public')

@section('title', 'Lacak Tiket - ' . config('app.name', 'PTSP MTsN 2 Kota Malang'))

@section('content')
<div class="container py-5">
        <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="text-center mb-8">
                        <div class="mx-auto bg-blue-100 dark:bg-blue-900 w-16 h-16 rounded-full flex items-center justify-center">
                            <i class="fas fa-search text-blue-600 dark:text-blue-400 text-2xl"></i>
                        </div>
                        <h1 class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">
                            Lacak Status Tiket Pelayanan
                        </h1>
                        <p class="mt-2 text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                            Masukkan nomor tiket Anda untuk melihat perkembangan permohonan layanan
                        </p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Tracking Form -->
                        <div class="lg:col-span-2">
                            <div class="bg-white dark:bg-gray-700 rounded-xl shadow-lg p-6">
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">
                                    <i class="fas fa-ticket-alt mr-2 text-blue-600 dark:text-blue-400"></i>
                                    Formulir Pelacakan
                                </h2>

                                <form class="space-y-6">
                                    <div>
                                        <label for="ticket-number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Nomor Tiket <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" id="ticket-number" 
                                               class="block w-full p-4 text-lg border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500" 
                                               placeholder="Contoh: LAYANAN-202510-00123" required>
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            Nomor tiket dapat ditemukan di email konfirmasi atau struk permohonan Anda.
                                        </p>
                                    </div>

                                    <div class="pt-4">
                                        <button type="submit" 
                                                class="w-full px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all">
                                            <i class="fas fa-search mr-2"></i> Lacak Tiket
                                        </button>
                                    </div>
                                </form>

                                <div class="mt-8 bg-blue-50 dark:bg-blue-900 rounded-xl p-5">
                                    <h3 class="font-bold text-lg text-blue-800 dark:text-blue-200 mb-3">
                                        Tidak Mengetahui Nomor Tiket?
                                    </h3>
                                    <ul class="text-blue-700 dark:text-blue-300 space-y-2 text-sm">
                                        <li class="flex items-start">
                                            <i class="fas fa-check-circle text-blue-500 mt-1 mr-2"></i>
                                            <span>Cek email konfirmasi yang dikirim saat mengajukan layanan</span>
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-check-circle text-blue-500 mt-1 mr-2"></i>
                                            <span>Login ke akun Anda untuk melihat riwayat permohonan</span>
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-check-circle text-blue-500 mt-1 mr-2"></i>
                                            <span>Hubungi kami di (0341) 123456 untuk bantuan</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Sample Tracking Result -->
                        <div>
                            <div class="bg-white dark:bg-gray-700 rounded-xl shadow-lg p-6 sticky top-6">
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                                    <i class="fas fa-info-circle mr-2 text-gray-600 dark:text-gray-400"></i>
                                    Contoh Hasil Pelacakan
                                </h2>
                                
                                <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-600 dark:to-gray-700 rounded-lg p-4">
                                    <div class="flex justify-between items-center mb-3">
                                        <div>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                LAYANAN-202510-00123
                                            </span>
                                            <p class="mt-2 font-medium text-gray-900 dark:text-white">
                                                Surat Keterangan Siswa Aktif
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                <i class="fas fa-check mr-1"></i> Selesai
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4 space-y-3">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600 dark:text-gray-300">Tanggal Pengajuan</span>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">30 Okt 2025, 09:15</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600 dark:text-gray-300">Tanggal Penyelesaian</span>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">31 Okt 2025, 14:30</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600 dark:text-gray-300">Durasi Pengerjaan</span>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">1 Hari 5 Jam 15 Menit</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-600">
                                        <h3 class="font-medium text-gray-900 dark:text-white mb-2">Status Terkini</h3>
                                        <div class="space-y-3">
                                            <div class="flex">
                                                <div class="shrink-0">
                                                    <div class="w-5 h-5 rounded-full bg-green-500"></div>
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Permohonan Diterima</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">30 Okt 2025, 09:15</p>
                                                </div>
                                            </div>
                                            
                                            <div class="flex">
                                                <div class="shrink-0">
                                                    <div class="w-5 h-5 rounded-full bg-green-500"></div>
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Dalam Proses</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">30 Okt 2025, 11:30</p>
                                                </div>
                                            </div>
                                            
                                            <div class="flex">
                                                <div class="shrink-0">
                                                    <div class="w-5 h-5 rounded-full bg-green-500"></div>
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Selesai</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">31 Okt 2025, 14:30</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-600">
                                        <a href="#" 
                                           class="w-full block text-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg">
                                            Unduh Dokumen
                                        </a>
                                        <button class="w-full mt-2 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium rounded-lg">
                                            Bagikan Pengalaman
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6 bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-gray-600 dark:to-gray-700 rounded-xl p-5">
                                <h3 class="font-bold text-lg text-indigo-800 dark:text-indigo-200 mb-3">
                                    <i class="fas fa-star mr-2"></i> Nilai Pelayanan Kami
                                </h3>
                                <p class="text-indigo-700 dark:text-indigo-300 text-sm mb-4">
                                    Bantu kami meningkatkan kualitas pelayanan dengan memberikan penilaian Anda.
                                </p>
                                <a href="{{ route('survey.form') }}" 
                                   class="block w-full text-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg">
                                    <i class="fas fa-poll mr-2"></i> Isi Survei Sekarang
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Section -->
                    <div class="mt-12 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800 rounded-xl p-8">
                        <h2 class="text-2xl font-bold text-center text-gray-900 dark:text-white mb-8">
                            Pertanyaan Umum Tentang Pelacakan Tiket
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-white dark:bg-gray-700 rounded-lg p-5 shadow">
                                <h3 class="font-bold text-gray-900 dark:text-white mb-2">
                                    <i class="fas fa-question-circle text-blue-500 mr-2"></i>
                                    Berapa lama waktu pelayanan?
                                </h3>
                                <p class="text-gray-600 dark:text-gray-300 text-sm">
                                    Setiap layanan memiliki waktu penyelesaian yang berbeda sesuai dengan SOP. Secara umum, layanan selesai dalam waktu 1-5 hari kerja.
                                </p>
                            </div>
                            
                            <div class="bg-white dark:bg-gray-700 rounded-lg p-5 shadow">
                                <h3 class="font-bold text-gray-900 dark:text-white mb-2">
                                    <i class="fas fa-question-circle text-blue-500 mr-2"></i>
                                    Kenapa status tiket tidak berubah?
                                </h3>
                                <p class="text-gray-600 dark:text-gray-300 text-sm">
                                    Status akan diperbarui setiap kali ada perubahan. Jika lebih dari waktu maksimal belum berubah, hubungi kami untuk informasi lebih lanjut.
                                </p>
                            </div>
                            
                            <div class="bg-white dark:bg-gray-700 rounded-lg p-5 shadow">
                                <h3 class="font-bold text-gray-900 dark:text-white mb-2">
                                    <i class="fas fa-question-circle text-blue-500 mr-2"></i>
                                    Bagaimana cara mengambil dokumen?
                                </h3>
                                <p class="text-gray-600 dark:text-gray-300 text-sm">
                                    Dokumen dapat diambil langsung di loket PTSP atau dikirimkan secara digital sesuai dengan pilihan Anda saat pengajuan.
                                </p>
                            </div>
                            
                            <div class="bg-white dark:bg-gray-700 rounded-lg p-5 shadow">
                                <h3 class="font-bold text-gray-900 dark:text-white mb-2">
                                    <i class="fas fa-question-circle text-blue-500 mr-2"></i>
                                    Apakah saya bisa mengubah permohonan?
                                </h3>
                                <p class="text-gray-600 dark:text-gray-300 text-sm">
                                    Permohonan yang sedang diproses tidak dapat diubah. Namun, Anda dapat membuat permohonan baru jika diperlukan.
                                </p>
                            </div>
                        </div>
                        
                        <div class="mt-8 text-center">
                            <a href="#" 
                               class="inline-flex items-center px-5 py-3 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium rounded-lg">
                                <i class="fas fa-book-open mr-2"></i> Lihat Panduan Lengkap
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection