<x-filament-panels::page>
    <x-filament::tabs label="Bagian">
        <x-filament::tabs.item
            :active="$section !== 'kode-registrasi'"
            icon="heroicon-m-shield-check"
            wire:click="$set('section', 'peran')"
        >
            Peran
        </x-filament::tabs.item>

        <x-filament::tabs.item
            :active="$section === 'kode-registrasi'"
            icon="heroicon-m-key"
            wire:click="$set('section', 'kode-registrasi')"
        >
            Kode Registrasi
        </x-filament::tabs.item>
    </x-filament::tabs>

    @if ($section === 'kode-registrasi')
        @livewire(\App\Livewire\RegistrationCodeSettings::class)
    @else
        {{ $this->table }}
    @endif
</x-filament-panels::page>
