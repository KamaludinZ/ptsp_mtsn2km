@php
    use App\Filament\Resources\PengumumanResource;
    use App\Support\ContentStatus;

    $s = $this->summary();
    $card = 'rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10';
@endphp

{{-- Manajemen Konten. Inline styles: the panel uses Filament's prebuilt CSS. --}}
<x-filament-panels::page>
    <div style="display: flex; flex-wrap: wrap; gap: .5rem;">
        <x-filament::button tag="a" :href="$s['links']['pengumuman_baru']" icon="heroicon-m-plus">Buat pengumuman</x-filament::button>
        <x-filament::button tag="a" :href="$s['links']['faq']" color="gray" icon="heroicon-m-question-mark-circle">Kelola FAQ</x-filament::button>
        <x-filament::button tag="a" :href="$s['links']['publik_pengumuman']" target="_blank" color="gray" icon="heroicon-m-arrow-top-right-on-square">Lihat situs</x-filament::button>
    </div>

    <section aria-label="Ringkasan konten" style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(10rem, 1fr));">
        @foreach (ContentStatus::LABELS as $key => $label)
            <a href="{{ $s['links']['pengumuman'] }}?tableFilters[status][value]={{ $key }}" class="{{ $card }}" style="padding: 1rem 1.25rem; text-decoration: none;">
                <span class="text-sm text-gray-500 dark:text-gray-400">Pengumuman {{ mb_strtolower($label) }}</span>
                <span class="text-3xl font-semibold text-gray-950 dark:text-white" style="display: block; margin-top: .25rem;">{{ $s['pengumuman'][$key] }}</span>
                <x-filament::badge :color="ContentStatus::COLORS[$key]" size="sm" style="margin-top: .25rem;">{{ $label }}</x-filament::badge>
            </a>
        @endforeach
        <a href="{{ $s['links']['faq'] }}" class="{{ $card }}" style="padding: 1rem 1.25rem; text-decoration: none;">
            <span class="text-sm text-gray-500 dark:text-gray-400">FAQ aktif</span>
            <span class="text-3xl font-semibold text-gray-950 dark:text-white" style="display: block; margin-top: .25rem;">{{ $s['faq']['aktif'] }}</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $s['faq']['nonaktif'] }} nonaktif</span>
        </a>
    </section>

    <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(min(100%, 22rem), 1fr));">
        <x-filament::section icon="heroicon-o-exclamation-circle" heading="Segera berakhir" description="Pengumuman tayang yang berakhir dalam 7 hari.">
            @forelse ($s['ending'] as $item)
                <a href="{{ PengumumanResource::getUrl('edit', ['record' => $item]) }}" class="text-sm" style="display: flex; justify-content: space-between; gap: .5rem; padding: .375rem 0; text-decoration: none;">
                    <span class="text-gray-950 dark:text-white">{{ $item->title }}</span>
                    <span class="text-warning-600 dark:text-warning-400" style="white-space: nowrap;">{{ $item->end_date->translatedFormat('j M') }}</span>
                </a>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada pengumuman yang segera berakhir.</p>
            @endforelse
        </x-filament::section>

        <x-filament::section icon="heroicon-o-clock" heading="Terakhir diubah">
            @forelse ($s['latest'] as $item)
                @php
                    $status = ContentStatus::of($item);
                @endphp
                <a href="{{ PengumumanResource::getUrl('edit', ['record' => $item]) }}" class="text-sm" style="display: flex; justify-content: space-between; align-items: center; gap: .5rem; padding: .375rem 0; text-decoration: none;">
                    <span class="text-gray-950 dark:text-white" style="min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $item->title }}</span>
                    <x-filament::badge :color="ContentStatus::COLORS[$status]" size="sm">{{ ContentStatus::LABELS[$status] }}</x-filament::badge>
                </a>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada pengumuman. <a class="text-primary-600 underline" href="{{ $s['links']['pengumuman_baru'] }}">Buat yang pertama</a>.</p>
            @endforelse
        </x-filament::section>
    </div>
</x-filament-panels::page>
