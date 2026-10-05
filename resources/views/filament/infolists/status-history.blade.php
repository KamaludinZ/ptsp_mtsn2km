@php
    use App\Support\TicketLabels;
    use App\Support\TicketProgress;

    $changes = TicketProgress::statusHistory($getRecord());
@endphp

{{-- Riwayat status: status changes only, with time spent in each. Inline styles: Filament's prebuilt CSS. --}}
@if (empty($changes))
    <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada perubahan status.</p>
@else
    <div style="overflow-x: auto;">
        <table class="text-sm" style="width: 100%; border-collapse: collapse; min-width: 34rem;">
            <thead>
                <tr class="text-xs text-gray-500 dark:text-gray-400" style="text-align: left;">
                    <th scope="col" style="padding: 0.375rem 0.75rem 0.375rem 0; font-weight: 600;">Status</th>
                    <th scope="col" style="padding: 0.375rem 0.75rem; font-weight: 600;">Waktu</th>
                    <th scope="col" style="padding: 0.375rem 0.75rem; font-weight: 600;">Oleh</th>
                    <th scope="col" style="padding: 0.375rem 0 0.375rem 0.75rem; font-weight: 600;">Lama di status ini</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($changes as $change)
                    <tr class="border-t border-gray-200 dark:border-white/10" style="vertical-align: top;">
                        <td style="padding: 0.5rem 0.75rem 0.5rem 0;">
                            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.375rem;">
                                @if ($change['from'] && $change['from'] !== $change['to'])
                                    <span class="text-gray-500 dark:text-gray-400">{{ TicketLabels::status($change['from']) }} →</span>
                                @endif
                                <x-filament::badge :color="TicketLabels::statusColor($change['to'])" size="sm">{{ TicketLabels::status($change['to']) }}</x-filament::badge>
                                @if ($change['current'])
                                    <span class="text-xs font-semibold text-primary-600 dark:text-primary-400">Saat ini</span>
                                @endif
                            </div>
                            @if ($change['notes'])
                                <p class="text-xs text-gray-600 dark:text-gray-300" style="margin-top: 0.25rem; white-space: pre-line;">{{ $change['notes'] }}</p>
                            @endif
                        </td>
                        <td class="text-gray-700 dark:text-gray-200" style="padding: 0.5rem 0.75rem; white-space: nowrap;">{{ $change['at']->translatedFormat('j M Y · H:i') }}</td>
                        <td class="text-gray-700 dark:text-gray-200" style="padding: 0.5rem 0.75rem;">{{ $change['actor'] }}</td>
                        <td class="text-gray-700 dark:text-gray-200" style="padding: 0.5rem 0 0.5rem 0.75rem; white-space: nowrap;">{{ $change['duration'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
