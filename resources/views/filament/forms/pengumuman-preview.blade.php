@php
    use App\Support\ContentStatus;
    use Illuminate\Support\Carbon;

    $state = $this->data ?? [];
    $title = trim((string) ($state['title'] ?? '')) ?: 'Judul pengumuman';
    $category = $state['category'] ?? null;
    $content = (string) ($state['content'] ?? '');
    $publish = filled($state['publish_date'] ?? null) ? Carbon::parse($state['publish_date']) : null;
    $end = filled($state['end_date'] ?? null) ? Carbon::parse($state['end_date']) : null;
    $preview = new \App\Models\Pengumuman([
        'is_active' => (bool) ($state['is_active'] ?? true),
        'publish_date' => $publish,
        'end_date' => $end,
    ]);
    $status = ContentStatus::of($preview);
@endphp

{{-- Live preview of the public announcement card. Inline styles: Filament's prebuilt CSS. --}}
<div style="display: flex; flex-direction: column; gap: .75rem;">
    <div style="display: flex; flex-wrap: wrap; gap: .375rem; align-items: center;">
        <x-filament::badge :color="ContentStatus::COLORS[$status]" size="sm">{{ ContentStatus::LABELS[$status] }}</x-filament::badge>
        @if ($category)
            <x-filament::badge color="gray" size="sm">{{ \Illuminate\Support\Str::headline($category) }}</x-filament::badge>
        @endif
    </div>
    <article class="rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10" style="padding: 1rem 1.25rem;">
        <p class="text-xs text-gray-500 dark:text-gray-400">
            {{ $publish?->translatedFormat('j F Y') ?? 'Tanggal tayang' }}{{ $end ? ' – ' . $end->translatedFormat('j F Y') : '' }}
        </p>
        <h3 class="text-lg font-semibold text-gray-950 dark:text-white" style="margin: .25rem 0 .5rem;">{{ $title }}</h3>
        <div class="prose prose-sm dark:prose-invert text-gray-700 dark:text-gray-200" style="max-width: none;">
            @if (filled(strip_tags($content)))
                {!! str($content)->sanitizeHtml() !!}
            @else
                <p class="text-gray-400">Isi pengumuman akan tampil di sini.</p>
            @endif
        </div>
        @if (filled($state['url'] ?? null))
            <p class="text-sm" style="margin-top: .75rem;"><span class="text-primary-600 underline">Buka tautan</span></p>
        @endif
    </article>
    @if ($status === 'dijadwalkan')
        <p class="text-xs text-info-600 dark:text-info-400">Baru tampil di situs mulai {{ $publish->translatedFormat('j F Y') }}.</p>
    @elseif ($status === 'draf')
        <p class="text-xs text-gray-500 dark:text-gray-400">Draf: tidak tampil di situs.</p>
    @elseif ($status === 'berakhir')
        <p class="text-xs text-warning-600 dark:text-warning-400">Sudah berakhir: tidak lagi tampil di situs.</p>
    @endif
</div>
