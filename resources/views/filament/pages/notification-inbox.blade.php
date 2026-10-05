<x-filament-panels::page>
    <x-filament::tabs label="Saring notifikasi">
        @foreach ($this->getInboxTabs() as $key => $tab)
            <x-filament::tabs.item
                :active="$show === $key"
                :badge="$tab['count']"
                wire:click="$set('show', '{{ $key }}')"
            >
                {{ $tab['label'] }}
            </x-filament::tabs.item>
        @endforeach
    </x-filament::tabs>

    {{ $this->table }}
</x-filament-panels::page>
