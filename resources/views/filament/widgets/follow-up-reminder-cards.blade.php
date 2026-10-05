@php
    use App\Filament\Resources\TicketResource;
    use App\Services\TicketService;
    use App\Support\FollowUpReminders;

    $reminders = $this->getReminders();
    $colors = ['overdue' => 'danger', 'today' => 'warning', 'soon' => 'info'];
@endphp

{{-- Pengingat tindak lanjut. Inline styles: the panel uses Filament's prebuilt CSS. --}}
<x-filament-widgets::widget>
    <x-filament::section icon="heroicon-o-clock" :heading="'Pengingat tindak lanjut' . ($reminders->isNotEmpty() ? ' (' . $reminders->count() . ')' : '')"
        description="Rencana tindak lanjut dari catatan permohonan yang jatuh tempo dalam {{ FollowUpReminders::AHEAD_DAYS }} hari.">
        @if ($reminders->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada tindak lanjut yang jatuh tempo. Rencana tindak lanjut ditambahkan lewat "Catatan tindak lanjut" pada detail permohonan.</p>
        @else
            <div style="display: grid; gap: 0.75rem; grid-template-columns: repeat(auto-fill, minmax(min(100%, 17rem), 1fr));">
                @foreach ($reminders as $item)
                    <a href="{{ TicketResource::getUrl('view', ['record' => $item['ticket']]) }}"
                       class="rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10 hover:bg-gray-50 dark:hover:bg-white/5"
                       style="display: flex; flex-direction: column; gap: 0.375rem; padding: 0.875rem 1rem; text-decoration: none; border-left: 4px solid rgb(var(--{{ $colors[$item['state']] }}-500));">
                        <span style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                            <span class="text-sm font-semibold text-gray-950 dark:text-white">{{ $item['ticket']->ticket_number }}</span>
                            <x-filament::badge :color="$colors[$item['state']]" size="sm">{{ FollowUpReminders::label($item) }}</x-filament::badge>
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $item['ticket']->service?->name }} · {{ $item['ticket']->user?->name }}
                        </span>
                        <span class="text-sm text-gray-700 dark:text-gray-200" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $item['note'] }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $item['type'] ? (TicketService::FOLLOW_UP_TYPES[$item['type']] ?? $item['type']) . ' · ' : '' }}rencana {{ $item['due']->translatedFormat('j M Y') }}
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
