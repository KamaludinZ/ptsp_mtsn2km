@php
    use App\Support\IncomingCategory;

    $history = IncomingCategory::history($getRecord());
@endphp

{{-- Riwayat kategori layanan masuk. Inline styles: the panel uses Filament's prebuilt CSS. --}}
<ol style="margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 0.75rem;">
    @foreach ($history as $entry)
        <li class="{{ $loop->first ? '' : 'border-t border-gray-200 dark:border-white/10' }}" style="{{ $loop->first ? '' : 'padding-top: 0.75rem;' }}">
            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.375rem;">
                @if ($entry['from'])
                    <x-filament::badge :color="IncomingCategory::COLORS[$entry['from']] ?? 'gray'" size="sm">{{ IncomingCategory::label($entry['from']) }}</x-filament::badge>
                    <span aria-hidden="true" class="text-gray-400">→</span>
                    <span class="sr-only">menjadi</span>
                @endif
                <x-filament::badge :color="IncomingCategory::COLORS[$entry['to']] ?? 'gray'" size="sm" :icon="IncomingCategory::ICONS[$entry['to']] ?? null">{{ IncomingCategory::label($entry['to']) }}</x-filament::badge>
                @if ($loop->first)
                    <span class="text-xs text-gray-500 dark:text-gray-400">Kategori awal</span>
                @elseif (! $entry['manual'])
                    <span class="text-xs text-gray-500 dark:text-gray-400">Kembali mengikuti instruksi</span>
                @endif
                @if ($loop->last)
                    <span class="text-xs font-semibold text-primary-600 dark:text-primary-400">Saat ini</span>
                @endif
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400" style="margin-top: 0.25rem;">
                {{ $entry['at']?->translatedFormat('j M Y · H:i') ?? '–' }} · {{ $entry['actor'] }}
            </p>
            @if ($entry['reason'])
                <p class="text-sm text-gray-700 dark:text-gray-300" style="margin-top: 0.125rem;">{{ $entry['reason'] }}</p>
            @endif
        </li>
    @endforeach
</ol>
