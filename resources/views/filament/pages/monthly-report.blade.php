@php
    use App\Support\ServiceMetrics;
    use App\Support\TicketLabels;

    $r = $this->report;
    $t = $r['tickets'];
    $maxDaily = max(1, collect($r['daily'])->max('total'));
    $card = 'rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10';
@endphp

{{-- Laporan bulanan. Inline styles: the panel uses Filament's prebuilt CSS. --}}
<x-filament-panels::page>
    <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 0.75rem;">
        <fieldset style="border: 0; margin: 0; padding: 0; min-width: 0;">
            <legend class="text-sm font-medium text-gray-950 dark:text-white">Periode laporan</legend>
            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; margin-top: 0.25rem;">
                <x-filament::icon-button icon="heroicon-m-chevron-left" color="gray" label="Bulan sebelumnya"
                    wire:click="previousMonth" :disabled="! $this->canGoPrevious()" />
                <x-filament::input.wrapper style="min-width: 9.5rem;">
                    <x-filament::input.select wire:model.live="monthNumber" aria-label="Bulan">
                        @foreach ($this->monthChoices() as $number => $choice)
                            <option value="{{ $number }}" @disabled($choice['disabled'])>{{ $choice['name'] }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
                <x-filament::input.wrapper style="min-width: 6.5rem;">
                    <x-filament::input.select wire:model.live="year" aria-label="Tahun">
                        @foreach ($this->yearChoices() as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
                <x-filament::icon-button icon="heroicon-m-chevron-right" color="gray" label="Bulan berikutnya"
                    wire:click="nextMonth" :disabled="! $this->canGoNext()" />
                @unless ($r['is_current'])
                    <x-filament::button size="sm" color="gray" wire:click="thisMonth">Bulan ini</x-filament::button>
                @endunless
            </div>
        </fieldset>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ $r['from']->day }} {{ \App\Support\MonthlyReport::MONTH_NAMES[$r['from']->month] }} – {{ $r['to']->day }} {{ \App\Support\MonthlyReport::MONTH_NAMES[$r['to']->month] }} {{ $r['to']->year }}
            @if ($r['is_current']) · <span class="font-medium text-warning-600 dark:text-warning-400">bulan berjalan</span> @endif
        </p>
    </div>

    <div wire:loading.delay.class="opacity-50" style="display: flex; flex-direction: column; gap: 1.5rem;">
        @if ($t['total'] === 0)
            {{-- Bulan nihil --}}
            <section class="{{ $card }}" aria-labelledby="empty-heading" style="padding: 2.5rem 1.25rem; text-align: center;">
                <div class="bg-gray-100 dark:bg-white/5" style="display: inline-flex; padding: 0.75rem; border-radius: 9999px;">
                    <x-filament::icon icon="heroicon-o-document-magnifying-glass" class="h-6 w-6 text-gray-500 dark:text-gray-400" />
                </div>
                <h2 id="empty-heading" class="text-base font-semibold text-gray-950 dark:text-white" style="margin-top: 0.75rem;">
                    {{ $r['is_current'] ? 'Belum ada permohonan bulan ini' : 'Tidak ada permohonan pada ' . $r['label'] }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400" style="max-width: 32rem; margin: 0.25rem auto 0;">
                    @if ($r['is_current'])
                        Laporan akan terisi otomatis saat permohonan online maupun dari loket masuk pada {{ $r['label'] }}.
                    @else
                        Tidak ada permohonan yang diajukan pada periode ini, sehingga rekap layanan, ketepatan waktu, dan grafik harian kosong.
                    @endif
                </p>
                @if ($r['nearest_with_data'])
                    <div style="margin-top: 1rem;">
                        <x-filament::button color="gray" icon="heroicon-m-arrow-left"
                            wire:click="openMonth('{{ $r['nearest_with_data']->format('Y-m') }}')">
                            Lihat {{ \App\Support\MonthlyReport::label($r['nearest_with_data']) }}
                        </x-filament::button>
                    </div>
                @endif
            </section>
        @else
            {{-- Ringkasan --}}
            <section aria-label="Ringkasan {{ $r['label'] }}"
                     style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr));">
                @foreach ([
                    ['Permohonan masuk', number_format($t['total'], 0, ',', '.'), $r['changes']['total'], $t['online'] . ' online · ' . $t['offline'] . ' loket'],
                    ['Selesai', number_format($t['completed'], 0, ',', '.'), $r['changes']['completed'], ($t['completion_rate'] ?? '–') . '% dari permohonan bulan ini'],
                    ['Tepat waktu', $t['on_time_rate'] !== null ? $t['on_time_rate'] . '%' : '–', $r['changes']['on_time_rate'], 'Selesai sebelum target standar layanan'],
                    ['Rata-rata penyelesaian', ServiceMetrics::days($t['avg_days']), $r['changes']['avg_days'], 'Dari diajukan sampai selesai'],
                ] as [$title, $value, $change, $hint])
                    <div class="{{ $card }}" style="padding: 1rem 1.25rem;">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $title }}</p>
                        <p class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white" style="margin-top: 0.25rem;">{{ $value }}</p>
                        @if ($change)
                            <p class="text-xs font-medium {{ $change['good'] === null ? 'text-gray-500 dark:text-gray-400' : ($change['good'] ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400') }}" style="margin-top: 0.25rem;">
                                {{ $change['trend'] === 'up' ? '▲' : ($change['trend'] === 'down' ? '▼' : '■') }} {{ $change['label'] }}
                            </p>
                        @endif
                        <p class="text-xs text-gray-500 dark:text-gray-400" style="margin-top: 0.125rem;">{{ $hint }}</p>
                    </div>
                @endforeach
            </section>

            <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(min(100%, 22rem), 1fr));">
                {{-- Permohonan per hari --}}
                <section class="{{ $card }}" style="padding: 1rem 1.25rem;" aria-labelledby="daily-heading">
                    <h2 id="daily-heading" class="text-base font-semibold text-gray-950 dark:text-white">Permohonan masuk per hari</h2>
                        <div role="img" aria-label="Grafik permohonan harian {{ $r['label'] }}, tertinggi {{ $maxDaily }} per hari"
                             style="display: flex; align-items: flex-end; gap: 2px; height: 9rem; margin-top: 1rem;">
                            @foreach ($r['daily'] as $day)
                                <div title="{{ \Illuminate\Support\Carbon::parse($day['date'])->translatedFormat('j M') }}: {{ $day['total'] }} permohonan"
                                     style="flex: 1; min-width: 3px; border-radius: 2px 2px 0 0; height: {{ max(2, round($day['total'] / $maxDaily * 100)) }}%; background: rgb(var({{ $day['total'] ? '--primary-500' : '--gray-200' }}));"></div>
                            @endforeach
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400" style="display: flex; justify-content: space-between; margin-top: 0.25rem;">
                            <span>1</span><span>{{ last($r['daily'])['day'] }}</span>
                        </div>
                </section>

                {{-- Per status --}}
                <section class="{{ $card }}" style="padding: 1rem 1.25rem;" aria-labelledby="status-heading">
                    <h2 id="status-heading" class="text-base font-semibold text-gray-950 dark:text-white">Status permohonan bulan ini</h2>
                    <ul style="list-style: none; margin: 0.75rem 0 0; padding: 0; display: flex; flex-direction: column; gap: 0.5rem;">
                        @foreach ($t['by_status'] as $status => $count)
                            @continue($count === 0 && in_array($status, ['approved', 'cancelled'], true))
                            <li style="display: grid; grid-template-columns: 7.5rem 1fr 2.5rem; align-items: center; gap: 0.5rem;">
                                <span class="text-sm text-gray-700 dark:text-gray-200">{{ TicketLabels::status($status) }}</span>
                                <span class="bg-gray-100 dark:bg-white/5" style="display: block; height: 0.5rem; border-radius: 9999px; overflow: hidden;">
                                    <span style="display: block; height: 100%; width: {{ $t['total'] ? round($count / $t['total'] * 100) : 0 }}%; background: rgb(var(--{{ TicketLabels::statusColor($status) === 'gray' ? 'gray-400' : TicketLabels::statusColor($status) . '-500' }}));"></span>
                                </span>
                                <span class="text-sm font-semibold text-gray-950 dark:text-white" style="text-align: right;">{{ $count }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="text-xs text-gray-500 dark:text-gray-400" style="margin-top: 0.75rem;">
                        Masih terbuka: {{ $t['open'] }} · melewati target: {{ $t['overdue'] }}
                    </p>
                </section>
            </div>

            {{-- Rekap jenis layanan --}}
            <section class="{{ $card }}" aria-labelledby="services-heading" style="overflow: hidden;">
                <div style="padding: 1rem 1.25rem 0.5rem;">
                    <h2 id="services-heading" class="text-base font-semibold text-gray-950 dark:text-white">Rekap jenis layanan</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Permohonan yang diajukan pada {{ $r['label'] }}, dikelompokkan per jenis layanan.</p>
                </div>
                    <div style="overflow-x: auto;">
                        <table class="text-sm" style="width: 100%; border-collapse: collapse; min-width: 46rem;">
                            <caption class="sr-only">Rekap jenis layanan {{ $r['label'] }}</caption>
                            <thead class="bg-gray-50 dark:bg-white/5">
                                <tr class="text-xs text-gray-600 dark:text-gray-300">
                                    @foreach (['Jenis layanan' => 'left', 'Jumlah' => 'right', 'Online' => 'right', 'Loket' => 'right', 'Selesai' => 'right', 'Ditolak/batal' => 'right', 'Dalam proses' => 'right', 'Tepat waktu' => 'right', 'Rata-rata' => 'right'] as $heading => $align)
                                        <th scope="col" style="padding: 0.5rem 0.75rem; text-align: {{ $align }}; font-weight: 600; white-space: nowrap;">{{ $heading }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($r['services']['rows'] as $row)
                                    <tr class="border-t border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-200">
                                        <th scope="row" class="font-medium text-gray-950 dark:text-white" style="padding: 0.5rem 0.75rem; text-align: left;">{{ $row['name'] }}</th>
                                        @include('filament.pages.partials.monthly-service-cells', ['row' => $row])
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-white/5">
                                <tr class="border-t-2 border-gray-300 dark:border-white/20 font-semibold text-gray-950 dark:text-white">
                                    <th scope="row" style="padding: 0.5rem 0.75rem; text-align: left;">Total</th>
                                    @include('filament.pages.partials.monthly-service-cells', ['row' => $r['services']['total']])
                                </tr>
                            </tfoot>
                        </table>
                    </div>
            </section>
        @endif

        <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(min(100%, 16rem), 1fr));">
            <section class="{{ $card }}" style="padding: 1rem 1.25rem;" aria-labelledby="ikm-heading">
                <h2 id="ikm-heading" class="text-base font-semibold text-gray-950 dark:text-white">Kepuasan masyarakat (IKM)</h2>
                <p class="text-3xl font-semibold text-gray-950 dark:text-white" style="margin-top: 0.5rem;">{{ $r['survey']['ikm'] !== null ? number_format($r['survey']['ikm'], 2, ',', '.') : '–' }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $r['survey']['ikm_grade'] ?? 'Belum ada survei' }} · {{ $r['survey']['respondents'] }} responden</p>
            </section>
            <section class="{{ $card }}" style="padding: 1rem 1.25rem;" aria-labelledby="dumas-heading">
                <h2 id="dumas-heading" class="text-base font-semibold text-gray-950 dark:text-white">Pengaduan & saran</h2>
                <p class="text-3xl font-semibold text-gray-950 dark:text-white" style="margin-top: 0.5rem;">{{ $r['complaints']['dumas']['total'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $r['complaints']['dumas']['resolved'] }} selesai ditindaklanjuti
                    @if ($r['complaints']['whistleblowing']['total']) · {{ $r['complaints']['whistleblowing']['total'] }} laporan whistleblowing @endif
                </p>
            </section>
            <section class="{{ $card }}" style="padding: 1rem 1.25rem;" aria-labelledby="visitor-heading">
                <h2 id="visitor-heading" class="text-base font-semibold text-gray-950 dark:text-white">Kunjungan buku tamu</h2>
                <p class="text-3xl font-semibold text-gray-950 dark:text-white" style="margin-top: 0.5rem;">{{ number_format($r['visitors'], 0, ',', '.') }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Tamu yang tercatat di loket</p>
            </section>
        </div>
    </div>
</x-filament-panels::page>
