{{-- Riwayat perubahan SK (SuratKeputusanService::history), newest first. --}}
@php($entries = $getState() ?? [])
@if (empty($entries))
    <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada catatan untuk SK ini.</p>
@else
    <ol role="list" style="display: grid; gap: 0.75rem;">
        @foreach ($entries as $entry)
            <li data-sk-history="{{ $entry['peristiwa'] }}" class="rounded-lg ring-1 ring-gray-950/5 dark:ring-white/10" style="padding: 0.5rem 0.75rem;">
                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.25rem 0.5rem;">
                    <x-filament::badge :color="match ($entry['peristiwa']) { 'created' => 'success', 'deleted' => 'danger', default => 'gray' }" size="sm">{{ $entry['label'] }}</x-filament::badge>
                    <span class="text-sm font-medium text-gray-950 dark:text-white">{{ $entry['oleh'] }}</span>
                    @if ($entry['peran'])
                        <span class="text-xs text-gray-500 dark:text-gray-400">sebagai {{ $entry['peran'] }}</span>
                    @endif
                    <time class="text-xs text-gray-500 dark:text-gray-400" datetime="{{ $entry['pada']->toIso8601String() }}" style="margin-left: auto;">{{ $entry['pada']->translatedFormat('d M Y H:i') }}</time>
                </div>
                @if ($entry['ganti_akun_oleh'])
                    <p class="text-xs text-warning-600 dark:text-warning-400" style="margin-top: 0.25rem;">Lewat ganti akun oleh {{ $entry['ganti_akun_oleh'] }}</p>
                @endif
                @if ($entry['perubahan'])
                    <ul role="list" class="text-sm text-gray-700 dark:text-gray-200" style="margin-top: 0.375rem; display: grid; gap: 0.125rem;">
                        @foreach ($entry['perubahan'] as $change)
                            <li><span class="text-gray-500 dark:text-gray-400">{{ $change['label'] }}:</span> {{ filled($change['lama']) ? $change['lama'] : '–' }} → {{ filled($change['baru']) ? $change['baru'] : '–' }}</li>
                        @endforeach
                    </ul>
                @endif
            </li>
        @endforeach
    </ol>
@endif
