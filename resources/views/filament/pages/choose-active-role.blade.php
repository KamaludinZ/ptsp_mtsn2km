@php $grid = 'display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit,minmax(18rem,1fr))'; @endphp

<x-filament-panels::page>
    <div style="{{ $grid }}">
        @foreach ($roles as $role)
            <x-filament::section :icon="$role['icon']" :icon-color="$role['active'] ? 'primary' : 'gray'"
                @class(['ring-2 ring-primary-500' => $role['active']])>
                <x-slot name="heading">{{ $role['label'] }}</x-slot>
                <x-slot name="description"><code class="text-xs">{{ $role['name'] }}</code></x-slot>

                @if ($role['active'])
                    <x-slot name="headerEnd">
                        <x-filament::badge color="success" icon="heroicon-m-check-circle">Sedang aktif</x-filament::badge>
                    </x-slot>
                @endif

                <p class="text-sm text-gray-600 dark:text-gray-300">{{ $role['description'] }}</p>

                <div style="display:flex;flex-wrap:wrap;gap:.25rem;margin-top:1rem">
                    @foreach ($role['areas'] as $area)
                        <x-filament::badge color="gray">{{ $area }}</x-filament::badge>
                    @endforeach
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;gap:.75rem;margin-top:1.25rem">
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        @if ($role['last_used'])
                            Pilihan terakhir · {{ $role['last_used']->locale('id')->diffForHumans() }}
                        @endif
                    </span>

                    <x-filament::button size="sm" :color="$role['active'] ? 'gray' : 'primary'"
                        icon="heroicon-m-arrow-right-end-on-rectangle" icon-position="after"
                        wire:click="choose('{{ $role['name'] }}')" wire:loading.attr="disabled">
                        {{ $role['active'] ? 'Lanjutkan' : 'Gunakan peran ini' }}
                    </x-filament::button>
                </div>
            </x-filament::section>
        @endforeach
    </div>
</x-filament-panels::page>
