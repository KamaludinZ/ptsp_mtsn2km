<?php

namespace App\Livewire;

use App\Filament\Pages\ChooseActiveRole;
use App\Support\ActiveRoles;
use App\Support\ActiveRoleToast;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Penanda + menu ganti peran cepat di header /cp. Berpindah peran tetap
 * di halaman yang sedang dibuka.
 */
class ActiveRoleSwitcher extends Component
{
    /** Halaman tempat menu dibuka; dibuka lagi setelah berpindah peran. */
    public string $returnUrl = '';

    public function mount(): void
    {
        $this->returnUrl = url()->current();
    }

    /** Juga dipanggil dari aksi "Kembali ke ..." pada toast perpindahan peran. */
    #[On(ActiveRoleToast::UNDO_EVENT)]
    public function switchTo(string $role): void
    {
        if (ActiveRoleToast::switchTo(auth()->user(), $role)) {
            $this->redirect($this->returnUrl ?: ChooseActiveRole::getUrl(panel: 'admin'), navigate: true);
        }
    }

    public function render()
    {
        $roles = ActiveRoles::for(auth()->user());

        return view('livewire.active-role-switcher', [
            'roles' => $roles->all(),
            'current' => $roles->firstWhere('active', true),
            'pickerUrl' => ChooseActiveRole::getUrl(panel: 'admin'),
        ]);
    }
}
