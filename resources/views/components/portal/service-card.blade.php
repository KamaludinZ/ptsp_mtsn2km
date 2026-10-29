@props(['service', 'openRequest' => null, 'forRoles' => []])

{{--
    One service in the portal's catalogue: what it is, how long and how much,
    what to prepare, and how to request it (the request form for an online
    service, the counter for an offline one). The standard opens in the page's
    "standard" action. $service carries requirements_count and
    active_templates_count (ServiceCatalog::offered); $openRequest is the
    applicant's request for it still in progress, if any; $forRoles, which
    of the applicant's roles it is open to (when they hold several).
--}}
@php($online = in_array($service->mode, ['online', 'hybrid'], true))

<x-filament::section {{ $attributes->merge(['data-service-card' => $service->slug]) }}>
    <x-slot name="heading">{{ $service->name }}</x-slot>
    <x-slot name="description">
        {{ $service->code }}@if ($service->categories->isNotEmpty()) · {{ $service->categories->pluck('name')->join(', ') }}@endif
    </x-slot>

    <div style="display: flex; flex-direction: column; gap: 0.75rem; height: 100%;">
        <p class="text-sm text-gray-600 dark:text-gray-300">{{ \App\Support\RichText::plain($service->description, 140) ?: 'Belum ada deskripsi.' }}</p>

        <dl class="text-sm" style="display: grid; grid-template-columns: auto 1fr; gap: 0.25rem 0.75rem;">
            <dt class="text-gray-500 dark:text-gray-400">Waktu proses</dt>
            <dd class="text-gray-950 dark:text-white">{{ $service->processing_time ?: '–' }}</dd>
            <dt class="text-gray-500 dark:text-gray-400">Biaya</dt>
            <dd class="text-gray-950 dark:text-white">{{ $service->fee > 0 ? 'Rp ' . number_format($service->fee, 0, ',', '.') : 'Gratis' }}</dd>
            @if ($forRoles)
                <dt class="text-gray-500 dark:text-gray-400">Untuk</dt>
                <dd class="text-gray-950 dark:text-white" data-service-roles>{{ collect($forRoles)->map(fn ($role) => \App\Support\RoleAccess::roleLabel($role))->join(', ') }}</dd>
            @endif
            <dt class="text-gray-500 dark:text-gray-400">Jalur</dt>
            <dd style="justify-self: start;">
                <x-filament::badge size="sm" :color="$online ? 'success' : 'gray'">{{ \App\Support\ServiceStandard::MODES[$service->mode] ?? 'Online & Offline' }}</x-filament::badge>
            </dd>
        </dl>

        {{-- What to prepare before applying. --}}
        @if ($service->requirements_count || $service->active_templates_count)
            <ul class="text-sm text-gray-600 dark:text-gray-300" style="margin: 0; padding: 0; list-style: none; display: flex; flex-wrap: wrap; gap: 0.25rem 1rem;" data-service-prepare>
                @if ($service->requirements_count)
                    <li style="display: flex; gap: 0.25rem; align-items: center;"><x-filament::icon icon="heroicon-m-paper-clip" class="h-4 w-4 text-gray-400" /> {{ $service->requirements_count }} berkas persyaratan</li>
                @endif
                @if ($service->active_templates_count)
                    <li style="display: flex; gap: 0.25rem; align-items: center;"><x-filament::icon icon="heroicon-m-document-arrow-down" class="h-4 w-4 text-gray-400" /> {{ $service->active_templates_count }} template berkas</li>
                @endif
            </ul>
        @endif

        @if ($openRequest)
            <p class="text-sm text-warning-600 dark:text-warning-400" style="display: flex; gap: 0.25rem; align-items: center;" data-open-request>
                <x-filament::icon icon="heroicon-m-clock" class="h-4 w-4" />
                <span>Permohonan Anda masih berjalan:
                    <x-filament::link :href="\App\Filament\Portal\Resources\TicketResource::getUrl('view', ['record' => $openRequest])" size="sm">{{ $openRequest->ticket_number }}</x-filament::link>
                </span>
            </p>
        @endif

        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem 1rem; align-items: center; margin-top: auto;">
            @if ($online)
                <x-filament::button tag="a" size="sm" icon="heroicon-m-paper-airplane" :href="\App\Filament\Portal\Pages\ApplyService::getUrl(['layanan' => $service->slug])">
                    Ajukan
                </x-filament::button>
            @else
                <span class="text-sm text-gray-500 dark:text-gray-400" style="display: flex; gap: 0.25rem; align-items: center;" data-offline-note>
                    <x-filament::icon icon="heroicon-m-building-storefront" class="h-4 w-4" /> Ajukan di loket PTSP
                </span>
            @endif
            {{ $slot }}
        </div>
    </div>
</x-filament::section>
