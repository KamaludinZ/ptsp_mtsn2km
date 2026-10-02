@php($actions = $this->getActions())

<x-filament-widgets::widget>
    @if (count($actions))
        <x-filament::section heading="Akses Cepat" compact>
            {{-- Inline grid: the panel uses Filament's prebuilt CSS, which lacks most grid-cols-* utilities. --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(8.5rem, 1fr)); gap: 0.75rem;">
                @foreach ($actions as $action)
                    <a href="{{ $action['url'] }}"
                       class="flex flex-col gap-1 p-3 transition rounded-lg ring-1 ring-gray-950/5 hover:bg-gray-50 dark:ring-white/10 dark:hover:bg-white/5">
                        <x-filament::icon :icon="$action['icon']" class="w-6 h-6 text-primary-500" />
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">{{ $action['label'] }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $action['description'] }}</span>
                    </a>
                @endforeach
            </div>
        </x-filament::section>
    @endif
</x-filament-widgets::widget>
