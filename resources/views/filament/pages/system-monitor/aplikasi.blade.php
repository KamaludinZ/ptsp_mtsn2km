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
