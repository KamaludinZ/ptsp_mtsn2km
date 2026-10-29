@php($entries = \App\Support\DocumentHistory::forTicket($getRecord()))

{{-- Riwayat berkas & hasil layanan, newest first (App\Support\DocumentHistory). --}}
@if (! $entries)
    <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada berkas atau hasil layanan yang diunggah.</p>
@else
    <div x-data="{ pratinjau: null }">
        <ul style="margin: 0; padding: 0; list-style: none;">
            @foreach ($entries as ['log' => $log, 'type' => $type, 'documents' => $documents, 'replaced' => $replaced])
                <li class="{{ $loop->first ? '' : 'border-t border-gray-200 dark:border-white/10' }}"
                    style="display: flex; flex-wrap: wrap; gap: 0.25rem 0.75rem; align-items: baseline; padding: 0.5rem 0;">
                    <x-filament::badge :color="$type === 'output' ? 'success' : 'gray'" size="sm">{{ $type === 'output' ? 'Hasil layanan' : 'Berkas' }}</x-filament::badge>
                    <span class="text-sm text-gray-950 dark:text-white" style="flex: 1; min-width: 12rem;">
                        {{ $log->notes }}
                        <span class="block text-xs text-gray-500 dark:text-gray-400">
                            {{ $log->created_at->translatedFormat('j M Y · H:i') }} · {{ $log->performer?->name ?? 'Sistem' }}
                        </span>
                    </span>
                    @foreach ($documents as $name => $url)
                        @if ($preview = \App\Support\TicketDocuments::previewUrl($name, $url))
                            {{-- A service output is stored under a random name; the log keeps the uploaded one. --}}
                            <x-document-preview-button :nama="$type === 'output' ? ($log->metadata['file_name'] ?? $name) : $name" :url="$preview" :unduh="$url" modal="pratinjau-dokumen-riwayat" />
                        @endif
                        <x-filament::link :href="$url" target="_blank" icon="heroicon-m-document-arrow-down" size="sm">Unduh</x-filament::link>
                    @endforeach
                    @if ($replaced)
                        <span class="text-xs text-gray-500 dark:text-gray-400">Diganti versi lebih baru</span>
                    @endif
                </li>
            @endforeach
        </ul>

        @if (collect($entries)->flatMap(fn (array $entry) => array_keys($entry['documents']))->contains(fn (string $name) => \App\Support\TicketDocuments::isPdf($name)))
            <x-document-preview-modal id="pratinjau-dokumen-riwayat" />
        @endif
    </div>
@endif
