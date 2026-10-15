<?php

namespace App\Livewire;

use App\Filament\Pages\ChooseActiveRole;
use App\Filament\Pages\Dashboard;
use App\Support\ActiveRoles;
use App\Support\ActiveRoleToast;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Penanda + menu ganti peran cepat di header /cp. Berpindah peran tetap
 * di halaman yang sedang dibuka, dimuat ulang penuh supaya header, menu
 * dan tombol aksi langsung mengikuti peran baru (header /cp dipertahankan
 * antarhalaman oleh mode SPA, jadi tidak ikut berganti tanpa muat ulang).
 */
class ActiveRoleSwitcher extends Component
{
    /** Juga dipanggil dari aksi "Kembali ke ..." pada toast perpindahan peran. */
    #[On(ActiveRoleToast::UNDO_EVENT)]
    public function switchTo(string $role, ?string $page = null): void
    {
        if (ActiveRoleToast::switchTo(auth()->user(), $role)) {
            $this->redirect(self::returnUrl($page ?? request()->headers->get('referer')));
        }
    }

    /**
     * Halaman /cp tempat menu dipakai (dikirim tombolnya, atau Referer
     * permintaan Livewire); selain itu, atau bila di luar /cp, dasbor.
     */
    public static function returnUrl(?string $page): string
    {
        $page = (string) $page;
        $panelRoot = rtrim(Dashboard::getUrl(panel: 'admin'), '/');

        return $page === $panelRoot || str_starts_with($page, $panelRoot . '/') || str_starts_with($page, $panelRoot . '?')
            ? $page
            : Dashboard::getUrl(panel: 'admin');
    }

    public function render()
    {
        $roles = ActiveRoles::for(auth()->user());

        return view('livewire.active-role-switcher', [
            'roles' => $roles->all(),
            'portalRoles' => ActiveRoles::portalRolesFor(auth()->user())->all(),
            'portalUrl' => \App\Filament\Portal\Pages\Dashboard::getUrl(panel: 'portal'),
            'current' => $roles->firstWhere('active', true),
            'pickerUrl' => ChooseActiveRole::getUrl(panel: 'admin'),
        ]);
    }
}
