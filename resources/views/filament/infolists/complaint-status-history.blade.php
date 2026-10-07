@php
    use App\Models\Complaint;

    $logs = $getRecord()->statusLogs()->with('actor:id,name')->get();
@endphp

{{-- Linimasa riwayat status laporan (complaint_status_logs, tidak dapat diubah). Inline styles: Filament's prebuilt CSS. --}}
@if ($logs->isEmpty())
    <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada riwayat status.</p>
@else
    <ol role="list" data-complaint-timeline style="display: grid; gap: 0.75rem;">
        @foreach ($logs as $log)
            <li style="display: grid; grid-template-columns: 1rem 1fr; gap: 0.75rem; align-items: start;">
                <span aria-hidden="true" @class([
                    'rounded-full',
                    'bg-primary-500' => $loop->last,
                    'bg-gray-300 dark:bg-gray-600' => ! $loop->last,
                ]) style="width: 0.75rem; height: 0.75rem; margin-top: 0.3rem;"></span>
                <div style="min-width: 0;">
                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.375rem 0.5rem;">
                        @if ($log->from_status && $log->from_status !== $log->to_status)
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ Complaint::STATUSES[$log->from_status] ?? $log->from_status }} →</span>
                        @endif
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">{{ Complaint::STATUSES[$log->to_status] ?? $log->to_status }}</span>
                        @if ($loop->last)
                            <x-filament::badge color="primary" size="sm">Saat ini</x-filament::badge>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $log->created_at->translatedFormat('j M Y · H:i') }} · {{ $log->actor?->name ?? 'Sistem' }}
                    </p>
                    @if ($log->response)
                        <p class="text-sm text-gray-700 dark:text-gray-200" style="margin-top: 0.25rem; white-space: pre-line;">
                            <span class="font-medium">Tanggapan:</span> {{ $log->response }}
                        </p>
                    @endif
                    @if ($log->internal_note)
                        <p class="text-xs text-gray-600 dark:text-gray-300" style="margin-top: 0.25rem; white-space: pre-line;">
                            <span class="font-medium">Catatan internal:</span> {{ $log->internal_note }}
                        </p>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
@endif
