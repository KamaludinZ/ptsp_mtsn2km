<div>
<form wire:submit="save" class="space-y-6">
    {{ $this->form }}

    <div class="flex flex-wrap items-center gap-3">
        <x-filament::button type="submit" icon="heroicon-m-check">
            Simpan
        </x-filament::button>

        @if ($isOpen)
            <x-filament::badge color="success" icon="heroicon-m-lock-open">Pendaftaran civitas dibuka</x-filament::badge>
        @else
            <x-filament::badge color="gray" icon="heroicon-m-lock-closed">Pendaftaran civitas ditutup</x-filament::badge>
        @endif
    </div>

    <x-filament-actions::modals />
</form>
</div>
