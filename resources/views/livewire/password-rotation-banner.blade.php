{{-- Pengingat ganti kata sandi. Inline styles: the panel uses Filament's prebuilt CSS. --}}
<div>
    @if ($reason)
        {{-- Tutup (×): hanya untuk halaman ini. Ingatkan nanti: untuk sisa sesi. --}}
        <div role="status" data-password-rotation-banner="{{ $reason }}" x-data="{ open: true }" x-show="open"
            class="rounded-xl bg-warning-50 text-warning-800 ring-1 ring-warning-600/20 dark:bg-warning-400/10 dark:text-warning-300 dark:ring-warning-400/30"
            style="display:flex;flex-wrap:wrap;align-items:center;gap:.5rem 1rem;padding:.75rem 1rem;margin-bottom:1.5rem;font-size:.875rem;line-height:1.4">
            <x-filament::icon icon="heroicon-o-key" class="h-5 w-5" style="flex:none" />
            <span style="flex:1 1 16rem;min-width:0">{{ $message }}</span>
            <span style="display:flex;align-items:center;gap:.5rem;flex:none">
                <x-filament::button tag="a" :href="$profileUrl" size="sm" color="warning" data-password-rotation-change>Ganti kata sandi</x-filament::button>
                <x-filament::button wire:click="dismiss" size="sm" color="gray" outlined data-password-rotation-dismiss>Ingatkan nanti</x-filament::button>
                <x-filament::icon-button icon="heroicon-m-x-mark" color="gray" size="sm" label="Tutup pengingat" x-on:click="open = false" data-password-rotation-close />
            </span>
        </div>
    @endif
</div>
