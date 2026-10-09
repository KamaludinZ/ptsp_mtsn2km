@php
    use App\Support\LogReader;

    $roleTypes = \App\Filament\Pages\System\ActivityTrail::TYPES;
    $cards = [
        ['key' => 'switches_today', 'label' => 'Perpindahan peran hari ini', 'icon' => 'heroicon-o-arrows-right-left'],
        ['key' => 'open_sessions', 'label' => 'Sesi ganti akun berjalan', 'icon' => 'heroicon-o-user-circle'],
        ['key' => 'sessions_week', 'label' => 'Sesi ganti akun 7 hari', 'icon' => 'heroicon-o-calendar-days'],
        ['key' => 'impersonated_actions', 'label' => 'Aksi lewat ganti akun (7 hari)', 'icon' => 'heroicon-o-exclamation-triangle'],
    ];
@endphp

{{-- Perpindahan peran & ganti akun: khusus administrator (SystemMonitor::canSeeRoleAudit). --}}
@if ($roleAudit)
<x-filament::section heading="Perpindahan peran & ganti akun" icon="heroicon-o-shield-exclamation" data-role-audit
    description="Hanya administrator. Catatan tidak dapat diubah atau dihapus.">
    <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(11rem,1fr))">
        @foreach ($cards as $card)
            <div class="rounded-lg ring-1 ring-gray-950/5 dark:ring-white/10" style="padding:.75rem 1rem" data-role-audit-card="{{ $card['key'] }}">
                <div class="text-xs text-gray-500 dark:text-gray-400" style="display:flex;align-items:center;gap:.375rem">
                    <x-filament::icon :icon="$card['icon']" class="h-4 w-4" />
                    {{ $card['label'] }}
                </div>
                <div @class([
                    'text-2xl font-semibold tabular-nums',
                    'text-warning-600 dark:text-warning-400' => $card['key'] === 'open_sessions' && $roleAudit['summary'][$card['key']] > 0,
                    'text-gray-950 dark:text-white' => ! ($card['key'] === 'open_sessions' && $roleAudit['summary'][$card['key']] > 0),
                ])>{{ $roleAudit['summary'][$card['key']] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Saringan & pencarian (berlaku untuk rekap dan catatan terbaru) --}}
    <div data-role-audit-filters style="display:grid;gap:.75rem;grid-template-columns:repeat(auto-fit,minmax(10rem,1fr));margin-top:1rem">
        <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass" style="grid-column:span 2 / span 2">
            <x-filament::input type="search" wire:model.live.debounce.400ms="roleAuditFilters.cari" placeholder="Cari pengguna atau kegiatan" aria-label="Cari perpindahan peran dan ganti akun" />
        </x-filament::input.wrapper>
        <x-filament::input.wrapper>
            <x-filament::input.select wire:model.live="roleAuditFilters.pengguna" aria-label="Pengguna">
                <option value="">Semua pengguna</option>
                @foreach ($roleAudit['options']['pengguna'] as $name)
                    <option value="{{ $name }}">{{ $name }}</option>
                @endforeach
            </x-filament::input.select>
        </x-filament::input.wrapper>
        <x-filament::input.wrapper>
            <x-filament::input.select wire:model.live="roleAuditFilters.peran" aria-label="Peran aktif">
                <option value="">Semua peran</option>
                @foreach ($roleAudit['options']['peran'] as $role)
                    <option value="{{ $role }}">{{ $role }}</option>
                @endforeach
            </x-filament::input.select>
        </x-filament::input.wrapper>
        <x-filament::input.wrapper>
            <x-filament::input.select wire:model.live="roleAuditFilters.jenis" aria-label="Jenis">
                <option value="">Semua jenis</option>
                <option value="role_switch">Perpindahan peran</option>
                <option value="impersonation">Ganti akun</option>
            </x-filament::input.select>
        </x-filament::input.wrapper>
        <x-filament::input.wrapper>
            <x-filament::input type="date" wire:model.live="roleAuditFilters.dari" aria-label="Dari tanggal" />
        </x-filament::input.wrapper>
        <x-filament::input.wrapper>
            <x-filament::input type="date" wire:model.live="roleAuditFilters.sampai" aria-label="Sampai tanggal" />
        </x-filament::input.wrapper>
    </div>
    @if ($roleAudit['filtering'])
        <x-filament::link tag="button" wire:click="resetRoleAuditFilters" icon="heroicon-m-x-mark" color="gray" size="sm" style="margin-top:.5rem">
            Hapus saringan
        </x-filament::link>
    @endif

    {{-- Rekap per pengguna–peran aktif, 7 hari --}}
    <div style="overflow-x:auto;margin-top:1rem">
        <table class="w-full text-sm" style="border-collapse:collapse;min-width:40rem" data-role-audit-recap>
            <caption class="text-left text-xs font-medium text-gray-500 dark:text-gray-400" style="padding-bottom:.375rem">Rekap per pengguna & peran aktif, 7 hari terakhir</caption>
            <thead>
                <tr class="text-left text-gray-500 dark:text-gray-400">
                    <th scope="col" style="padding:.375rem .75rem .375rem 0;font-weight:500">Pengguna</th>
                    <th scope="col" style="padding:.375rem .75rem;font-weight:500">Peran aktif</th>
                    <th scope="col" style="padding:.375rem .75rem;font-weight:500;text-align:right">Pindah peran</th>
                    <th scope="col" style="padding:.375rem .75rem;font-weight:500;text-align:right">Sesi ganti akun</th>
                    <th scope="col" style="padding:.375rem .75rem;font-weight:500;text-align:right">Aksi tiket</th>
                    <th scope="col" style="padding:.375rem .75rem;font-weight:500;text-align:right">Lewat ganti akun</th>
                    <th scope="col" style="padding:.375rem 0 .375rem .75rem;font-weight:500">Terakhir</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($roleAudit['recap'] as $row)
                    <tr class="border-t border-gray-200 dark:border-white/10">
                        <td class="font-semibold text-gray-950 dark:text-white" style="padding:.375rem .75rem .375rem 0">{{ $row['user'] }}</td>
                        <td class="text-gray-700 dark:text-gray-200" style="padding:.375rem .75rem">{{ $row['role'] }}</td>
                        @foreach (['switches', 'sessions', 'ticket_actions', 'impersonated'] as $key)
                            <td @class(['tabular-nums', 'text-warning-600 dark:text-warning-400 font-semibold' => $key === 'impersonated' && $row[$key] > 0, 'text-gray-700 dark:text-gray-200' => ! ($key === 'impersonated' && $row[$key] > 0)])
                                style="padding:.375rem .75rem;text-align:right">{{ $row[$key] ?: '–' }}</td>
                        @endforeach
                        <td class="text-gray-500 dark:text-gray-400" style="padding:.375rem 0 .375rem .75rem;white-space:nowrap">{{ $row['last']->translatedFormat('j M, H.i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-gray-500 dark:text-gray-400" style="padding:.5rem 0">Belum ada aktivitas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <ol role="list" style="margin-top:1rem">
        @forelse ($roleAudit['latest'] as $entry)
            <li class="{{ $loop->first ? '' : 'border-t border-gray-200 dark:border-white/10' }}" style="display:flex;flex-wrap:wrap;gap:.25rem .75rem;padding:.5rem 0;align-items:baseline">
                <span class="text-xs text-gray-500 dark:text-gray-400" style="min-width:7.5rem">{{ $entry['at']->translatedFormat('j M Y H:i') }}</span>
                <x-filament::badge :color="$roleTypes[$entry['type']]['color']" size="sm">{{ $roleTypes[$entry['type']]['label'] }}</x-filament::badge>
                <span class="text-sm font-semibold text-gray-950 dark:text-white">{{ $entry['user'] }}</span>
                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $entry['description'] }}</span>
            </li>
        @empty
            <li class="text-sm text-gray-500 dark:text-gray-400">Belum ada perpindahan peran atau sesi ganti akun.</li>
        @endforelse
    </ol>

    <x-filament::link :href="\App\Filament\Pages\System\ActivityTrail::getUrl()" icon="heroicon-m-finger-print" size="sm" style="margin-top:.5rem">
        Buka Rekam Jejak Aktivitas
    </x-filament::link>
</x-filament::section>
@endif

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
            @if ($accountSummary = \App\Support\UserAccountAudit::summary($activity))
                <x-filament::badge :color="$activity->description === \App\Support\UserAccountAudit::IMPORT_FAILED ? 'danger' : 'info'" size="sm" icon="heroicon-m-users" data-account-audit>{{ $accountSummary }}</x-filament::badge>
            @endif
            @if ($activity->subject_type)
                <x-filament::badge color="gray" size="sm">{{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}</x-filament::badge>
            @endif
            @if ($importDetail = \App\Support\UserAccountAudit::importDetail($activity))
                <details data-account-import-detail style="flex-basis:100%">
                    <summary class="text-sm text-primary-600 dark:text-primary-400" style="cursor:pointer">Rincian import</summary>
                    <dl class="text-sm" style="display:grid;grid-template-columns:auto 1fr;gap:.125rem .75rem;margin:.5rem 0">
                        @foreach ($importDetail['summary'] as $label => $value)
                            <dt class="text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                            <dd class="text-gray-950 dark:text-white">{{ $value }}</dd>
                        @endforeach
                    </dl>
                    @if ($importDetail['failed'])
                        <table class="text-sm" style="border-collapse:collapse">
                            <caption class="sr-only">Baris gagal</caption>
                            <thead>
                                <tr class="text-gray-950 dark:text-white" style="text-align:left">
                                    <th scope="col" style="padding:.25rem .75rem .25rem 0">Baris</th>
                                    <th scope="col" style="padding:.25rem 0">Alasan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($importDetail['failed'] as $row)
                                    <tr class="border-t border-gray-100 dark:border-white/5 text-gray-700 dark:text-gray-300">
                                        <td style="padding:.25rem .75rem .25rem 0;font-variant-numeric:tabular-nums">{{ $row['row'] }}</td>
                                        <td style="padding:.25rem 0">{{ $row['reason'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </details>
            @endif
        </div>
    @empty
        <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada aktivitas yang cocok.</p>
    @endforelse

    <x-filament::link :href="\App\Filament\Resources\ServiceHistoryResource::getUrl('index')" icon="heroicon-m-clock" size="sm" style="margin-top:.75rem">
        Buka Riwayat Layanan & Audit Trail per tiket
    </x-filament::link>
</x-filament::section>
