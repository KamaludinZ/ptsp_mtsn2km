@php
    use App\Models\AppUpdate;

    $release = $update['release'];
@endphp

<div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(16rem,1fr))">
    <x-filament::section compact>
        <p class="text-sm text-gray-500 dark:text-gray-400">Versi berjalan</p>
        <p class="text-2xl font-bold text-gray-950 dark:text-white">{{ $update['current'] }}</p>
    </x-filament::section>
    <x-filament::section compact>
        <p class="text-sm text-gray-500 dark:text-gray-400">Rilis terbaru di GitHub</p>
        @isset($release['error'])
            <p class="text-sm text-gray-950 dark:text-white">{{ $release['error'] }}</p>
        @else
            <p class="text-2xl font-bold text-gray-950 dark:text-white">{{ $release['tag'] }}</p>
            <x-filament::badge :color="$update['newer'] ? 'warning' : 'success'">
                {{ $update['newer'] === null ? 'Tidak dapat dibandingkan' : ($update['newer'] ? 'Pembaruan tersedia' : 'Sudah terbaru') }}
            </x-filament::badge>
        @endisset
        <div style="margin-top:.5rem">
            <x-filament::button size="sm" color="gray" icon="heroicon-m-arrow-path" wire:click="checkForUpdate" wire:loading.attr="disabled">
                Cek pembaruan
            </x-filament::button>
        </div>
    </x-filament::section>
</div>

@if (! isset($release['error']) && $release['notes'])
    <x-filament::section :heading="'Catatan rilis ' . $release['name']" icon="heroicon-o-megaphone" collapsible collapsed>
        <p class="text-sm text-gray-700 dark:text-gray-300" style="white-space:pre-line">{{ $release['notes'] }}</p>
        <x-filament::link :href="$release['url']" target="_blank" size="sm" icon="heroicon-m-arrow-top-right-on-square">Lihat di GitHub</x-filament::link>
    </x-filament::section>
@endif

<x-filament::section heading="Riwayat pembaruan" icon="heroicon-o-clock">
    @if ($update['history']->isEmpty())
        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada pembaruan yang tercatat.</p>
    @else
        <div style="overflow-x:auto">
            <table class="w-full text-sm" style="border-collapse:collapse;min-width:36rem">
                <thead><tr class="text-xs text-gray-500 dark:text-gray-400" style="text-align:left">
                    <th style="padding:.375rem .75rem .375rem 0">Waktu</th>
                    <th style="padding:.375rem .75rem">Sumber</th>
                    <th style="padding:.375rem .75rem">Versi</th>
                    <th style="padding:.375rem .75rem">Status</th>
                    <th style="padding:.375rem 0 .375rem .75rem">Oleh</th>
                </tr></thead>
                <tbody>
                    @foreach ($update['history'] as $item)
                        <tr class="border-t border-gray-200 dark:border-white/10" style="vertical-align:top">
                            <td style="padding:.375rem .75rem .375rem 0;white-space:nowrap">{{ $item->created_at->translatedFormat('j M Y H:i') }}</td>
                            <td style="padding:.375rem .75rem">{{ AppUpdate::SOURCES[$item->source] ?? $item->source }}{{ $item->package_name ? ' · ' . $item->package_name : '' }}</td>
                            <td style="padding:.375rem .75rem">{{ $item->from_version ?? '–' }} → {{ $item->to_version ?? '–' }}</td>
                            <td style="padding:.375rem .75rem">
                                <x-filament::badge :color="$item->statusColor()" size="sm">{{ AppUpdate::STATUSES[$item->status] ?? $item->status }}</x-filament::badge>
                                @if ($item->notes)
                                    <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $item->notes }}</span>
                                @endif
                            </td>
                            <td style="padding:.375rem 0 .375rem .75rem">{{ $item->performer?->name ?? 'Sistem' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-filament::section>
