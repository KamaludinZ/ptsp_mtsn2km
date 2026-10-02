@php
    use App\Support\LogReader;
@endphp

<x-filament::section heading="Log aplikasi" icon="heroicon-o-document-text"
    :description="$logFile ? 'Entri terbaru dari ' . basename($logFile) . ' (pesan saja, tanpa jejak tumpukan).' : 'Belum ada berkas log.'">
    <div style="display:flex;flex-wrap:wrap;gap:.75rem;margin-bottom:1rem">
        <x-filament::input.wrapper style="min-width:10rem">
            <x-filament::input.select wire:model.live="logLevel" aria-label="Level log">
                <option value="">Semua level</option>
                @foreach (LogReader::LEVELS as $level)
                    <option value="{{ $level }}">{{ ucfirst($level) }}</option>
                @endforeach
            </x-filament::input.select>
        </x-filament::input.wrapper>
        <x-filament::input.wrapper style="flex:1;min-width:14rem" prefix-icon="heroicon-m-magnifying-glass">
            <x-filament::input type="search" wire:model.live.debounce.400ms="logSearch" placeholder="Cari pesan log" aria-label="Cari pesan log" />
        </x-filament::input.wrapper>
    </div>

    @forelse ($logs as $entry)
        <div class="{{ $loop->first ? '' : 'border-t border-gray-200 dark:border-white/10' }}" style="display:flex;gap:.75rem;padding:.5rem 0;align-items:baseline">
            <x-filament::badge :color="LogReader::color($entry['level'])" size="sm">{{ strtoupper($entry['level']) }}</x-filament::badge>
            <div style="min-width:0;flex:1">
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $entry['at'] }}</p>
                <p class="text-sm text-gray-950 dark:text-white" style="overflow-wrap:anywhere">{{ $entry['message'] }}</p>
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada entri log yang cocok.</p>
    @endforelse
</x-filament::section>

<x-filament::section heading="Audit trail aktivitas pengguna" icon="heroicon-o-finger-print"
    description="Perubahan data yang dicatat sistem. Riwayat per permohonan ada di menu Riwayat Layanan.">
    <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass" style="margin-bottom:1rem">
        <x-filament::input type="search" wire:model.live.debounce.400ms="auditSearch" placeholder="Cari kegiatan, pengguna, atau jenis data" aria-label="Cari audit trail" />
    </x-filament::input.wrapper>

    @forelse ($audit as $activity)
        <div class="{{ $loop->first ? '' : 'border-t border-gray-200 dark:border-white/10' }}" style="display:flex;flex-wrap:wrap;gap:.25rem .75rem;padding:.5rem 0;align-items:baseline">
            <span class="text-xs text-gray-500 dark:text-gray-400" style="min-width:7.5rem">{{ $activity->created_at->translatedFormat('j M Y H:i') }}</span>
            <span class="text-sm font-semibold text-gray-950 dark:text-white">{{ $activity->causer?->name ?? 'Sistem' }}</span>
            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $activity->description }}</span>
            @if ($activity->subject_type)
                <x-filament::badge color="gray" size="sm">{{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}</x-filament::badge>
            @endif
        </div>
    @empty
        <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada aktivitas yang cocok.</p>
    @endforelse

    <x-filament::link :href="\App\Filament\Resources\ServiceHistoryResource::getUrl('index')" icon="heroicon-m-clock" size="sm" style="margin-top:.75rem">
        Buka Riwayat Layanan & Audit Trail per tiket
    </x-filament::link>
</x-filament::section>
