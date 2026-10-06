{{-- Panel keputusan peran aktif di Antrean Disposisi. --}}
<x-filament-widgets::widget>
    <x-filament::section :icon="$active['icon'] ?? 'heroicon-o-arrows-right-left'" :icon-color="$isLeader ? 'success' : 'warning'">
        <x-slot name="heading">
            Memutuskan sebagai: {{ $active['label'] ?? 'belum memilih peran' }}
        </x-slot>
        <x-slot name="description">
            Disposisi dan penolakan dicatat atas nama peran aktif ini.
        </x-slot>

        <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(14rem,1fr))">
            <div data-decision="mine">
                <div class="text-sm text-gray-500 dark:text-gray-400">Menunggu keputusan di peran ini</div>
                <div class="text-2xl font-semibold text-gray-950 dark:text-white">{{ $mine }} permohonan</div>
            </div>

            @if ($otherRole)
                <div data-decision="elsewhere">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Menunggu peran {{ $otherRole['label'] }}</div>
                    <div class="text-2xl font-semibold text-gray-950 dark:text-white">{{ $elsewhere }} permohonan</div>
                </div>
            @endif
        </div>

        @if (! $isLeader)
            <p class="text-sm text-warning-700 dark:text-warning-400" style="margin-top:1rem">
                Peran aktif Anda bukan peran pimpinan, jadi antrean di bawah kosong. Aktifkan peran pimpinan untuk memberi disposisi.
            </p>
        @endif

        @if ($otherRole && ($elsewhere > 0 || ! $isLeader))
            <form method="POST" action="{{ route('peran-aktif.ganti') }}" style="margin-top:1rem">
                @csrf
                <input type="hidden" name="peran" value="{{ $otherRole['name'] }}">
                <input type="hidden" name="kembali" value="{{ $returnUrl }}">
                <x-filament::button type="submit" size="sm" icon="heroicon-m-arrows-right-left">
                    Putuskan sebagai {{ $otherRole['label'] }}
                </x-filament::button>
            </form>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
