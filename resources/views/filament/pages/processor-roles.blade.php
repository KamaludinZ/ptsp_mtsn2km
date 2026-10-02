@php $grid = 'display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit,minmax(18rem,1fr))'; @endphp

<x-filament-panels::page>
    <div style="{{ $grid }}">
        @foreach ($roles as $row)
            <x-filament::section :icon="$row['icon']" icon-color="primary">
                <x-slot name="heading">{{ $row['label'] }}</x-slot>
                <x-slot name="description"><code class="text-xs">{{ $row['name'] }}</code></x-slot>

                <x-slot name="headerEnd">
                    <div style="display:flex;gap:.75rem;align-items:center">
                        {{ ($this->assignAction)(['role' => $row['name']]) }}
                        @if ($url = $editUrl($row['role']))
                            <x-filament::link :href="$url" size="sm" icon="heroicon-m-pencil-square" color="gray">Atur izin</x-filament::link>
                        @endif
                    </div>
                </x-slot>

                <p class="text-sm text-gray-600 dark:text-gray-300">{{ $row['description'] }}</p>

                <dl class="text-sm" style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-top:1rem">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Pemegang</dt>
                        <dd class="text-lg font-semibold">{{ $row['holders']->count() }} staf</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Layanan penerima</dt>
                        <dd class="text-lg font-semibold">{{ $row['services'] }} layanan</dd>
                    </div>
                </dl>

                <div style="display:flex;flex-wrap:wrap;gap:.25rem;margin-top:1rem">
                    @if (! $row['role'])
                        <x-filament::badge color="warning" icon="heroicon-m-exclamation-triangle">Peran belum dibuat</x-filament::badge>
                    @elseif ($row['holders']->isEmpty())
                        <x-filament::badge color="gray">Belum ada pemegang</x-filament::badge>
                    @else
                        @foreach ($row['holders']->take(4) as $holder)
                            <x-filament::badge color="primary">{{ $holder->name }}</x-filament::badge>
                        @endforeach
                        @if ($row['holders']->count() > 4)
                            <x-filament::badge color="gray">+{{ $row['holders']->count() - 4 }} lainnya</x-filament::badge>
                        @endif
                    @endif
                </div>
            </x-filament::section>
        @endforeach
    </div>
</x-filament-panels::page>
