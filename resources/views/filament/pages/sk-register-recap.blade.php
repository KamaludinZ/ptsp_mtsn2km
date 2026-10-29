@php
    $r = $this->recap;
    $card = 'rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10';
    $th = 'padding: 0.5rem 0.75rem; font-weight: 500;';
    $td = 'padding: 0.5rem 0.75rem;';
    $num = fn (int $n) => $n === 0 ? '–' : number_format($n, 0, ',', '.');
    $due = \App\Support\SkRegisterRecap::SCAN_DUE_DAYS;
    $waitingNote = 'Unggah scan lewat "Ubah" di Rincian SK setelah SK ditandatangani. '
        . ($r['scan_terlambat'] ? $r['scan_terlambat'] . " SK sudah lewat {$due} hari." : "Belum ada yang lewat {$due} hari.");
@endphp

{{-- Rekap Register SK. Inline styles: the panel uses Filament's prebuilt CSS. --}}
<x-filament-panels::page>
    <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 0.75rem;">
        <div>
            <label for="sk-recap-year" class="text-sm font-medium text-gray-950 dark:text-white">Tahun register</label>
            <x-filament::input.wrapper style="min-width: 7rem; margin-top: 0.25rem;">
                <x-filament::input.select id="sk-recap-year" wire:model.live="year">
                    @foreach ($this->years() as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>
        <x-filament::link :href="$this->registerUrl()" icon="heroicon-m-arrow-top-right-on-square" size="sm">
            Buka register tahun {{ $r['tahun'] }}
        </x-filament::link>
    </div>

    <div wire:loading.delay.class="opacity-50" style="display: flex; flex-direction: column; gap: 1.5rem;">
        @if ($r['surat_keluar_sk'])
            {{-- Data quality: SK numbered in the buku surat keluar --}}
            <div role="status" data-sk-recap="surat-keluar-sk" class="{{ $card }}" style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem 1rem; padding: 0.75rem 1.25rem;">
                <x-filament::icon icon="heroicon-m-exclamation-triangle" class="h-5 w-5 text-warning-600 dark:text-warning-400" />
                <p class="text-sm text-gray-700 dark:text-gray-200" style="flex: 1; min-width: 14rem;">
                    {{ $r['surat_keluar_sk'] }} surat di buku surat keluar tahun {{ $r['tahun'] }} berjenis Surat Keputusan. SK bernomor sendiri di register ini; periksa apakah surat itu perlu dicatat ulang sebagai SK.
                </p>
                @if (\App\Filament\Resources\SuratKeluarResource::canViewAny())
                    <x-filament::link :href="\App\Filament\Resources\SuratKeluarResource::getUrl('index', ['tableFilters' => ['tahun' => ['value' => $r['tahun']], 'tercatat_sebagai_sk' => ['isActive' => true]]])" size="sm">
                        Lihat di buku surat keluar
                    </x-filament::link>
                @endif
            </div>
        @endif

        @if ($r['terbit'] === 0)
            <section class="{{ $card }}" data-sk-recap-empty style="padding: 2.5rem 1.25rem; text-align: center;">
                <x-filament::icon icon="heroicon-o-document-magnifying-glass" class="h-6 w-6 text-gray-500 dark:text-gray-400" style="margin: 0 auto;" />
                <h2 class="text-base font-semibold text-gray-950 dark:text-white" style="margin-top: 0.75rem;">Belum ada nomor SK tahun {{ $r['tahun'] }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400" style="max-width: 32rem; margin: 0.25rem auto 0;">
                    Rekap terisi otomatis setelah petugas meminta nomor SK bertanggal tahun {{ $r['tahun'] }}.
                    @if ($r['dihapus'])
                        {{ $r['dihapus'] }} SK tahun ini sudah dihapus dari register.
                    @endif
                </p>
            </section>
        @else
            {{-- Angka utama --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(11rem, 1fr)); gap: 1rem;">
                <div class="{{ $card }}" data-sk-recap="terbit" style="padding: 1rem 1.25rem;">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Nomor SK terbit</p>
                    <p class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">{{ $r['terbit'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Terakhir: {{ $r['nomor_terakhir'] }}</p>
                </div>
                <div class="{{ $card }}" data-sk-recap="arsip" style="padding: 1rem 1.25rem;">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Arsip scan lengkap</p>
                    <p class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">{{ $r['persen_arsip'] }}%</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $r['terarsip'] }} dari {{ $r['terbit'] }} SK terarsip</p>
                </div>
                <div class="{{ $card }}" data-sk-recap="menunggu" style="padding: 1rem 1.25rem;">
                    <p class="text-sm text-gray-500 dark:text-gray-400" style="display: flex; align-items: center; gap: 0.375rem;">
                        @if ($r['menunggu_scan'])
                            <x-filament::icon icon="heroicon-m-arrow-up-tray" class="h-4 w-4 text-warning-600 dark:text-warning-400" />
                        @else
                            <x-filament::icon icon="heroicon-m-check-circle" class="h-4 w-4 text-success-600 dark:text-success-400" />
                        @endif
                        Menunggu scan
                    </p>
                    <p class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">{{ $r['menunggu_scan'] }}</p>
                    @if ($r['menunggu_scan'])
                        <x-filament::link :href="$this->registerUrl(['status' => ['value' => 'menunggu_scan']])" size="xs">Lihat SK yang menunggu scan</x-filament::link>
                    @else
                        <p class="text-xs text-gray-500 dark:text-gray-400">Semua SK sudah terarsip</p>
                    @endif
                </div>
                <div class="{{ $card }}" data-sk-recap="rata-hari" style="padding: 1rem 1.25rem;">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Rata-rata sampai terarsip</p>
                    <p class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">
                        {{ $r['rata_hari_arsip'] === null ? '–' : str_replace('.', ',', (string) $r['rata_hari_arsip']) . ' hari' }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Dari nomor diambil sampai scan diunggah</p>
                </div>
                <div class="{{ $card }}" data-sk-recap="dihapus" style="padding: 1rem 1.25rem;">
                    <p class="text-sm text-gray-500 dark:text-gray-400">SK dihapus</p>
                    <p class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">{{ $r['dihapus'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Nomornya tidak diberikan lagi</p>
                </div>
            </div>

            @if ($r['menunggu_terlama'])
                {{-- Arsip scan SK: what is still to upload, oldest first --}}
                <x-filament::section heading="Menunggu scan paling lama" icon="heroicon-o-arrow-up-tray" compact :description="$waitingNote">
                    <ul role="list" style="display: grid; gap: 0.5rem;">
                        @foreach ($r['menunggu_terlama'] as $row)
                            <li data-sk-recap-waiting="{{ $row['nomor_sk'] }}" class="rounded-lg ring-1 ring-gray-950/5 dark:ring-white/10"
                                style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.25rem 0.75rem; padding: 0.5rem 0.75rem;">
                                <span style="min-width: 0;">
                                    <x-filament::link :href="\App\Filament\Resources\SuratKeputusanResource::getUrl('view', ['record' => $row['id']])" size="sm" weight="semibold">{{ $row['nomor_sk'] }}</x-filament::link>
                                    <span class="text-sm text-gray-700 dark:text-gray-200"> · Tentang {{ $row['perihal'] }}</span>
                                </span>
                                @if ($row['hari'] >= $due)
                                    <x-filament::badge color="warning" icon="heroicon-m-clock" size="sm">Lewat {{ $due }} hari · {{ $row['hari'] }} hari</x-filament::badge>
                                @else
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $row['hari'] ? $row['hari'] . ' hari sejak nomor diambil' : 'Nomor diambil hari ini' }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </x-filament::section>
            @endif

            {{-- Per bulan --}}
            <x-filament::section heading="Per bulan" description="Menurut tanggal surat. Klik nama bulan untuk melihat SK-nya di register." icon="heroicon-o-calendar-days" compact>
                <div style="overflow-x: auto;">
                    <table class="w-full text-sm" style="border-collapse: collapse; min-width: 24rem;">
                        <thead>
                            <tr class="text-left text-gray-500 dark:text-gray-400">
                                <th scope="col" style="{{ $th }} padding-left: 0;">Bulan</th>
                                <th scope="col" style="{{ $th }} text-align: right;">Terbit</th>
                                <th scope="col" style="{{ $th }} text-align: right;">Terarsip</th>
                                <th scope="col" style="{{ $th }} text-align: right;">Menunggu scan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($r['per_bulan'] as $row)
                                <tr class="border-t border-gray-200 dark:border-white/10" data-sk-recap-month="{{ $row['bulan'] }}">
                                    <th scope="row" class="font-medium text-gray-950 dark:text-white" style="{{ $td }} padding-left: 0; text-align: left;">
                                        @if ($row['terbit'])
                                            <x-filament::link :href="$this->monthUrl($row['bulan'])" size="sm">{{ $row['nama'] }}</x-filament::link>
                                        @else
                                            {{ $row['nama'] }}
                                        @endif
                                    </th>
                                    <td class="tabular-nums text-gray-700 dark:text-gray-200" style="{{ $td }} text-align: right;">{{ $num($row['terbit']) }}</td>
                                    <td class="tabular-nums text-gray-700 dark:text-gray-200" style="{{ $td }} text-align: right;">{{ $num($row['terarsip']) }}</td>
                                    <td class="tabular-nums text-gray-700 dark:text-gray-200" style="{{ $td }} text-align: right;">{{ $num($row['terbit'] - $row['terarsip']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(20rem, 1fr)); gap: 1.5rem; align-items: start;">
                {{-- Per jenis SK --}}
                <x-filament::section heading="Per jenis SK" description="Jenis aktif yang belum dipakai ikut tampil; jenis nonaktif tampil bila masih dipakai tahun ini." icon="heroicon-o-tag" compact>
                    @if (\App\Filament\Resources\PersuratanMasterResource::canViewAny())
                        <x-slot name="headerEnd">
                            <x-filament::link :href="\App\Filament\Resources\PersuratanMasterResource::getUrl('index', ['activeTab' => \App\Filament\Resources\PersuratanMasterResource\Pages\ListPersuratanMasters::JENIS_SK_TAB])"
                                icon="heroicon-m-cog-6-tooth" size="sm">Kelola jenis SK</x-filament::link>
                        </x-slot>
                    @endif
                    <table class="w-full text-sm" style="border-collapse: collapse;">
                        <thead>
                            <tr class="text-left text-gray-500 dark:text-gray-400">
                                <th scope="col" style="{{ $th }} padding-left: 0;">Jenis SK</th>
                                <th scope="col" style="{{ $th }} text-align: right;">Terbit</th>
                                <th scope="col" style="{{ $th }} text-align: right; padding-right: 0;">Terarsip</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($r['per_jenis'] as $row)
                                <tr class="border-t border-gray-200 dark:border-white/10" data-sk-recap-jenis="{{ $row['jenis'] }}" data-aktif="{{ $row['aktif'] ? 'ya' : 'tidak' }}">
                                    <th scope="row" style="{{ $td }} padding-left: 0; text-align: left; font-weight: 400;">
                                        @if ($row['terbit'])
                                            <x-filament::link :href="$this->registerUrl(['jenis_sk' => ['values' => [$row['jenis']]]])" color="gray" size="sm">{{ $row['jenis'] }}</x-filament::link>
                                        @else
                                            <span class="text-gray-700 dark:text-gray-200">{{ $row['jenis'] }}</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400" style="display: block;">Belum dipakai tahun ini</span>
                                        @endif
                                        @unless ($row['aktif'])
                                            <x-filament::badge color="gray" icon="heroicon-m-eye-slash" size="sm" style="margin-top: 0.25rem; width: fit-content;">Nonaktif</x-filament::badge>
                                        @endunless
                                    </th>
                                    <td class="tabular-nums text-gray-700 dark:text-gray-200" style="{{ $td }} text-align: right;">{{ $num($row['terbit']) }}</td>
                                    <td class="tabular-nums text-gray-700 dark:text-gray-200" style="{{ $td }} text-align: right; padding-right: 0;">{{ $num($row['terarsip']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-filament::section>

                {{-- Per petugas pencatat --}}
                <x-filament::section heading="Per petugas pencatat" icon="heroicon-o-user-group" compact>
                    <table class="w-full text-sm" style="border-collapse: collapse;">
                        <thead>
                            <tr class="text-left text-gray-500 dark:text-gray-400">
                                <th scope="col" style="{{ $th }} padding-left: 0;">Petugas</th>
                                <th scope="col" style="{{ $th }} text-align: right;">Terbit</th>
                                <th scope="col" style="{{ $th }} text-align: right; padding-right: 0;">Terarsip</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($r['per_petugas'] as $row)
                                <tr class="border-t border-gray-200 dark:border-white/10" data-sk-recap-petugas="{{ $row['petugas'] }}">
                                    <th scope="row" style="{{ $td }} padding-left: 0; text-align: left;">
                                        <span class="font-medium text-gray-950 dark:text-white">{{ $row['petugas'] }}</span>
                                        @if ($row['peran'])
                                            <span class="text-xs text-gray-500 dark:text-gray-400" style="display: block; font-weight: 400;">{{ $row['peran'] }}</span>
                                        @endif
                                    </th>
                                    <td class="tabular-nums text-gray-700 dark:text-gray-200" style="{{ $td }} text-align: right;">{{ $row['terbit'] }}</td>
                                    <td class="tabular-nums text-gray-700 dark:text-gray-200" style="{{ $td }} text-align: right; padding-right: 0;">{{ $row['terarsip'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-filament::section>
            </div>
        @endif
    </div>
</x-filament-panels::page>
