@props(['nama', 'url', 'unduh', 'modal'])

{{-- Opens one PDF in <x-document-preview-modal> (same x-data scope, id $modal). --}}
@php($open = 'pratinjau = ' . \Illuminate\Support\Js::from(['nama' => $nama, 'url' => $url, 'unduh' => $unduh]) . '; $dispatch(\'open-modal\', { id: ' . \Illuminate\Support\Js::from($modal) . ' })')
<x-filament::link tag="button" type="button" icon="heroicon-m-eye" size="sm" :x-on:click="$open" aria-haspopup="dialog">Pratinjau</x-filament::link>
