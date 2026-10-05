@php
    use App\Support\RoleAccess;

    // Group "Back Office — lihat tiket" style labels by their area.
    $groups = collect($permissions)->groupBy(fn ($label) => trim(explode('—', $label)[0]), preserveKeys: true);
    $has = $roles->mapWithKeys(fn ($role) => [$role->id => $role->permissions->pluck('name')->flip()]);
@endphp

{{-- Peta izin: roles × permissions. Inline styles: the panel uses Filament's prebuilt CSS. --}}
<div style="overflow: auto; max-height: 70vh;">
    <table class="text-sm" style="border-collapse: separate; border-spacing: 0; min-width: 100%;">
        <caption class="sr-only">Peta izin per peran</caption>
        <thead>
            <tr>
                <th scope="col" class="bg-white dark:bg-gray-900 text-gray-950 dark:text-white" style="position: sticky; top: 0; left: 0; z-index: 2; text-align: left; padding: 0.5rem 0.75rem; min-width: 16rem;">Izin</th>
                @foreach ($roles as $role)
                    <th scope="col" class="bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200" style="position: sticky; top: 0; z-index: 1; padding: 0.5rem; font-weight: 600; white-space: nowrap; writing-mode: vertical-rl; transform: rotate(180deg); text-align: left;">
                        {{ RoleAccess::roleLabel($role->name) }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($groups as $group => $items)
                <tr>
                    <th scope="rowgroup" colspan="{{ $roles->count() + 1 }}" class="bg-gray-50 dark:bg-white/5 text-gray-950 dark:text-white" style="text-align: left; padding: 0.375rem 0.75rem; font-weight: 600;">{{ $group }}</th>
                </tr>
                @foreach ($items as $name => $label)
                    <tr class="border-t border-gray-100 dark:border-white/5">
                        <th scope="row" class="bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200" style="position: sticky; left: 0; text-align: left; padding: 0.375rem 0.75rem; font-weight: 400;">
                            {{ trim(explode('—', $label)[1] ?? $label) }}
                        </th>
                        @foreach ($roles as $role)
                            <td style="text-align: center; padding: 0.375rem;">
                                @if (isset($has[$role->id][$name]))
                                    <span class="text-success-600 dark:text-success-400" title="{{ RoleAccess::roleLabel($role->name) }}: {{ $label }}">✓</span>
                                    <span class="sr-only">ya</span>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600" aria-hidden="true">·</span>
                                    <span class="sr-only">tidak</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>
