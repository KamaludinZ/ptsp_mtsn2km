<x-filament-panels::page>
    <x-filament::tabs label="Saring kategori layanan masuk">
        @foreach ($this->getTabs() as $key => $tab)
            <x-filament::tabs.item
                :active="(string) $activeTab === (string) $key"
                :badge="$tab['count']"
                :badge-color="(string) $activeTab === (string) $key ? 'primary' : $tab['color']"
                wire:click="$set('activeTab', {{ $key === '' ? 'null' : \Illuminate\Support\Js::from($key) }})"
            >
                {{ $tab['label'] }}
            </x-filament::tabs.item>
        @endforeach
    </x-filament::tabs>

    @if ($description = \App\Support\IncomingCategory::DESCRIPTIONS[$activeTab] ?? null)
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
    @endif

    {{ $this->table }}
</x-filament-panels::page>
