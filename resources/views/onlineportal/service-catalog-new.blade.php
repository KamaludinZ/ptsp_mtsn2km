@extends('layouts.public')

@section('title', 'Katalog Layanan - PTSP MTsN 2 Kota Malang')

@section('content')
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

<!-- Hero Section -->
<section class="bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-800 dark:to-gray-900 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                <i class="fas fa-folder-open mr-3 text-blue-600 dark:text-blue-400"></i>
                Katalog Layanan
            </h1>
            <p class="text-lg text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                Daftar lengkap layanan yang tersedia sesuai Permen PANRB 15/2014 dan kebutuhan masyarakat
            </p>

            <!-- Statistics -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $services->count() }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Layanan</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $categories->count() }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Kategori</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">14+</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Standar Pelayanan</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">24/7</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Pelayanan</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Search and Filter -->
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text"
                               id="searchInput"
                               class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Cari layanan..."
                               onkeyup="filterServices()">
                    </div>
                </div>

                <div>
                    <select id="categoryFilter"
                            class="block w-full py-3 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            onchange="filterServices()">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->name }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- View Toggle -->
                <div class="flex justify-end">
                    <div class="inline-flex rounded-md shadow-sm" role="group">
                        <button type="button" 
                                id="gridView" 
                                class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-s-lg hover:bg-gray-100 dark:hover:bg-gray-600 focus:z-10">
                            <i class="fas fa-th-large"></i>
                        </button>
                        <button type="button" 
                                id="listView" 
                                class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border-t border-b border-gray-200 dark:border-gray-600 rounded-e-lg hover:bg-gray-100 dark:hover:bg-gray-600 focus:z-10">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services Container -->
        <div id="servicesContainer" class="services-grid">
            <div id="servicesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($services as $service)
                    <div class="service-card bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden border border-gray-200 dark:border-gray-600 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1" 
                         data-service="{{ $service->name }}" 
                         data-category="{{ $service->categories->first()->name ?? '' }}">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $service->code }}
                                    </span>
                                    <h3 class="mt-3 text-xl font-bold text-gray-900 dark:text-white">
                                        {{ $service->name }}
                                    </h3>
                                </div>
                                <div class="shrink-0">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        <i class="fas fa-check-circle mr-1"></i>Aktif
                                    </span>
                                </div>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">
                                {{ $service->description }}
                            </p>

                            <!-- Service Mode Badge -->
                            <div class="flex items-center mb-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                   @if($service->mode === 'online') bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200
                                   @elseif($service->mode === 'offline') bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-200
                                   @else bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-200 @endif">
                                    @if($service->mode === 'online')
                                        <i class="fas fa-wifi mr-1"></i>Online
                                    @elseif($service->mode === 'offline')
                                        <i class="fas fa-store mr-1"></i>Offline
                                    @else
                                        <i class="fas fa-sync-alt mr-1"></i>Hybrid
                                    @endif
                                </span>
                            </div>

                            <!-- Service Type Tags -->
                            <div class="flex flex-wrap gap-2 mb-4">
                                @php
                                    $userTypes = json_decode($service->user_types_allowed, true);
                                    $typeIcons = [
                                        'siswa' => 'fa-user-graduate',
                                        'alumni' => 'fa-user-tie',
                                        'umum' => 'fa-users',
                                        'instansi' => 'fa-building',
                                        'walimurid' => 'fa-user-friends',
                                        'pegawai' => 'fa-user-cog',
                                    ];
                                    $typeLabels = [
                                        'siswa' => 'Siswa',
                                        'alumni' => 'Alumni',
                                        'umum' => 'Umum',
                                        'instansi' => 'Instansi',
                                        'walimurid' => 'Wali Murid',
                                        'pegawai' => 'Pegawai',
                                    ];
                                @endphp
                                @foreach($userTypes as $userType)
                                    @if(isset($typeIcons[$userType]))
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                            <i class="fas {{ $typeIcons[$userType] }} mr-1"></i>{{ $typeLabels[$userType] }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>

                            <!-- Processing Time -->
                            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-4">
                                <i class="far fa-clock mr-2"></i>
                                <span>Estimasi: 1-3 hari kerja</span>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-2">
                                <button onclick="showServiceDetail('{{ $service->code }}')"
                                        class="flex-1 px-4 py-2 bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-800 text-sm font-medium rounded-lg transition-colors">
                                    <i class="fas fa-info-circle mr-2"></i>Detail
                                </button>

                                @auth
                                    <a href="{{ route('onlineportal.service.apply', $service->slug) }}"
                                       class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm text-center transition-colors">
                                        <i class="fas fa-file-medical mr-2"></i>Ajukan
                                    </a>
                                @else
                                    <a href="{{ route('login') }}"
                                       class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm text-center transition-colors">
                                        <i class="fas fa-sign-in-alt mr-2"></i>Login untuk Ajukan
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12">
                        <i class="fas fa-folder-open text-gray-400 text-6xl mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-400 mb-2">
                            Belum ada layanan tersedia
                        </h3>
                        <p class="text-gray-500 dark:text-gray-500">
                            Layanan akan segera tersedia. Silakan cek kembali nanti.
                        </p>
                    </div>
                @endforelse
            </div>
            
            <!-- Services List (initially hidden) -->
            <div id="servicesList" class="hidden">
                @forelse($services as $service)
                    <div class="service-card bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden border border-gray-200 dark:border-gray-600 mb-4 hover:shadow-lg transition-all duration-300" 
                         data-service="{{ $service->name }}" 
                         data-category="{{ $service->categories->first()->name ?? '' }}">
                        <div class="p-4 flex items-start">
                            <div class="flex-1">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ $service->code }}
                                        </span>
                                        <h3 class="mt-2 text-xl font-bold text-gray-900 dark:text-white">
                                            {{ $service->name }}
                                        </h3>
                                    </div>
                                    <div class="shrink-0">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            <i class="fas fa-check-circle mr-1"></i>Aktif
                                        </span>
                                    </div>
                                </div>

                                <p class="text-gray-600 dark:text-gray-300 text-sm mb-3">
                                    {{ $service->description }}
                                </p>

                                <!-- Service Mode and Type Tags -->
                                <div class="flex flex-wrap gap-2 mb-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                       @if($service->mode === 'online') bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200
                                       @elseif($service->mode === 'offline') bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-200
                                       @else bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-200 @endif">
                                        @if($service->mode === 'online')
                                            <i class="fas fa-wifi mr-1"></i>Online
                                        @elseif($service->mode === 'offline')
                                            <i class="fas fa-store mr-1"></i>Offline
                                        @else
                                            <i class="fas fa-sync-alt mr-1"></i>Hybrid
                                        @endif
                                    </span>
                                    
                                    @php
                                        $userTypes = json_decode($service->user_types_allowed, true);
                                        $typeIcons = [
                                            'siswa' => 'fa-user-graduate',
                                            'alumni' => 'fa-user-tie',
                                            'umum' => 'fa-users',
                                            'instansi' => 'fa-building',
                                            'walimurid' => 'fa-user-friends',
                                            'pegawai' => 'fa-user-cog',
                                        ];
                                        $typeLabels = [
                                            'siswa' => 'Siswa',
                                            'alumni' => 'Alumni',
                                            'umum' => 'Umum',
                                            'instansi' => 'Instansi',
                                            'walimurid' => 'Wali Murid',
                                            'pegawai' => 'Pegawai',
                                        ];
                                    @endphp
                                    @foreach($userTypes as $userType)
                                        @if(isset($typeIcons[$userType]))
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                                <i class="fas {{ $typeIcons[$userType] }} mr-1"></i>{{ $typeLabels[$userType] }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>

                                <!-- Processing Time -->
                                <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-4">
                                    <i class="far fa-clock mr-2"></i>
                                    <span>Estimasi: 1-3 hari kerja</span>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex gap-2">
                                    <button onclick="showServiceDetail('{{ $service->code }}')"
                                            class="flex-1 px-4 py-2 bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-800 text-sm font-medium rounded-lg transition-colors">
                                        <i class="fas fa-info-circle mr-2"></i>Detail
                                    </button>

                                    @auth
                                        <a href="{{ route('onlineportal.service.apply', $service->slug) }}"
                                           class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm text-center transition-colors">
                                            <i class="fas fa-file-medical mr-2"></i>Ajukan
                                        </a>
                                    @else
                                        <a href="{{ route('login') }}"
                                           class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm text-center transition-colors">
                                            <i class="fas fa-sign-in-alt mr-2"></i>Login untuk Ajukan
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <i class="fas fa-folder-open text-gray-400 text-6xl mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-400 mb-2">
                            Belum ada layanan tersedia
                        </h3>
                        <p class="text-gray-500 dark:text-gray-500">
                            Layanan akan segera tersedia. Silakan cek kembali nanti.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Standards Compliance Note -->
        <div class="mt-12 p-6 bg-blue-50 dark:bg-blue-900 rounded-xl">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-500 dark:text-blue-400 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-medium text-blue-800 dark:text-blue-200">
                        Kepatuhan Permen PANRB 15/2014
                    </h4>
                    <p class="mt-2 text-blue-700 dark:text-blue-300">
                        Semua layanan ini telah diselaraskan dengan 14 komponen standar pelayanan sesuai Peraturan Menteri PANRB Nomor 15 Tahun 2014 tentang Pedoman Pelayanan Publik.
                    </p>
                    <div class="mt-4">
                        <button onclick="showStandardsModal()" class="inline-flex items-center text-blue-700 dark:text-blue-300 hover:underline font-medium">
                            <i class="fas fa-list-check mr-2"></i> Lihat 14 Komponen Standar Pelayanan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Service Detail Modal -->
<div id="serviceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full z-50">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-6 flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white" id="modalTitle">Detail Layanan</h3>
                <button onclick="closeServiceModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="p-6" id="modalContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<!-- Standards Modal -->
<div id="standardsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full z-50">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-6 flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                    14 Komponen Standar Pelayanan
                </h3>
                <button onclick="closeStandardsModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="p-6">
                <div class="space-y-4">
                    <!-- Component 1 -->
                    <div class="border-l-4 border-blue-500 pl-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">
                            1. Dasar Hukum
                        </h4>
                        <p class="text-gray-600 dark:text-gray-300">
                            Landasan hukum penyelenggaraan layanan PTSP MTsN 2 Kota Malang sesuai peraturan perundang-undangan yang berlaku.
                        </p>
                    </div>

                    <!-- Component 2 -->
                    <div class="border-l-4 border-blue-500 pl-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">
                            2. Persyaratan
                        </h4>
                        <p class="text-gray-600 dark:text-gray-300">
                            Dokumen dan persyaratan yang harus dipenuhi oleh pemohon untuk mendapatkan pelayanan.
                        </p>
                    </div>

                    <!-- Component 3 -->
                    <div class="border-l-4 border-blue-500 pl-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">
                            3. Sistem, Mekanisme, Prosedur
                        </h4>
                        <p class="text-gray-600 dark:text-gray-300">
                            Alur dan tata cara pelayanan dari mulai pengajuan hingga selesai.
                        </p>
                    </div>

                    <!-- Component 4 -->
                    <div class="border-l-4 border-blue-500 pl-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">
                            4. Jangka Waktu Penyelesaian
                        </h4>
                        <p class="text-gray-600 dark:text-gray-300">
                            Estimasi waktu yang dibutuhkan untuk menyelesaikan setiap layanan.
                        </p>
                    </div>

                    <!-- Component 5 -->
                    <div class="border-l-4 border-blue-500 pl-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">
                            5. Biaya/Tarif
                        </h4>
                        <p class="text-gray-600 dark:text-gray-300">
                            Informasi biaya atau tarif layanan yang berlaku (jika ada).
                        </p>
                    </div>

                    <!-- Component 6 -->
                    <div class="border-l-4 border-blue-500 pl-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">
                            6. Produk Pelayanan
                        </h4>
                        <p class="text-gray-600 dark:text-gray-300">
                            Hasil akhir dari layanan yang diberikan kepada pemohon.
                        </p>
                    </div>

                    <!-- Component 7 -->
                    <div class="border-l-4 border-blue-500 pl-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">
                            7. Sarana, Prasarana
                        </h4>
                        <p class="text-gray-600 dark:text-gray-300">
                            Fasilitas yang mendukung penyelenggaraan layanan.
                        </p>
                    </div>

                    <!-- Component 8 -->
                    <div class="border-l-4 border-blue-500 pl-4">
                       h4 class="font-semibold text-gray-900 dark:text-white mb-2">
                            8. Kompetensi Pelaksana
                        </h4>
                        <p class="text-gray-600 dark:text-gray-300">
                            Kualifikasi dan kompetensi petugas pelaksana layanan.
                        </p>
                    </div>

                    <!-- Component 9 -->
                    <div class="border-l-4 border-blue-500 pl-4">
                       h4 class="font-semibold text-gray-900 dark:text-white mb-2">
                            9. Pengawasan Internal
                        </h4>
                        <p class="text-gray-600 dark:text-gray-300">
                            Mekanisme pengawasan internal untuk memastikan kualitas pelayanan.
                        </p>
                    </div>

                    <!-- Component 10 -->
                    <div class="border-l-4 border-blue-500 pl-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">
                            10. Penanganan Pengaduan
                        </h4>
                        <p class="text-gray-600 dark:text-gray-300">
                            Proses penanganan keluhan dan pengaduan dari masyarakat.
                        </p>
                    </div>

                    <!-- Component 11 -->
                    <div class="border-l-4 border-blue-500 pl-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">
                            11. Jumlah Pelaksana
                        </h4>
                        <p class="text-gray-600 dark:text-gray-300">
                            Jumlah petugas yang tersedia untuk setiap layanan.
                        </p>
                    </div>

                    <!-- Component 12 -->
                    <div class="border-l-4 border-blue-500 pl-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">
                            12. Jaminan Pelayanan
                        </h4>
                        <p class="text-gray-600 dark:text-gray-300">
                            Jaminan yang diberikan kepada pemohon terkait kualitas dan waktu pelayanan.
                        </p>
                    </div>

                    <!-- Component 13 -->
                    <div class="border-l-4 border-blue-500 pl-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">
                            13. Jaminan Keamanan
                        </h4>
                        <p class="text-gray-600 dark:text-gray-300">
                            Jaminan keamanan data dan informasi pemohon.
                        </p>
                    </div>

                    <!-- Component 14 -->
                    <div class="border-l-4 border-blue-500 pl-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">
                            14. Evaluasi Kinerja
                        </h4>
                        <p class="text-gray-600 dark:text-gray-300">
                            Mekanisme evaluasi kinerja layanan secara berkala.
                        </p>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                    <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        Untuk informasi lebih lengkap, silakan hubungi PTSP MTsN 2 Kota Malang.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Service data from controller
    <?php
    $servicesArray = $services->map(function($service) {
        return [
            'code' => $service->code,
            'name' => $service->name,
            'description' => $service->description,
            'category' => $service->categories->first()->name ?? '',
            'userTypes' => $service->user_types_allowed,
            'mode' => $service->mode,
            'processingTime' => $service->processing_time_days ?? 1
        ];
    })->toArray();
    ?>
    const servicesData = @json($servicesArray);

    // View mode functionality
    let currentViewMode = 'grid'; // Default to grid view
    
    document.addEventListener('DOMContentLoaded', function() {
        // Load saved view mode from localStorage if available
        const savedViewMode = localStorage.getItem('serviceViewMode');
        if (savedViewMode) {
            currentViewMode = savedViewMode;
            switchViewMode(currentViewMode);
        } else {
            switchViewMode('grid');
        }
        
        // Set active state for view toggle buttons
        updateViewToggleButtons();
        
        // Add event listeners for view toggle buttons
        document.getElementById('gridView').addEventListener('click', function() {
            switchViewMode('grid');
            localStorage.setItem('serviceViewMode', 'grid');
            updateViewToggleButtons();
        });
        
        document.getElementById('listView').addEventListener('click', function() {
            switchViewMode('list');
            localStorage.setItem('serviceViewMode', 'list');
            updateViewToggleButtons();
        });
    });
    
    function switchViewMode(mode) {
        currentViewMode = mode;
        
        const gridContainer = document.getElementById('servicesGrid');
        const listContainer = document.getElementById('servicesList');
        
        if (mode === 'grid') {
            gridContainer.classList.remove('hidden');
            listContainer.classList.add('hidden');
        } else {
            gridContainer.classList.add('hidden');
            listContainer.classList.remove('hidden');
        }
    }
    
    function updateViewToggleButtons() {
        const gridView = document.getElementById('gridView');
        const listView = document.getElementById('listView');
        
        if (currentViewMode === 'grid') {
            gridView.classList.add('bg-blue-500', 'text-white', 'dark:bg-blue-600', 'dark:text-white');
            gridView.classList.remove('bg-white', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
            listView.classList.remove('bg-blue-500', 'text-white', 'dark:bg-blue-600', 'dark:text-white');
            listView.classList.add('bg-white', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
        } else {
            listView.classList.add('bg-blue-500', 'text-white', 'dark:bg-blue-600', 'dark:text-white');
            listView.classList.remove('bg-white', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
            gridView.classList.remove('bg-blue-500', 'text-white', 'dark:bg-blue-600', 'dark:text-white');
            gridView.classList.add('bg-white', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
        }
    }

    function filterServices() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const categoryFilter = document.getElementById('categoryFilter').value;
        
        // Filter both grid and list views
        const gridCards = document.querySelectorAll('#servicesGrid .service-card');
        const listCards = document.querySelectorAll('#servicesList .service-card');

        let visibleCount = 0;

        // Filter grid view
        gridCards.forEach(card => {
            const serviceName = card.getAttribute('data-service').toLowerCase();
            const serviceCategory = card.getAttribute('data-category');

            const matchesSearch = !searchTerm || serviceName.includes(searchTerm);
            const matchesCategory = !categoryFilter || serviceCategory === categoryFilter;

            if (matchesSearch && matchesCategory) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Filter list view
        listCards.forEach(card => {
            const serviceName = card.getAttribute('data-service').toLowerCase();
            const serviceCategory = card.getAttribute('data-category');

            const matchesSearch = !searchTerm || serviceName.includes(searchTerm);
            const matchesCategory = !categoryFilter || serviceCategory === categoryFilter;

            if (matchesSearch && matchesCategory) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });

        // Show "no results" message if needed
        const noResults = document.querySelector('.no-results');
        if (visibleCount === 0 && gridCards.length > 0) {
            if (!noResults) {
                const noResultsDiv = document.createElement('div');
                noResultsDiv.className = 'no-results text-center py-12';
                noResultsDiv.innerHTML = `
                    <i class="fas fa-search text-gray-400 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-400 mb-2">
                        Tidak ada layanan yang ditemukan
                    </h3>
                    <p class="text-gray-500 dark:text-gray-500">
                        Coba kata kunci atau kategori yang berbeda.
                    </p>
                `;
                document.getElementById('servicesGrid').appendChild(noResultsDiv);
            }
        } else if (noResults) {
            noResults.remove();
        }
    }

    function showServiceDetail(serviceCode) {
        const service = servicesData.find(s => s.code === serviceCode);
        if (!service) return;

        document.getElementById('modalTitle').textContent = service.name;
        document.getElementById('modalContent').innerHTML = `
            <div class="space-y-6">
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Kode Layanan</h4>
                    <p class="text-gray-600 dark:text-gray-300">${service.code}</p>
                </div>

                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Deskripsi</h4>
                    <p class="text-gray-600 dark:text-gray-300">${service.description}</p>
                </div>

                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Mode Pelayanan</h4>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium 
                        ${service.mode === 'online' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                          service.mode === 'offline' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 
                          'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'}">
                        ${service.mode === 'online' ? '<i class="fas fa-wifi mr-1"></i>Online' : 
                          service.mode === 'offline' ? '<i class="fas fa-store mr-1"></i>Offline' : 
                          '<i class="fas fa-sync-alt mr-1"></i>Hybrid'}
                    </span>
                </div>

                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Kategori</h4>
                    <p class="text-gray-600 dark:text-gray-300">${service.category || 'Tidak ada kategori'}</p>
                </div>

                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Diperuntukan Untuk</h4>
                    <div class="flex flex-wrap gap-2">
                        ${service.userTypes.map(type => {
                            const icons = {
                                'siswa': 'fa-user-graduate',
                                'alumni': 'fa-user-tie',
                                'umum': 'fa-users',
                                'instansi': 'fa-building',
                                'walimurid': 'fa-user-friends',
                                'pegawai': 'fa-user-cog'
                            };
                            const labels = {
                                'siswa': 'Siswa',
                                'alumni': 'Alumni',
                                'umum': 'Umum',
                                'instansi': 'Instansi',
                                'walimurid': 'Wali Murid',
                                'pegawai': 'Pegawai'
                            };
                            const icon = icons[type] || 'fa-user';
                            const label = labels[type] || type;
                            return `
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                    <i class="fas ${icon} mr-1"></i>${label}
                                </span>
                            `;
                        }).join('')}
                    </div>
                </div>

                <!-- Service Components Section -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Komponen Layanan</h4>
                    
                    <div class="space-y-4">
                        <div>
                            <h5 class="font-medium text-gray-800 dark:text-gray-200 mb-2">1. Persyaratan</h5>
                            <p class="text-gray-600 dark:text-gray-300">${service.requirements || '-'}</p>
                        </div>
                        
                        <div>
                            <h5 class="font-medium text-gray-800 dark:text-gray-200 mb-2">2. Sistem, Mekanisme dan Prosedur</h5>
                            <p class="text-gray-600 dark:text-gray-300">${service.mechanism || '-'}</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h5 class="font-medium text-gray-800 dark:text-gray-200 mb-2">3. Waktu Penyelesaian</h5>
                                <p class="text-gray-600 dark:text-gray-300">${service.processingTime || 'Tidak disebutkan'}</p>
                            </div>
                            
                            <div>
                                <h5 class="font-medium text-gray-800 dark:text-gray-200 mb-2">4. Biaya / Tarif</h5>
                                <p class="text-gray-600 dark:text-gray-300">Rp ${service.fee ? parseFloat(service.fee).toLocaleString('id-ID') : 'Gratis'}</p>
                            </div>
                        </div>
                        
                        <div>
                            <h5 class="font-medium text-gray-800 dark:text-gray-200 mb-2">5. Produk Pelayanan</h5>
                            <p class="text-gray-600 dark:text-gray-300">${service.product || '-'}</p>
                        </div>
                        
                        <div>
                            <h5 class="font-medium text-gray-800 dark:text-gray-200 mb-2">6. Pengaduan Pelayanan</h5>
                            <p class="text-gray-600 dark:text-gray-300">${service.complaint_handling || '-'}</p>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button onclick="closeServiceModal()"
                            class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-lg transition-colors">
                        Tutup
                    </button>
                    @auth
                        <button onclick="applyService('${service.code}')"
                                class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                            <i class="fas fa-file-medical mr-2"></i>Ajukan Sekarang
                        </button>
                    @else
                        <a href="{{ route('login') }}"
                           class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-center transition-colors">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login untuk Ajukan
                        </a>
                    @endif
                </div>
            </div>
        `;

        document.getElementById('serviceModal').classList.remove('hidden');
    }

    function closeServiceModal() {
        document.getElementById('serviceModal').classList.add('hidden');
    }

    function showStandardsModal() {
        document.getElementById('standardsModal').classList.remove('hidden');
    }

    function closeStandardsModal() {
        document.getElementById('standardsModal').classList.add('hidden');
    }

    function applyService(serviceCode) {
        // Redirect to apply page
        window.location.href = `{{ url('/services') }}/${serviceCode}/apply`;
    }

    // Auto-filter on page load if URL has search params
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('search')) {
            document.getElementById('searchInput').value = urlParams.get('search');
            filterServices();
        }
        if (urlParams.get('category')) {
            document.getElementById('categoryFilter').value = urlParams.get('category');
            filterServices();
        }
    });

    // Update URL when filters change
    function updateURL() {
        const searchTerm = document.getElementById('searchInput').value;
        const categoryFilter = document.getElementById('categoryFilter').value;

        const params = new URLSearchParams();
        if (searchTerm) params.set('search', searchTerm);
        if (categoryFilter) params.set('category', categoryFilter);

        const newURL = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.history.replaceState({}, '', newURL);
    }

    // Add event listeners for URL updates
    document.getElementById('searchInput').addEventListener('input', updateURL);
    document.getElementById('categoryFilter').addEventListener('change', updateURL);
</script>
@endpush