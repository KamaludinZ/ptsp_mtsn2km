<?php

namespace App\Filament\Resources\TicketResource\Widgets;

use App\Models\User;
use App\Support\ActiveRoles;
use App\Support\TicketRoleActionStub;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

/**
 * Aksi untuk peran aktif: apa yang boleh dilakukan pada tiket ini dengan
 * peran yang sedang aktif, dan peran mana (yang juga dipegang) untuk aksi
 * lainnya, dengan tombol ganti peran cepat. Hanya untuk petugas multi-peran.
 */
class ActiveRoleActions extends Widget
{
    public ?Model $record = null;

    /** Halaman tiket ini, tujuan kembali setelah ganti peran. */
    public string $returnUrl = '';

    public function mount(): void
    {
        $this->returnUrl = url()->current();
    }

    protected int | string | array $columnSpan = 'full';

    protected static bool $isLazy = false;

    protected static string $view = 'filament.resources.ticket-resource.widgets.active-role-actions';

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
            'actions' => $this->record ? TicketRoleActionStub::for($this->record, $user) : [],
        ];
    }
}
