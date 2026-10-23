@php
    use App\Support\DispositionHistory;
    use App\Support\ServiceDisposition;

    $entries = DispositionHistory::forTicket($getRecord());
    $statuses = DispositionHistory::statuses($getRecord());
@endphp

@if ($statuses)
    {{-- Status disposisi digital per pejabat (Kepala Madrasah + Kepala TU: keduanya) --}}
    <div data-disposition-statuses style="display: grid; gap: 0.75rem; grid-template-columns: repeat(auto-fit, minmax(16rem, 1fr)); margin-bottom: 1rem;">
        @foreach ($statuses as $status)
            <div class="rounded-lg border border-gray-200 dark:border-white/10" style="padding: 0.75rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                    <span class="text-sm font-semibold text-gray-950 dark:text-white">Status disposisi {{ $status['jabatan'] }}</span>
                    <x-filament::badge :color="$status['color']" size="sm">{{ $status['label'] }}</x-filament::badge>
                </div>
                <dl class="text-sm" style="display: grid; grid-template-columns: 5rem 1fr; gap: 0.25rem 0.5rem; margin-top: 0.5rem;">
                    <dt class="text-gray-500 dark:text-gray-400">Nama</dt>
                    <dd class="text-gray-950 dark:text-white">{{ $status['name'] ?? '–' }}</dd>
                    <dt class="text-gray-500 dark:text-gray-400">Jabatan</dt>
                    <dd class="text-gray-950 dark:text-white">{{ $status['jabatan'] }}</dd>
                    <dt class="text-gray-500 dark:text-gray-400">Waktu</dt>
                    <dd class="text-gray-950 dark:text-white">{{ $status['at'] ? $status['at']->translatedFormat('j M Y · H:i') . ' WIB' : '–' }}</dd>
                </dl>
            </div>
        @endforeach
    </div>
@endif

@if (! $entries)
    <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada disposisi pada tiket ini.</p>
@else
    <div style="overflow-x: auto;">
        <table class="w-full text-sm" style="border-collapse: collapse; min-width: 36rem;">
            <thead>
                <tr class="text-xs text-gray-500 dark:text-gray-400" style="text-align: left;">
                    <th style="padding: 0.5rem 0.75rem 0.5rem 0; font-weight: 600;">Waktu</th>
                    <th style="padding: 0.5rem 0.75rem; font-weight: 600;">Pejabat</th>
                    <th style="padding: 0.5rem 0.75rem; font-weight: 600;">Keputusan</th>
                    <th style="padding: 0.5rem 0.75rem; font-weight: 600;">Model tanda tangan</th>
                    <th style="padding: 0.5rem 0 0.5rem 0.75rem; font-weight: 600;">Penerima / instruksi / catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($entries as $entry)
                    <tr class="border-t border-gray-200 dark:border-white/10" style="vertical-align: top;">
                        <td class="text-gray-950 dark:text-white" style="padding: 0.5rem 0.75rem 0.5rem 0; white-space: nowrap;">{{ $entry['at']->translatedFormat('j M Y · H:i') }}</td>
                        <td style="padding: 0.5rem 0.75rem;">
                            <span class="font-semibold text-gray-950 dark:text-white">{{ $entry['actor'] }}</span>
                            @if ($entry['role'])
                                <span class="block text-xs text-gray-500 dark:text-gray-400">sebagai {{ $entry['role'] }}</span>
                            @endif
                        </td>
                        <td style="padding: 0.5rem 0.75rem;">
                            <x-filament::badge :color="DispositionHistory::actionColor($entry['action'])" size="sm">{{ DispositionHistory::action($entry['action']) }}</x-filament::badge>
                        </td>
                        <td class="text-gray-950 dark:text-white" style="padding: 0.5rem 0.75rem;">
                            {{ DispositionHistory::signatureModel($entry['signature_model']) }}
                            @if ($entry['acknowledged_by'])
                                <div class="text-sm text-gray-600 dark:text-gray-400">oleh {{ $entry['acknowledged_by'] }}</div>
                            @endif
                            @if ($entry['signature_file'])
                                <x-filament::link :href="$entry['signature_file']" target="_blank" icon="heroicon-m-document-arrow-down" size="sm">Berkas TTD/TTE</x-filament::link>
                            @endif
                        </td>
                        <td class="text-gray-700 dark:text-gray-300" style="padding: 0.5rem 0 0.5rem 0.75rem; white-space: pre-line;">{{ collect([
                            $entry['recipients'] ? 'Kepada: ' . ServiceDisposition::recipients($entry['recipients']) : null,
                            $entry['instruction'] ? 'Instruksi: ' . $entry['instruction'] : null,
                            $entry['note'],
                        ])->filter()->join("\n") ?: '–' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
