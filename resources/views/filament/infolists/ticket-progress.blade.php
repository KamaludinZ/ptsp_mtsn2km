@php
    use App\Support\TicketProgress;

    $ticket = $getRecord();
    $steps = TicketProgress::steps($ticket);
    $deadline = TicketProgress::deadline($ticket);
    $colors = ['done' => 'success-500', 'current' => 'primary-500', 'stopped' => 'danger-500', 'upcoming' => 'gray-300'];
@endphp

{{-- Tahapan permohonan. Inline styles: the panel uses Filament's prebuilt CSS. --}}
<div style="display: flex; flex-direction: column; gap: 0.75rem;">
    <ol aria-label="Tahapan permohonan" style="display: flex; flex-wrap: wrap; gap: 0.5rem 0; margin: 0; padding: 0; list-style: none;">
        @foreach ($steps as $step)
            <li @if ($step['state'] === 'current') aria-current="step" @endif
                style="display: flex; align-items: flex-start; gap: 0.5rem; flex: 1 1 8.5rem; min-width: 8.5rem;">
                <span aria-hidden="true"
                      style="flex: none; display: inline-flex; align-items: center; justify-content: center; width: 1.75rem; height: 1.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; color: #fff; background: rgb(var(--{{ $colors[$step['state']] }}));">
                    @if ($step['state'] === 'done')
                        ✓
                    @elseif ($step['state'] === 'stopped')
                        ✕
                    @else
                        {{ $loop->iteration }}
                    @endif
                </span>
                <span style="min-width: 0; padding-right: 0.5rem;">
                    <span class="text-sm {{ $step['state'] === 'upcoming' ? 'text-gray-500 dark:text-gray-400' : 'font-semibold text-gray-950 dark:text-white' }}" style="display: block;">
                        {{ $step['label'] }}
                        <span class="sr-only">({{ ['done' => 'selesai', 'current' => 'tahap saat ini', 'stopped' => 'berhenti', 'upcoming' => 'belum'][$step['state']] }})</span>
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400" style="display: block;">
                        {{ $step['at']?->translatedFormat('j M Y · H:i') ?? ($step['state'] === 'current' ? 'Sedang berjalan' : '–') }}
                    </span>
                </span>
            </li>
        @endforeach
    </ol>
    @if ($deadline)
        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <x-filament::badge :color="$deadline['color']" icon="heroicon-m-clock">{{ $deadline['label'] }}</x-filament::badge>
            <span class="text-xs text-gray-500 dark:text-gray-400">Target selesai {{ $ticket->estimated_completion_date->translatedFormat('j F Y') }}</span>
        </div>
    @endif
</div>
