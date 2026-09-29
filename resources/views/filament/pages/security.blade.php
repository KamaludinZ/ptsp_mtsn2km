@php $grid = 'display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit,minmax(13rem,1fr))'; @endphp

<x-filament-panels::page>
    @if ($isDown)
        <x-filament::section>
            <p class="font-bold text-danger-600">Mode pemeliharaan sedang aktif. Pengunjung melihat halaman pemeliharaan.</p>
        </x-filament::section>
    @endif

    <div style="{{ $grid }}">
        <x-filament::section>
            <p class="text-sm text-gray-500 dark:text-gray-400">Skor keamanan</p>
            <p class="text-3xl font-bold text-primary-600">{{ $metrics['security_score'] }}%</p>
        </x-filament::section>
        <x-filament::section>
            <p class="text-sm text-gray-500 dark:text-gray-400">IP diblokir</p>
            <p class="text-3xl font-bold">{{ count($blocked) }}</p>
        </x-filament::section>
        <x-filament::section>
            <p class="text-sm text-gray-500 dark:text-gray-400">Aktivitas mencurigakan hari ini</p>
            <p class="text-3xl font-bold">{{ $metrics['suspicious_activities_today'] }}</p>
        </x-filament::section>
        <x-filament::section>
            <p class="text-sm text-gray-500 dark:text-gray-400">Pemindaian terakhir</p>
            <p class="text-2xl font-bold">{{ $metrics['last_security_scan'] ? \Illuminate\Support\Carbon::parse($metrics['last_security_scan'])->diffForHumans() : 'Belum pernah' }}</p>
        </x-filament::section>
    </div>

    <x-filament::section heading="Perlindungan dasar" icon="heroicon-o-shield-check">
        <div style="{{ $grid }}">
            @foreach ($metrics['checks'] as $name => $check)
                <div>
                    <x-filament::badge :color="$statusColor($check['status'])">{{ strtoupper($check['status']) }}</x-filament::badge>
                    <p class="font-bold">{{ $name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $check['message'] }}</p>
                </div>
            @endforeach
        </div>
    </x-filament::section>

    <x-filament::section heading="Hasil pemindaian" icon="heroicon-o-magnifying-glass-circle" collapsible>
        @if (! $scan)
            <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada hasil. Jalankan pemindaian dari tombol di atas.</p>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $scan['timestamp'] }} · skor {{ $scan['overall_score'] }}%</p>
            <table class="w-full text-start text-sm">
                <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                    @foreach ($scan['checks'] as $name => $check)
                        <tr>
                            <td class="px-3 py-2"><x-filament::badge :color="$statusColor($check['status'])">{{ strtoupper($check['status']) }}</x-filament::badge></td>
                            <td class="px-3 py-2 font-bold">{{ $name }}</td>
                            <td class="px-3 py-2">
                                {{ $check['message'] }}
                                @foreach ($check['issues'] ?? [] as $issue)
                                    <br><span class="text-gray-500 dark:text-gray-400">• {{ $issue }}</span>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-filament::section>

    <x-filament::section heading="Daftar IP diblokir" icon="heroicon-o-no-symbol">
        @if (empty($blocked))
            <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada IP yang diblokir.</p>
        @else
            <table class="w-full text-start text-sm">
                <thead>
                    <tr>
                        <th class="text-start px-3 py-2">IP</th>
                        <th class="text-start px-3 py-2">Alasan</th>
                        <th class="text-start px-3 py-2">Diblokir</th>
                        <th class="text-start px-3 py-2">Berakhir</th>
                        <th class="px-3 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                    @foreach ($blocked as $ip => $data)
                        <tr wire:key="ip-{{ $ip }}">
                            <td class="px-3 py-2 font-bold">{{ $ip }}</td>
                            <td class="px-3 py-2">{{ $data['reason'] ?? '–' }}</td>
                            <td class="px-3 py-2">{{ $data['blocked_at'] ?? '–' }} @if (! empty($data['blocked_by'])) · {{ $data['blocked_by'] }} @endif</td>
                            <td class="px-3 py-2">{{ $data['expires_at'] ?? 'Permanen' }}</td>
                            <td class="text-end px-3 py-2">
                                <x-filament::button size="sm" color="gray" wire:click="unblock(@js($ip))" wire:confirm="Buka blokir {{ $ip }}?">
                                    Buka blokir
                                </x-filament::button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-filament::section>
</x-filament-panels::page>
