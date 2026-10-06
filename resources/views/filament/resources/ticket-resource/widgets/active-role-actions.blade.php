{{-- Aksi untuk peran aktif di halaman tiket (matriks: TicketRoleActionStub). --}}
<x-filament-widgets::widget>
    <x-filament::section icon="heroicon-o-arrows-right-left" icon-color="primary" collapsible>
        <x-slot name="heading">
            Aksi untuk peran aktif: {{ $active['label'] ?? 'belum dipilih' }}
        </x-slot>
        <x-slot name="description">
            Tombol aksi tiket mengikuti peran yang sedang aktif, bukan semua peran yang Anda pegang.
        </x-slot>

        <ul style="display:grid;gap:.5rem" role="list">
            @foreach ($actions as $action)
                <li data-ticket-action="{{ $action['key'] }}" data-allowed="{{ $action['allowed'] ? 'ya' : 'tidak' }}"
                    style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.5rem .75rem"
                    class="rounded-lg px-3 py-2 ring-1 ring-gray-950/5 dark:ring-white/10">
                    <span style="display:flex;align-items:center;gap:.5rem;min-width:0">
                        <x-filament::icon :icon="$action['icon']" @class([
                            'h-5 w-5 shrink-0',
                            'text-success-600 dark:text-success-400' => $action['allowed'],
                            'text-gray-400 dark:text-gray-500' => ! $action['allowed'],
                        ]) />
                        <span class="text-sm font-medium text-gray-950 dark:text-white">{{ $action['label'] }}</span>
                    </span>

                    <span style="display:flex;flex-wrap:wrap;align-items:center;gap:.5rem">
                        @if ($action['allowed'])
                            <x-filament::badge color="success" icon="heroicon-m-check">Tersedia</x-filament::badge>
                        @else
                            <x-filament::badge color="gray" icon="heroicon-m-lock-closed"
                                :tooltip="'Peran yang berwenang: ' . implode(', ', $action['roles'])">
                                Butuh peran lain
                            </x-filament::badge>

                            @if ($action['switch_to'])
                                <form method="POST" action="{{ route('peran-aktif.ganti') }}" style="display:inline">
                                    @csrf
                                    <input type="hidden" name="peran" value="{{ $action['switch_to']['name'] }}">
                                    <input type="hidden" name="kembali" value="{{ $returnUrl }}">
                                    <x-filament::button type="submit" size="xs" color="gray" outlined
                                        icon="heroicon-m-arrows-right-left">
                                        Ganti ke {{ $action['switch_to']['label'] }}
                                    </x-filament::button>
                                </form>
                            @endif
                        @endif
                    </span>
                </li>
            @endforeach
        </ul>
    </x-filament::section>
</x-filament-widgets::widget>
