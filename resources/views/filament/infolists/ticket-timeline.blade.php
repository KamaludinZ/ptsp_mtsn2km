@php
    use App\Support\TicketLabels;

    $logs = $getRecord()->logs()->with('performer:id,name')->oldest()->oldest('id')->get();
@endphp

{{-- Timeline riwayat layanan (oldest first). Inline styles: the panel uses Filament's prebuilt CSS. --}}
@if ($logs->isEmpty())
    <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada riwayat.</p>
@else
    <ol style="position: relative; margin: 0; padding: 0; list-style: none;">
        @foreach ($logs as $log)
            @php($color = $log->to_status ? TicketLabels::statusColor($log->to_status) : 'gray')
            <li style="position: relative; display: flex; gap: 0.75rem; padding-bottom: {{ $loop->last ? '0' : '1.25rem' }};">
                @unless ($loop->last)
                    <span aria-hidden="true" class="bg-gray-200 dark:bg-white/10"
                          style="position: absolute; left: 0.5625rem; top: 1.375rem; bottom: 0; width: 2px;"></span>
                @endunless
                <span aria-hidden="true" class="ring-4 ring-white dark:ring-gray-900"
                      style="flex: none; margin-top: 0.25rem; width: 1.25rem; height: 1.25rem; border-radius: 9999px; background: rgb(var(--{{ $color === 'gray' ? 'gray-400' : $color . '-500' }}));"></span>
                <div style="min-width: 0; flex: 1;">
                    <div style="display: flex; flex-wrap: wrap; align-items: baseline; column-gap: 0.5rem;">
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">{{ TicketLabels::logAction($log->action) }}</span>
                        @if ($log->to_status)
                            <x-filament::badge :color="$color" size="sm">
                                {{ $log->statusChange() }}
                            </x-filament::badge>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $log->created_at->translatedFormat('l, j F Y · H:i') }} · {{ $log->performer?->name ?? 'Sistem' }}
                    </p>
                    @if ($log->notes)
                        <p class="text-sm text-gray-700 dark:text-gray-300" style="margin-top: 0.25rem; white-space: pre-line;">{{ $log->notes }}</p>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
@endif
