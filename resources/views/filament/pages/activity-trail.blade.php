<x-filament-panels::page>
    <x-filament::section compact>
        {{ $this->form }}
        @if ($filtering || $type !== '')
            <div style="margin-top:.75rem">
                <x-filament::link tag="button" wire:click="resetFilters" icon="heroicon-m-x-mark" color="gray" size="sm">
                    Hapus saringan
                </x-filament::link>
            </div>
        @endif
    </x-filament::section>

    {{-- Rekap per pengguna dan peran aktif --}}
    <x-filament::section heading="Rekap per pengguna & peran" icon="heroicon-o-table-cells" collapsible compact>
        <div style="overflow-x:auto">
            <table class="w-full text-sm" style="border-collapse:collapse;min-width:36rem">
                <thead>
                    <tr class="text-left text-gray-500 dark:text-gray-400">
                        <th scope="col" style="padding:.375rem .75rem .375rem 0;font-weight:500">Pengguna</th>
                        <th scope="col" style="padding:.375rem .75rem;font-weight:500">Peran aktif</th>
                        @foreach ($types as $meta)
                            <th scope="col" style="padding:.375rem .75rem;font-weight:500;text-align:right">{{ $meta['label'] }}</th>
                        @endforeach
                        <th scope="col" style="padding:.375rem .75rem;font-weight:500;text-align:right">Total</th>
                        <th scope="col" style="padding:.375rem 0 .375rem .75rem;font-weight:500">Terakhir</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recap as $row)
                        <tr class="border-t border-gray-200 dark:border-white/10" data-recap-row="{{ $row['user'] }}|{{ $row['role'] }}">
                            <td class="font-semibold text-gray-950 dark:text-white" style="padding:.375rem .75rem .375rem 0">{{ $row['user'] }}</td>
                            <td class="text-gray-700 dark:text-gray-200" style="padding:.375rem .75rem">{{ $row['role'] }}</td>
                            @foreach ($types as $key => $meta)
                                <td class="tabular-nums text-gray-700 dark:text-gray-200" style="padding:.375rem .75rem;text-align:right">{{ $row['by_type'][$key] ?? '–' }}</td>
                            @endforeach
                            <td class="font-semibold tabular-nums text-gray-950 dark:text-white" style="padding:.375rem .75rem;text-align:right">{{ $row['total'] }}</td>
                            <td class="text-gray-500 dark:text-gray-400" style="padding:.375rem 0 .375rem .75rem;white-space:nowrap">{{ $row['last']->translatedFormat('j M, H.i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($types) + 4 }}" class="text-gray-500 dark:text-gray-400" style="padding:.5rem 0">Tidak ada aktivitas yang cocok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>

    {{-- Jenis: semua / perpindahan peran / ganti akun / aksi tiket --}}
    <x-filament::tabs label="Jenis aktivitas">
        <x-filament::tabs.item :active="$type === ''" wire:click="setType('')">
            Semua
            <x-slot name="badge">{{ $counts->sum() }}</x-slot>
        </x-filament::tabs.item>
        @foreach ($types as $key => $meta)
            <x-filament::tabs.item :active="$type === $key" :icon="$meta['icon']" wire:click="setType('{{ $key }}')">
                {{ $meta['label'] }}
                <x-slot name="badge">{{ $counts[$key] ?? 0 }}</x-slot>
            </x-filament::tabs.item>
        @endforeach
    </x-filament::tabs>

    @if ($limited)
        <p class="text-sm text-gray-500 dark:text-gray-400">Menampilkan {{ \App\Filament\Pages\System\ActivityTrail::TIMELINE_LIMIT }} catatan terbaru; persempit dengan saringan untuk melihat yang lebih lama.</p>
    @endif

    @forelse ($days as $day => $entries)
        <x-filament::section :heading="\Illuminate\Support\Carbon::parse($day)->translatedFormat('l, j F Y')" compact>
            <ol role="list" style="display:grid;gap:.75rem">
                @foreach ($entries as $entry)
                    <li data-trail-type="{{ $entry['type'] }}"
                        style="display:grid;grid-template-columns:3.5rem 1fr;gap:.75rem;align-items:start">
                        <time class="text-sm font-medium text-gray-500 dark:text-gray-400" datetime="{{ $entry['at']->toIso8601String() }}">
                            {{ $entry['at']->format('H.i') }}
                        </time>
                        <div style="min-width:0">
                            <div style="display:flex;flex-wrap:wrap;align-items:center;gap:.375rem .5rem">
                                <x-filament::badge :color="$types[$entry['type']]['color']" :icon="$types[$entry['type']]['icon']" size="sm">
                                    {{ $types[$entry['type']]['label'] }}
                                </x-filament::badge>
                                <span class="text-sm font-semibold text-gray-950 dark:text-white">{{ $entry['user'] }}</span>
                                @if ($entry['acting_role'])
                                    <span class="text-xs text-gray-500 dark:text-gray-400">sebagai {{ $entry['acting_role'] }}</span>
                                @endif
                            </div>

                            <p class="text-sm text-gray-700 dark:text-gray-200" style="margin-top:.25rem">
                                {{ $entry['description'] }}
                                @if ($entry['from_role'] || $entry['to_role'])
                                    <span class="text-gray-500 dark:text-gray-400">· {{ $entry['from_role'] ?? 'belum ada' }} → {{ $entry['to_role'] }}</span>
                                @endif
                            </p>

                            <div class="text-xs text-gray-500 dark:text-gray-400" style="display:flex;flex-wrap:wrap;gap:.25rem .75rem;margin-top:.25rem">
                                @if ($entry['ticket'])
                                    <span>Tiket {{ $entry['ticket'] }}@if ($entry['service']) · {{ $entry['service'] }}@endif</span>
                                @endif
                                @if ($entry['impersonated_by'])
                                    <span class="text-warning-600 dark:text-warning-400">dilakukan {{ $entry['impersonated_by'] }} lewat ganti akun</span>
                                @endif
                                @if ($entry['ip'])
                                    <span>IP {{ $entry['ip'] }}</span>
                                @endif
                                <x-filament::link tag="button" size="xs" wire:click="showDetail({{ $entry['id'] }})">Lihat detail</x-filament::link>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ol>
        </x-filament::section>
    @empty
        <x-filament::section>
            <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada aktivitas untuk jenis ini.</p>
        </x-filament::section>
    @endforelse

    {{-- Detail satu catatan --}}
    <x-filament::modal id="trail-detail" slide-over width="md">
        @if ($detail)
            <x-slot name="heading">{{ $types[$detail['type']]['label'] }}</x-slot>
            <x-slot name="description">{{ $detail['at']->translatedFormat('l, j F Y · H.i') }}</x-slot>

            <dl data-trail-detail="{{ $detail['id'] }}" class="text-sm" style="display:grid;grid-template-columns:auto 1fr;gap:.5rem 1rem">
                <dt class="text-gray-500 dark:text-gray-400">Pengguna</dt>
                <dd class="font-semibold text-gray-950 dark:text-white">{{ $detail['user'] }}</dd>

                @if ($detail['impersonated_by'])
                    <dt class="text-gray-500 dark:text-gray-400">Pelaku sebenarnya</dt>
                    <dd class="text-warning-600 dark:text-warning-400">{{ $detail['impersonated_by'] }} (ganti akun)</dd>
                @endif

                <dt class="text-gray-500 dark:text-gray-400">Peran aktif</dt>
                <dd>{{ $detail['acting_role'] ?? 'Pemohon' }}</dd>

                @if ($detail['from_role'] || $detail['to_role'])
                    <dt class="text-gray-500 dark:text-gray-400">Perpindahan</dt>
                    <dd>{{ $detail['from_role'] ?? 'belum ada' }} → {{ $detail['to_role'] }}</dd>
                @endif

                <dt class="text-gray-500 dark:text-gray-400">Aksi</dt>
                <dd>{{ $detail['description'] }}</dd>

                @if ($detail['ticket'])
                    <dt class="text-gray-500 dark:text-gray-400">Tiket</dt>
                    <dd>{{ $detail['ticket'] }}@if ($detail['service']) · {{ $detail['service'] }}@endif</dd>
                @endif

                @foreach ($detail['details'] as $label => $value)
                    <dt class="text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                    <dd>{{ $value }}</dd>
                @endforeach

                <dt class="text-gray-500 dark:text-gray-400">Alamat IP</dt>
                <dd>{{ $detail['ip'] ?? '–' }}</dd>
            </dl>

            <p class="text-xs text-gray-500 dark:text-gray-400" style="margin-top:1rem">Catatan ini tidak dapat diubah atau dihapus.</p>
        @endif
    </x-filament::modal>
</x-filament-panels::page>
