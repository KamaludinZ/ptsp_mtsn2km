@php
    use App\Services\SystemMonitorService as M;

    $grid = 'display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(13rem,1fr))';
    $percentColor = fn (?float $p) => M::level($p);
    $cards = [
        ['Status aplikasi', $app['up'] ? 'Berjalan' : ($app['maintenance'] ? 'Mode perawatan' : 'Bermasalah'), 'Versi ' . $app['version'], $app['up'] ? 'success' : 'danger', 'aplikasi'],
        ['Database', $app['database']['ok'] ? 'Terhubung' : 'Gagal', $app['database']['ok'] ? $app['database']['latency_ms'] . ' ms · ' . $app['database']['driver'] : $app['database']['error'], $app['database']['ok'] ? 'success' : 'danger', 'aplikasi'],
        ['Scheduler', $app['scheduler']['ok'] ? 'Aktif' : 'Tidak terdeteksi', $app['scheduler']['last_run'] ? 'Terakhir ' . \Illuminate\Support\Carbon::parse($app['scheduler']['last_run'])->diffForHumans() : 'Belum pernah berjalan', $app['scheduler']['ok'] ? 'success' : 'warning', 'aplikasi'],
        ['Beban CPU', $server['load_percent'] !== null ? $server['load_percent'] . '%' : '–', $server['load'] ? 'Load ' . implode(' / ', $server['load']) . ' · ' . $server['cpu_cores'] . ' inti' : 'Tidak tersedia', $percentColor($server['load_percent']), 'server'],
        ['Memori', $server['memory']['used_percent'] !== null ? $server['memory']['used_percent'] . '%' : '–', M::bytes($server['memory']['available']) . ' tersedia dari ' . M::bytes($server['memory']['total']), $percentColor($server['memory']['used_percent']), 'server'],
        ['Disk', $server['disk']['used_percent'] !== null ? $server['disk']['used_percent'] . '%' : '–', M::bytes($server['disk']['free']) . ' kosong dari ' . M::bytes($server['disk']['total']), $percentColor($server['disk']['used_percent']), 'server'],
        ['Skor keamanan', ($security['security_score'] ?? '–') . '%', $security['failed_logins_today'] . ' login gagal hari ini · ' . $security['blocked_ips_count'] . ' IP diblokir', ($security['security_score'] ?? 0) >= 80 ? 'success' : 'warning', 'keamanan'],
        ['Uptime server', M::duration($server['uptime_seconds']), $server['container'] ? 'Berjalan di container ' . $server['container']['id'] : ($server['hostname'] ?? ''), 'gray', 'server'],
    ];
@endphp

<div style="{{ $grid }}">
    @foreach ($cards as [$title, $value, $detail, $color, $target])
        <button type="button" wire:click="$set('tab', '{{ $target }}')" class="text-start">
            <x-filament::section compact>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $title }}</p>
                <p class="text-2xl font-bold" style="color: rgb(var(--{{ $color === 'gray' ? 'gray-500' : $color . '-600' }}));">{{ $value }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $detail }}</p>
            </x-filament::section>
        </button>
    @endforeach
</div>
