<x-filament-panels::page>
    <x-filament::tabs label="Tampilan responden">
        <x-filament::tabs.item :active="$tab === 'responden'" wire:click="$set('tab', 'responden')" icon="heroicon-m-check-badge">
            Sudah mengisi
        </x-filament::tabs.item>
        <x-filament::tabs.item :active="$tab === 'belum'" wire:click="$set('tab', 'belum')" icon="heroicon-m-bell-alert" :badge="$this->unratedCount() ?: null">
            Belum mengisi
        </x-filament::tabs.item>
    </x-filament::tabs>

    {{ $this->table }}
</x-filament-panels::page>
