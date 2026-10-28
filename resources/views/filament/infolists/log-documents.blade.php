@php($documents = $getRecord()->relatedDocuments())

@if ($documents)
    <div x-data="{ pratinjau: null }">
        <ul style="margin: 0; padding: 0; list-style: none;">
            @foreach ($documents as $name => $url)
                <li style="display: flex; flex-wrap: wrap; gap: 0.25rem 0.75rem; align-items: center;">
                    <x-filament::link :href="$url" target="_blank" icon="heroicon-m-document-arrow-down" size="sm">{{ $name }}</x-filament::link>
                    @if ($preview = \App\Support\TicketDocuments::previewUrl($name, $url))
                        {{-- A service output is stored under a random name; the log keeps the uploaded one. --}}
                        <x-document-preview-button :nama="$getRecord()->action === 'output_uploaded' ? ($getRecord()->metadata['file_name'] ?? $name) : $name" :url="$preview" :unduh="$url" modal="pratinjau-dokumen-langkah" />
                    @endif
                </li>
            @endforeach
        </ul>

        @if (collect(array_keys($documents))->contains(fn (string $name) => \App\Support\TicketDocuments::isPdf($name)))
            <x-document-preview-modal id="pratinjau-dokumen-langkah" />
        @endif
    </div>
@else
    <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada berkas pada langkah ini.</p>
@endif
