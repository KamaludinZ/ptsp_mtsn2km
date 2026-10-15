<?php

namespace App\Filament\Pages;

use App\Support\ActiveRoles;
use App\Support\ActiveRoleToast;
use Filament\Pages\Page;

/**
 * Semua peran (/cp/pilih-peran): petugas yang memegang lebih dari satu peran
 * melihat semua perannya dan bisa berganti konteks kerja. Peran pemohon
 * yang juga ia pegang (mis. Guru) membuka portal pemohon. Setelah memilih,
 * ia dibawa ke dasbor (atau halaman yang tadi dituju).
 */
class ChooseActiveRole extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $title = 'Pilih Peran Aktif';

    protected static ?string $slug = 'pilih-peran';

    protected static string $view = 'filament.pages.choose-active-role';

    protected static bool $shouldRegisterNavigation = false;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isStaff();
    }

    public function getSubheading(): ?string
    {
        return ActiveRoles::for(auth()->user())->count() > 1
            ? 'Akun Anda memegang beberapa peran. Satu peran sudah aktif otomatis; pilih peran lain bila ingin berganti. Menu dan tombol aksi mengikuti peran yang aktif.'
            : 'Akun Anda memegang satu peran petugas; peran ini selalu aktif.';
    }

    protected function getViewData(): array
    {
        return [
            'roles' => ActiveRoles::for(auth()->user())->all(),
            'portalRoles' => ActiveRoles::portalRolesFor(auth()->user())->all(),
            'portalUrl' => \App\Filament\Portal\Pages\Dashboard::getUrl(panel: 'portal'),
        ];
    }

    public function choose(string $role): void
    {
        if (ActiveRoleToast::switchTo(auth()->user(), $role) === null) {
            return;
        }

        // Muat ulang penuh: header /cp (mode SPA) baru mengikuti peran baru setelah dimuat ulang.
        $this->redirect(session()->pull('url.intended', Dashboard::getUrl(panel: 'admin')));
    }
}
