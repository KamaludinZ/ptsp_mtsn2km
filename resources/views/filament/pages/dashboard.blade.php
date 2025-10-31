<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Welcome Card -->
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold">{{ $this->getHeading() }}</h2>
                    <p class="mt-2 text-green-100">
                        @php
                            $userType = auth()->user()->user_type ?? 'umum';
                            $messages = [
                                'guru' => 'Selamat datang di dashboard guru. Kelola layanan administratif Anda di sini.',
                                'pegawai' => 'Selamat datang di dashboard pegawai. Akses layanan kepegawaian Anda dengan mudah.',
                                'siswa' => 'Selamat datang di dashboard siswa. Ajukan berbagai layanan akademik dengan cepat.',
                                'alumni' => 'Selamat datang di dashboard alumni. Layanan untuk alumni tersedia di sini.',
                                'walimurid' => 'Selamat datang di dashboard wali murid. Monitor layanan untuk putra-putri Anda.',
                                'instansi' => 'Selamat datang di dashboard instansi. Kelola permohonan kerjasama dan layanan institusional.',
                                'umum' => 'Selamat datang di sistem PTSP MTsN 2 Kota Malang. Akses berbagai layanan publik kami.',
                            ];
                        @endphp
                        {{ $messages[$userType] ?? 'Selamat datang di sistem PTSP MTsN 2 Kota Malang.' }}
                    </p>
                </div>
                <div class="hidden md:block">
                    <div class="bg-white bg-opacity-20 rounded-full p-4">
                        @php
                            $icons = [
                                'guru' => 'heroicon-o-academic-cap',
                                'pegawai' => 'heroicon-o-identification',
                                'siswa' => 'heroicon-o-book-open',
                                'alumni' => 'heroicon-o-user-group',
                                'walimurid' => 'heroicon-o-users',
                                'instansi' => 'heroicon-o-building-office',
                                'umum' => 'heroicon-o-user',
                            ];
                            $icon = $icons[$userType] ?? 'heroicon-o-home';
                        @endphp
                        <x-filament::icon
                            :icon="$icon"
                            class="w-16 h-16 text-white"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('onlineportal.service.catalog') }}"
               class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-all hover:-translate-y-1 border-2 border-transparent hover:border-green-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                        <x-filament::icon icon="heroicon-o-document-plus" class="w-6 h-6 text-green-600" />
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Ajukan Layanan Baru</h3>
                        <p class="text-sm text-gray-600">Buat permohonan layanan</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('onlineportal.track.ticket.form') }}"
               class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-all hover:-translate-y-1 border-2 border-transparent hover:border-orange-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-orange-100 rounded-lg p-3">
                        <x-filament::icon icon="heroicon-o-magnifying-glass" class="w-6 h-6 text-orange-600" />
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Lacak Tiket</h3>
                        <p class="text-sm text-gray-600">Cek status permohonan</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('supervision.complaints.dashboard') }}"
               class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-all hover:-translate-y-1 border-2 border-transparent hover:border-red-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-100 rounded-lg p-3">
                        <x-filament::icon icon="heroicon-o-exclamation-circle" class="w-6 h-6 text-red-600" />
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Pengaduan</h3>
                        <p class="text-sm text-gray-600">Sampaikan keluhan</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Widgets -->
        <div class="grid grid-cols-1 gap-6">
            <x-filament-widgets::widgets
                :widgets="$this->getWidgets()"
                :columns="[
                    'md' => 2,
                    'xl' => 3,
                ]"
            />
        </div>

        <!-- Help Section -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg shadow p-6 border border-blue-200">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <x-filament::icon icon="heroicon-o-information-circle" class="w-6 h-6 text-blue-600" />
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Butuh Bantuan?</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Jika Anda mengalami kesulitan dalam menggunakan sistem, silakan hubungi petugas PTSP di (0341) 123456
                        atau email ke <a href="mailto:info@mtsn2malang.sch.id" class="text-blue-600 hover:text-blue-800 underline">info@mtsn2malang.sch.id</a>
                    </p>
                    <div class="mt-4 flex gap-2">
                        <x-filament::button
                            tag="a"
                            href="#"
                            color="primary"
                            size="sm"
                            icon="heroicon-o-book-open"
                        >
                            Panduan Pengguna
                        </x-filament::button>
                        <x-filament::button
                            tag="a"
                            href="#"
                            color="gray"
                            size="sm"
                            icon="heroicon-o-question-mark-circle"
                            outlined
                        >
                            FAQ
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
