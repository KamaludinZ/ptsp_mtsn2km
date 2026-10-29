@php($documents = \App\Support\TicketDocuments::listFor($getRecord(), $kind))
@php($modal = "pratinjau-dokumen-{$kind}")

{{-- Berkas persyaratan or hasil layanan of the ticket (App\Support\TicketDocuments::listFor). --}}
@if (! $documents)
    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $empty ?? 'Belum ada berkas.' }}</p>
@else
    <div x-data="{ pratinjau: null }">
        <ul style="margin: 0; padding: 0; list-style: none;">
            @foreach ($documents as $document)
                <li class="{{ $loop->first ? '' : 'border-t border-gray-200 dark:border-white/10' }}"
                    style="display: flex; flex-wrap: wrap; gap: 0.25rem 0.75rem; align-items: center; padding: 0.5rem 0;">
                    <x-filament::icon
                        :icon="$document['pdf'] ? 'heroicon-o-document-text' : 'heroicon-o-document'"
                        class="h-5 w-5 text-gray-400 dark:text-gray-500"
                    />
                    <span class="text-sm text-gray-950 dark:text-white" style="flex: 1; min-width: 10rem; overflow-wrap: anywhere;">
                        {{ $document['nama'] }}
                        <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $document['keterangan'] }}</span>
                    </span>
                    <x-filament::badge :color="$document['pdf'] ? 'info' : 'gray'" size="sm">{{ $document['jenis'] }}</x-filament::badge>
                    @if ($document['pratinjau'])
                        <x-document-preview-button :nama="$document['nama']" :url="$document['pratinjau']" :unduh="$document['unduh']" :modal="$modal" />
                    @endif
                    <x-filament::link :href="$document['unduh']" target="_blank" icon="heroicon-m-arrow-down-tray" size="sm">Unduh</x-filament::link>
                </li>
            @endforeach
        </ul>

        @if (array_filter(array_column($documents, 'pratinjau')))
            <x-document-preview-modal :id="$modal" />
        @endif
    </div>
@endif
