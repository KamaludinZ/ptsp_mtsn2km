@php($documents = $getRecord()->relatedDocuments())

@if ($documents)
    <ul style="margin: 0; padding: 0; list-style: none;">
        @foreach ($documents as $name => $url)
            <li>
                <x-filament::link :href="$url" target="_blank" icon="heroicon-m-document-arrow-down" size="sm">{{ $name }}</x-filament::link>
            </li>
        @endforeach
    </ul>
@else
    <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada berkas pada langkah ini.</p>
@endif
