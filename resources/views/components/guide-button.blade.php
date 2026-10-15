@props(['url'])
<x-filament::button
    tag="a"
    :href="$url"
    color="gray"
    size="sm"
    icon="heroicon-o-book-open"
    outlined
    title="Panduan lengkap menu sesuai peran Anda"
>
    <span class="hidden sm:inline">Panduan</span>
</x-filament::button>
