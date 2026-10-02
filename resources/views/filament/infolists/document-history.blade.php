@php
    $uploads = $getRecord()->logs()
        ->whereIn('action', ['file_uploaded', 'output_uploaded'])
        ->with('performer:id,name')
        ->latest()->latest('id')
        ->get();
    $latestOutput = $uploads->firstWhere('action', 'output_uploaded');
@endphp

{{-- Riwayat berkas & hasil layanan, newest first. Replaced outputs are deleted from storage, so only the log remains. --}}
@if ($uploads->isEmpty())
    <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada berkas atau hasil layanan yang diunggah.</p>
@else
    <ul style="margin: 0; padding: 0; list-style: none;">
        @foreach ($uploads as $log)
            @php
                $isOutput = $log->action === 'output_uploaded';
                $documents = $log->relatedDocuments();
            @endphp
            <li class="{{ $loop->first ? '' : 'border-t border-gray-200 dark:border-white/10' }}"
                style="display: flex; flex-wrap: wrap; gap: 0.25rem 0.75rem; align-items: baseline; padding: 0.5rem 0;">
                <x-filament::badge :color="$isOutput ? 'success' : 'gray'" size="sm">{{ $isOutput ? 'Hasil layanan' : 'Berkas' }}</x-filament::badge>
                <span class="text-sm text-gray-950 dark:text-white" style="flex: 1; min-width: 12rem;">
                    {{ $log->notes }}
                    <span class="block text-xs text-gray-500 dark:text-gray-400">
                        {{ $log->created_at->translatedFormat('j M Y · H:i') }} · {{ $log->performer?->name ?? 'Sistem' }}
                    </span>
                </span>
                @if ($documents)
                    @foreach ($documents as $name => $url)
                        <x-filament::link :href="$url" target="_blank" icon="heroicon-m-document-arrow-down" size="sm">Unduh</x-filament::link>
                    @endforeach
                @elseif ($isOutput && $log->isNot($latestOutput))
                    <span class="text-xs text-gray-500 dark:text-gray-400">Diganti versi lebih baru</span>
                @endif
            </li>
        @endforeach
    </ul>
@endif
