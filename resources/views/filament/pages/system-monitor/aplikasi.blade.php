@php
    use App\Services\SystemMonitorService as M;

    $badge = fn (bool $ok, string $yes, string $no) => [$ok ? 'success' : 'danger', $ok ? $yes : $no];
    $rows = [
        ['Status', ...$badge($app['up'], 'Berjalan', $app['maintenance'] ? 'Mode perawatan' : 'Bermasalah'), null],
        ['Versi aplikasi', 'gray', $app['version'], null],
        ['Lingkungan', $app['environment'] === 'production' ? 'success' : 'warning', $app['environment'], $app['debug'] ? 'Mode debug AKTIF — matikan di produksi.' : 'Mode debug nonaktif.'],
        ['PHP / Laravel', 'gray', $app['php'] . ' / ' . $app['laravel'], null],
        ['Koneksi database', ...$badge($app['database']['ok'], 'Terhubung', 'Gagal'), $app['database']['ok'] ? 'Latensi ' . $app['database']['latency_ms'] . ' ms (' . $app['database']['driver'] . ')' : $app['database']['error']],
        ['Antrean (queue)', 'gray', $app['queue']['connection'],
            collect([
                $app['queue']['pending'] !== null ? $app['queue']['pending'] . ' job menunggu' : ($app['queue']['connection'] === 'sync' ? 'Dijalankan langsung (sync), tanpa antrean' : null),
                $app['queue']['failed'] !== null ? $app['queue']['failed'] . ' job gagal' : null,
            ])->filter()->join(' · ')],
        ['Scheduler', ...$badge($app['scheduler']['ok'], 'Aktif', 'Tidak terdeteksi'),
            $app['scheduler']['last_run'] ? 'Detak terakhir ' . \Illuminate\Support\Carbon::parse($app['scheduler']['last_run'])->translatedFormat('j M Y H:i') : 'Belum ada detak. Pastikan "php artisan schedule:work" berjalan.'],
        ['Penyimpanan', ...$badge($app['storage']['writable'], 'Dapat ditulis', 'Tidak dapat ditulis'),
            M::bytes($app['storage']['disk']['free']) . ' kosong · terpakai ' . ($app['storage']['disk']['used_percent'] ?? '–') . '%'],
        ['Waktu respons halaman', 'gray', $app['response_ms'] !== null ? $app['response_ms'] . ' ms' : '–', 'Waktu server menyiapkan halaman ini.'],
    ];
@endphp

<x-filament::section heading="Kondisi aplikasi" icon="heroicon-o-cube">
    <dl style="display:grid;grid-template-columns:minmax(9rem,14rem) 1fr;gap:.75rem 1rem;margin:0">
        @foreach ($rows as [$label, $color, $value, $detail])
            <dt class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</dt>
            <dd style="margin:0">
                <x-filament::badge :color="$color" style="display:inline-flex">{{ $value }}</x-filament::badge>
                @if ($detail)
                    <span class="block text-xs text-gray-500 dark:text-gray-400" style="margin-top:.25rem">{{ $detail }}</span>
                @endif
            </dd>
        @endforeach
    </dl>
</x-filament::section>

{{-- Integrasi notifikasi: saklar kanal dan hasil pengiriman 24 jam terakhir. --}}
<x-filament::section heading="Integrasi notifikasi" icon="heroicon-o-bell-alert" data-integrations
    description="Kanal Email dan WhatsApp beserta hasil pengiriman 24 jam terakhir.">
    <x-slot name="headerEnd">
        <x-filament::link :href="\App\Filament\Pages\System\NotificationIntegrations::getUrl()" size="sm" icon="heroicon-m-cog-6-tooth">Atur integrasi</x-filament::link>
    </x-slot>

    <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(15rem,1fr))">
        @foreach ($integrations as $key => $channel)
            <div class="rounded-lg ring-1 ring-gray-950/5 dark:ring-white/10" style="padding:.75rem 1rem" data-integration="{{ $key }}">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:.5rem">
                    <span class="text-sm font-semibold text-gray-950 dark:text-white">{{ $channel['label'] }}</span>
                    <x-filament::badge :color="$channel['enabled'] ? ($channel['failed'] > 0 ? 'warning' : 'success') : 'gray'" size="sm">
                        {{ $channel['enabled'] ? ($channel['failed'] > 0 ? 'Aktif, ada kegagalan' : 'Aktif') : 'Nonaktif' }}
                    </x-filament::badge>
                </div>
                <p class="text-sm text-gray-700 dark:text-gray-200" style="margin-top:.5rem">
                    {{ $channel['sent'] }} terkirim · {{ $channel['failed'] }} gagal (24 jam)
                </p>
                @if ($channel['last_failure'])
                    <p class="text-xs text-gray-500 dark:text-gray-400" style="margin-top:.25rem">
                        Gagal terakhir {{ $channel['last_failure']['at']->diffForHumans() }}@if ($channel['last_failure']['error']): {{ $channel['last_failure']['error'] }}@endif
                    </p>
                @endif
            </div>
        @endforeach
    </div>
</x-filament::section>
