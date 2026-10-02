@php
    $d = $securityDetail;
    $grid = 'display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(11rem,1fr))';
    $stats = [
        ['Login gagal hari ini', $security['failed_logins_today'], $security['failed_logins_this_week'] . ' minggu ini', $security['failed_logins_today'] >= 20 ? 'danger' : ($security['failed_logins_today'] ? 'warning' : 'success')],
        ['Penguncian login', $d['lockouts_today'], 'Akibat terlalu banyak percobaan hari ini', $d['lockouts_today'] ? 'warning' : 'success'],
        ['Akun dinonaktifkan', $d['deactivated_accounts'], 'Tidak dapat masuk panel', 'gray'],
        ['Sesi aktif', $d['active_sessions'] ?? '–', 'Dalam ' . config('session.lifetime') . ' menit terakhir', 'gray'],
        ['IP diblokir', count($d['blocked']), $security['suspicious_activities_today'] . ' aktivitas mencurigakan hari ini', count($d['blocked']) ? 'warning' : 'success'],
        ['HTTPS', $d['https'] ? 'Aktif' : 'Belum', $d['https'] ? 'Koneksi terenkripsi' : 'Pasang sertifikat (Coolify mengaturnya otomatis)', $d['https'] ? 'success' : 'danger'],
    ];
    $levelLabel = ['danger' => 'Tinggi', 'warning' => 'Sedang', 'info' => 'Info'];
@endphp

<div style="{{ $grid }}">
    @foreach ($stats as [$title, $value, $detail, $color])
        <x-filament::section compact>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $title }}</p>
            <p class="text-2xl font-bold" style="color: rgb(var(--{{ $color === 'gray' ? 'gray-500' : $color . '-600' }}));">{{ $value }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $detail }}</p>
        </x-filament::section>
    @endforeach
</div>

<x-filament::section heading="Temuan konfigurasi berisiko" icon="heroicon-o-exclamation-triangle"
    :description="$d['last_scan'] ? 'Termasuk hasil pemindaian ' . \Illuminate\Support\Carbon::parse($d['last_scan']['timestamp'])->translatedFormat('j M Y H:i') . '.' : 'Belum ada pemindaian keamanan; jalankan dari menu Keamanan Sistem.'">
    @forelse ($d['findings'] as $finding)
        <div class="{{ $loop->first ? '' : 'border-t border-gray-200 dark:border-white/10' }}" style="display:flex;gap:.75rem;padding:.625rem 0;align-items:baseline">
            <x-filament::badge :color="$finding['level']">{{ $levelLabel[$finding['level']] ?? $finding['level'] }}</x-filament::badge>
            <div>
                <p class="text-sm font-semibold text-gray-950 dark:text-white">{{ $finding['title'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $finding['detail'] }}</p>
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada temuan. Konfigurasi terlihat aman.</p>
    @endforelse
</x-filament::section>

<x-filament::section heading="Percobaan login gagal terbaru" icon="heroicon-o-finger-print" collapsible>
    @if ($d['recent_failed'])
        <div style="overflow-x:auto">
            <table class="w-full text-sm" style="border-collapse:collapse">
                <thead><tr class="text-xs text-gray-500 dark:text-gray-400" style="text-align:left">
                    <th style="padding:.375rem .75rem .375rem 0">Waktu</th><th style="padding:.375rem .75rem">Akun</th><th style="padding:.375rem 0 .375rem .75rem">Alamat IP</th>
                </tr></thead>
                <tbody>
                    @foreach ($d['recent_failed'] as $attempt)
                        <tr class="border-t border-gray-200 dark:border-white/10">
                            <td style="padding:.375rem .75rem .375rem 0;white-space:nowrap">{{ \Illuminate\Support\Carbon::parse($attempt['at'])->translatedFormat('j M H:i') }}</td>
                            <td style="padding:.375rem .75rem">{{ $attempt['email'] ?? '–' }}</td>
                            <td style="padding:.375rem 0 .375rem .75rem">{{ $attempt['ip'] ?? '–' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada percobaan login gagal tercatat.</p>
    @endif
    <x-filament::link :href="\App\Filament\Pages\System\Security::getUrl()" icon="heroicon-m-shield-check" size="sm" style="margin-top:.75rem">
        Kelola blokir IP & pemindaian di Keamanan Sistem
    </x-filament::link>
</x-filament::section>
