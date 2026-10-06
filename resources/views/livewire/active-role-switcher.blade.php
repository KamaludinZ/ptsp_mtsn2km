{{-- Penanda peran aktif + menu ganti peran cepat di header /cp (ActiveRoles). --}}
<div style="margin-inline-end:.75rem">
    <style>
        .ptsp-active-role{display:flex;align-items:center;gap:.5rem;padding:.375rem .75rem;border-radius:.5rem;font-size:.875rem;font-weight:600;line-height:1.15;text-align:start;color:rgb(var(--primary-700));background:rgb(var(--primary-50));box-shadow:inset 0 0 0 1px rgba(var(--primary-600),.3);transition:background-color .15s}
        .ptsp-active-role:hover{background:rgb(var(--primary-100))}
        .ptsp-active-role__caption{display:block;font-size:.65rem;font-weight:500;letter-spacing:.05em;text-transform:uppercase;opacity:.75}
        .ptsp-active-role__text,.ptsp-active-role__chevron{display:none}
        @media (min-width:640px){.ptsp-active-role__text{display:block}.ptsp-active-role__chevron{display:block}}
        .dark .ptsp-active-role{color:rgb(var(--primary-400));background:rgba(var(--primary-500),.1);box-shadow:inset 0 0 0 1px rgba(var(--primary-400),.3)}
        .dark .ptsp-active-role:hover{background:rgba(var(--primary-500),.2)}
    </style>

    <x-filament::dropdown placement="bottom-end" width="xs">
        <x-slot name="trigger">
            @php $label = $current['label'] ?? 'Belum dipilih'; @endphp
            <button type="button" class="ptsp-active-role" data-active-role="{{ $current['name'] ?? '' }}"
                title="Peran aktif: {{ $label }} — klik untuk ganti peran"
                aria-label="Peran aktif: {{ $label }}. Ganti peran">
                <x-filament::icon :icon="$current['icon'] ?? 'heroicon-o-arrows-right-left'" class="h-5 w-5 shrink-0" />
                <span class="ptsp-active-role__text">
                    <span class="ptsp-active-role__caption">Peran aktif</span>
                    {{ $label }}
                </span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="ptsp-active-role__chevron h-4 w-4 opacity-60" />
            </button>
        </x-slot>

        <x-filament::dropdown.header icon="heroicon-m-arrows-right-left" color="gray">
            Ganti peran
        </x-filament::dropdown.header>

        <x-filament::dropdown.list>
            @foreach ($roles as $role)
                @if ($role['active'])
                    <x-filament::dropdown.list.item :icon="$role['icon']" color="primary" disabled>
                        {{ $role['label'] }} · aktif
                    </x-filament::dropdown.list.item>
                @else
                    <x-filament::dropdown.list.item :icon="$role['icon']" color="gray"
                        wire:click="switchTo('{{ $role['name'] }}')" wire:loading.attr="disabled">
                        {{ $role['label'] }}
                    </x-filament::dropdown.list.item>
                @endif
            @endforeach
        </x-filament::dropdown.list>

        <x-filament::dropdown.list>
            <x-filament::dropdown.list.item icon="heroicon-m-squares-2x2" color="gray" tag="a" :href="$pickerUrl">
                Lihat semua peran
            </x-filament::dropdown.list.item>
        </x-filament::dropdown.list>
    </x-filament::dropdown>
</div>
