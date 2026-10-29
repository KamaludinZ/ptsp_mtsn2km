<?php

namespace App\Filament\Resources\SuratKeputusanResource\Widgets;

use App\Models\User;
use App\Support\ActiveRoles;
use App\Support\RegisterSkAccess;
use Filament\Widgets\Widget;

/**
 * Akses untuk peran aktif on the SK register: what the active role may do
 * here and, for the rest (deleting is for Administrator), a role the officer
 * also holds, with the quick role switch. Multi-role officers only.
 */
class RegisterSkRoleAccess extends Widget
{
    /** The register, to come back to after switching roles. */
    public string $returnUrl = '';

    public function mount(): void
    {
        $this->returnUrl = url()->current();
    }

    protected int | string | array $columnSpan = 'full';

    protected static bool $isLazy = false;

    protected static string $view = 'filament.resources.surat-keputusan-resource.widgets.register-sk-role-access';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user instanceof User && ActiveRoles::for($user)->count() > 1;
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        $active = ActiveRoles::inContext($user);

        return [
            'active' => $active ? ActiveRoles::row($active) : null,
            'actions' => RegisterSkAccess::actionsFor($user),
        ];
    }
}
