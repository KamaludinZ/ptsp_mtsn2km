<x-filament-widgets::widget>
    <x-filament::section icon="heroicon-o-star" icon-color="warning">
        <x-slot name="heading">Bantu kami meningkatkan layanan</x-slot>
        <x-slot name="description">
            {{ $count }} layanan Anda sudah selesai dan belum dinilai. Survei hanya butuh 2–3 menit.
        </x-slot>

        <x-filament::button tag="a" :href="$url" icon="heroicon-m-star">
            Isi survei kepuasan
        </x-filament::button>
    </x-filament::section>
</x-filament-widgets::widget>
