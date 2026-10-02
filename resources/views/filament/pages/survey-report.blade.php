@php
    $results = $report['results'];
    $label = $surveyType === 'skm' ? 'IKM' : 'IPAK';
    $grid = 'display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit,minmax(15rem,1fr))';
@endphp

<x-filament-panels::page>
    <x-filament::tabs>
        <x-filament::tabs.item :active="$surveyType === 'skm'" wire:click="$set('type', 'skm')" icon="heroicon-m-face-smile">
            SKM (Kepuasan Masyarakat)
        </x-filament::tabs.item>
        <x-filament::tabs.item :active="$surveyType === 'spak'" wire:click="$set('type', 'spak')" icon="heroicon-m-shield-check">
            SPAK (Persepsi Anti Korupsi)
        </x-filament::tabs.item>
    </x-filament::tabs>

    <style>
        @media print {
            .fi-sidebar, .fi-topbar, .fi-header-actions, .fi-tabs, [data-survey-filters] { display: none !important; }
            .fi-main-ctn, .fi-main { padding: 0 !important; margin: 0 !important; }
        }
    </style>

    <div data-survey-filters style="display:flex;flex-wrap:wrap;gap:.75rem;align-items:end">
        <label style="min-width:12rem">
            <span class="text-sm font-medium text-gray-950 dark:text-white">Periode</span>
            <x-filament::input.wrapper>
                <x-filament::input.select wire:model.live="period">
                    @foreach ($periods as $value => $name)
                        <option value="{{ $value }}">{{ $name }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </label>
        <label style="min-width:14rem">
            <span class="text-sm font-medium text-gray-950 dark:text-white">Edisi survei</span>
            <x-filament::input.wrapper>
                <x-filament::input.select wire:model.live="edition">
                    <option value="">Semua edisi</option>
                    @foreach ($editions as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </label>
    </div>

    <div style="{{ $grid }}">
        <x-filament::section>
            <p class="text-sm text-gray-500 dark:text-gray-400">Indeks {{ $label }} {{ $periodLabel }}</p>
            <p class="text-3xl font-bold text-primary-600">{{ $index !== null ? number_format($index, 2, ',', '.') : '–' }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $grade ?? 'Belum ada data survei' }}</p>
        </x-filament::section>
        <x-filament::section>
            <p class="text-sm text-gray-500 dark:text-gray-400">Rata-rata nilai unsur (skala 1–4)</p>
            <p class="text-3xl font-bold">{{ $results['average'] ? number_format($results['average'], 2, ',', '.') : '–' }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Persentase {{ number_format($results['percentage'] ?? 0, 2, ',', '.') }}%</p>
        </x-filament::section>
        <x-filament::section>
            <p class="text-sm text-gray-500 dark:text-gray-400">Responden {{ $periodLabel }}</p>
            <p class="text-3xl font-bold">{{ $results['total_respondents'] ?? 0 }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $report['responses_count'] ?? 0 }} jawaban tercatat</p>
        </x-filament::section>
    </div>

    <x-filament::section heading="Nilai per pertanyaan" icon="heroicon-o-list-bullet">
        @if (empty($results['scores']))
            <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada jawaban survei pada {{ $periodLabel }}.</p>
        @else
            <table class="w-full text-start text-sm">
                <thead>
                    <tr>
                        <th class="text-start px-3 py-2">Pertanyaan</th>
                        <th class="text-end px-3 py-2">Responden</th>
                        <th class="text-end px-3 py-2">Rata-rata</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                    @foreach ($results['scores'] as $score)
                        <tr>
                            <td class="px-3 py-2">{{ $score['question'] }}</td>
                            <td class="text-end px-3 py-2">{{ $score['total_responses'] }}</td>
                            <td class="text-end px-3 py-2 font-bold">{{ number_format($score['average_score'], 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-filament::section>

    <x-filament::section :heading="'Demografi responden ' . $periodLabel" icon="heroicon-o-user-group" collapsible>
        <div style="{{ $grid }}">
            @foreach ($demographicLabels as $key => $title)
                <div>
                    <p class="font-bold">{{ $title }}</p>
                    @forelse ($report['demographics'][$key] ?? [] as $name => $count)
                        <div style="display:flex;justify-content:space-between;gap:1rem" class="text-sm py-2">
                            <span>{{ $name }}</span>
                            <x-filament::badge>{{ $count }}</x-filament::badge>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada data.</p>
                    @endforelse
                </div>
            @endforeach
        </div>
    </x-filament::section>

    <x-filament::section heading="Arsip triwulan" icon="heroicon-o-archive-box" collapsible>
        @if ($archives->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada arsip triwulan.</p>
        @else
            <table class="w-full text-start text-sm">
                <thead>
                    <tr>
                        <th class="text-start px-3 py-2">Triwulan</th>
                        <th class="text-end px-3 py-2">Responden</th>
                        <th class="text-end px-3 py-2">Rata-rata</th>
                        <th class="text-end px-3 py-2">Persentase</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                    @foreach ($archives as $archive)
                        @php $values = $archive->calculated_values ?? []; @endphp
                        <tr>
                            <td class="px-3 py-2">{{ $archive->quarter }} {{ $archive->year }}</td>
                            <td class="text-end px-3 py-2">{{ $values['total_respondents'] ?? '–' }}</td>
                            <td class="text-end px-3 py-2">{{ isset($values['average']) ? number_format($values['average'], 2, ',', '.') : '–' }}</td>
                            <td class="text-end px-3 py-2">{{ isset($values['percentage']) ? number_format($values['percentage'], 2, ',', '.') . '%' : '–' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-filament::section>
</x-filament-panels::page>
