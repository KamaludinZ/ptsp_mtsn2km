@props(['id'])

{{--
    Pratinjau PDF in a modal, without leaving the page. The surrounding
    x-data holds `pratinjau` ({ nama, url, unduh } or null); a button sets it
    and dispatches open-modal with this id. The iframe exists only while a
    document is shown, so closing the modal unloads it. Escape is caught
    before other modals hear it: opened from inside another modal (the
    detail of a Riwayat Layanan step), it closes only the pratinjau.
--}}
<div
    x-on:close-modal.window="if ($event.detail.id === @js($id)) pratinjau = null"
    x-on:keydown.escape.window.capture="if (pratinjau) { $event.stopPropagation(); $dispatch('close-modal', { id: @js($id) }) }"
>
    <x-filament::modal :id="$id" width="6xl" :close-by-escaping="false">
        <x-slot name="heading">
            <span x-text="pratinjau?.nama ?? 'Pratinjau dokumen'"></span>
        </x-slot>
        <x-slot name="description">
            Bila dokumen tidak tampil di perangkat ini, buka di tab baru atau unduh.
        </x-slot>

        {{-- "Memuat" shows through the iframe until the PDF has loaded; a device that cannot show it gets a hint after a while. --}}
        <template x-if="pratinjau">
            <div
                x-data="{ memuat: true, lama: false }"
                x-init="setTimeout(() => lama = memuat, 8000)"
                class="rounded-lg bg-gray-100 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
                style="position: relative; height: 70vh; overflow: hidden;"
            >
                <div x-show="memuat" role="status" style="position: absolute; inset: 0;">
                    <div
                        class="text-sm text-gray-500 dark:text-gray-400"
                        style="display: flex; height: 100%; flex-direction: column; align-items: center; justify-content: center; gap: 0.5rem; padding: 1rem; text-align: center;"
                    >
                        <x-filament::loading-indicator x-show="! lama" class="h-6 w-6" />
                        <span x-show="! lama">Memuat dokumen…</span>
                        <span x-show="lama" x-cloak data-pratinjau-lama>Dokumen belum tampil di perangkat ini. Buka di tab baru atau unduh.</span>
                    </div>
                </div>
                <iframe
                    x-bind:src="pratinjau.url"
                    x-bind:title="'Pratinjau ' + pratinjau.nama"
                    x-on:load="memuat = false"
                    data-pratinjau-dokumen
                    style="position: absolute; inset: 0; width: 100%; height: 100%; border: 0;"
                ></iframe>
            </div>
        </template>

        <x-slot name="footerActions">
            <x-filament::button tag="a" x-bind:href="pratinjau?.url" target="_blank" color="gray" icon="heroicon-m-arrow-top-right-on-square">
                Buka di tab baru
            </x-filament::button>
            <x-filament::button tag="a" x-bind:href="pratinjau?.unduh" icon="heroicon-m-arrow-down-tray">
                Unduh
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</div>
