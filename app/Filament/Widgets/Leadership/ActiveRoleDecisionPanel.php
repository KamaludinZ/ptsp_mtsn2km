<?php

namespace App\Filament\Widgets\Leadership;

use App\Models\Ticket;
use App\Models\User;
use App\Services\DispositionAuthority;
use App\Support\ActiveRoles;
use App\Support\RoleAccess;
use Filament\Widgets\Widget;

/**
 * Panel keputusan peran aktif di Antrean Disposisi: atas nama peran apa
 * pimpinan memutuskan, berapa permohonan menunggu di peran itu, dan
 * berapa yang menunggu peran pimpinan lain yang juga dipegangnya (dengan
 * tombol ganti peran cepat).
 */
class ActiveRoleDecisionPanel extends Widget
{
    protected static bool $isDiscovered = false;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'filament.widgets.leadership.active-role-decision-panel';

    public string $returnUrl = '';

    public function mount(): void
    {
        $this->returnUrl = url()->current();
    }

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user instanceof User && ActiveRoles::for($user)->count() > 1;
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        $active = ActiveRoles::inContext($user);
        $awaiting = Ticket::with('service')->awaitingApproval()->get();
        $mine = $awaiting->filter(fn (Ticket $ticket) => $user->can('approve', $ticket))->count();
        // Waiting for a leadership role the user holds but has not made active.
        $elsewhere = $awaiting->filter(fn (Ticket $ticket) => ! $user->can('approve', $ticket)
            && app(DispositionAuthority::class)->canDispose($user, $ticket))->count();
        $otherRole = ActiveRoles::for($user)->pluck('name')
            ->first(fn (string $role) => $role !== $active && in_array($role, RoleAccess::LEADERSHIP, true));

        return [
            'active' => $active ? ActiveRoles::row($active) : null,
            'isLeader' => $active !== null && in_array($active, RoleAccess::LEADERSHIP, true),
            'mine' => $mine,
            'elsewhere' => $elsewhere,
            'otherRole' => $otherRole ? ActiveRoles::row($otherRole) : null,
        ];
    }
}
