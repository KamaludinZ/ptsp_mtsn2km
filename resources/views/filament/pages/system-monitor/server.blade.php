@php
    use App\Services\SystemMonitorService as M;

    $meters = [
        ['Beban CPU', $server['load_percent'], $server['load'] ? 'Load rata-rata 1/5/15 menit: ' . implode(' / ', $server['load']) . ' pada ' . $server['cpu_cores'] . ' inti' : 'Tidak tersedia di host ini'],
        ['Memori (RAM)', $server['memory']['used_percent'], M::bytes($server['memory']['available']) . ' tersedia dari ' . M::bytes($server['memory']['total'])],
        ['Disk', $server['disk']['used_percent'], M::bytes($server['disk']['free']) . ' kosong dari ' . M::bytes($server['disk']['total'])],
    ];
    $levelText = ['success' => 'Normal', 'warning' => 'Perlu perhatian', 'danger' => 'Kritis', 'gray' => 'Tidak tersedia'];
@endphp

<x-filament::section heading="Kondisi server" icon="heroicon-o-server"
    :description="'Peringatan mulai ' . M::WARNING_AT . '%, kritis mulai ' . M::CRITICAL_AT . '%.'">
    <div style="display:grid;gap:1.25rem">
        @foreach ($meters as [$label, $percent, $detail])
            @php($level = M::level($percent))
            <div>
                <div style="display:flex;justify-content:space-between;gap:1rem;align-items:baseline">
                    <span class="text-sm font-semibold text-gray-950 dark:text-white">{{ $label }}</span>
                    <x-filament::badge :color="$level">{{ $percent !== null ? $percent . '% · ' : '' }}{{ $levelText[$level] }}</x-filament::badge>
                </div>
                <div class="bg-gray-200 dark:bg-white/10" style="height:.5rem;border-radius:9999px;overflow:hidden;margin:.375rem 0"
                     role="meter" aria-label="{{ $label }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percent ?? 0 }}">
                    <div style="height:100%;width:{{ min(100, $percent ?? 0) }}%;background:rgb(var(--{{ $level === 'gray' ? 'gray-400' : $level . '-500' }}))"></div>
                </div>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $detail }}</span>
            </div>
        @endforeach
    </div>
</x-filament::section>

<x-filament::section heading="Host" icon="heroicon-o-computer-desktop">
    <dl style="display:grid;grid-template-columns:minmax(9rem,14rem) 1fr;gap:.5rem 1rem;margin:0" class="text-sm">
        <dt class="text-gray-500 dark:text-gray-400">Nama host</dt><dd style="margin:0">{{ $server['hostname'] ?? '–' }}</dd>
        <dt class="text-gray-500 dark:text-gray-400">Sistem operasi</dt><dd style="margin:0">{{ $server['os'] }}</dd>
        <dt class="text-gray-500 dark:text-gray-400">Uptime</dt><dd style="margin:0">{{ M::duration($server['uptime_seconds']) }}</dd>
        <dt class="text-gray-500 dark:text-gray-400">Container</dt>
        <dd style="margin:0">
            @if ($server['container'])
                Docker {{ $server['container']['id'] }}
                @if ($server['container']['memory_usage'])
                    · memori {{ M::bytes($server['container']['memory_usage']) }}{{ $server['container']['memory_limit'] ? ' dari batas ' . M::bytes($server['container']['memory_limit']) : ' (tanpa batas)' }}
                @endif
            @else
                Tidak berjalan di container
            @endif
        </dd>
    </dl>
</x-filament::section>
