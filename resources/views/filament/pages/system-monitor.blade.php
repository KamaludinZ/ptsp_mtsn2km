{{-- Monitoring Sistem: one partial per tab (resources/views/filament/pages/system-monitor/*). --}}
<x-filament-panels::page>
    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:.5rem" role="status">
        <x-filament::badge :color="$overall['level']" size="lg"
            :icon="match ($overall['level']) { 'success' => 'heroicon-m-check-circle', 'warning' => 'heroicon-m-exclamation-triangle', default => 'heroicon-m-x-circle' }">
            Status sistem: {{ $overall['label'] }}
        </x-filament::badge>
        @if ($overall['issues'])
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ implode(' · ', $overall['issues']) }}</span>
        @endif
        <span class="text-xs text-gray-500 dark:text-gray-400" style="margin-left:auto">
            Dicek {{ \Illuminate\Support\Carbon::parse($overall['checked_at'])->translatedFormat('H:i:s') }}
        </span>
    </div>

    <x-filament::tabs>
        @foreach ($tabs as $key => [$label, $icon])
            <x-filament::tabs.item :active="$activeTab === $key" wire:click="$set('tab', '{{ $key }}')" :icon="$icon">
                {{ $label }}
            </x-filament::tabs.item>
        @endforeach
    </x-filament::tabs>

    <div wire:poll.30s>
        @includeIf('filament.pages.system-monitor.' . $activeTab)
    </div>
</x-filament-panels::page>
