<?php

namespace App\Filament\Pages;

use App\Support\ActiveRoles;
use App\Support\ActiveRoleToast;
use Filament\Pages\Page;

/**
 * Pilih peran aktif (/cp/pilih-peran): petugas yang memegang lebih dari
 * satu peran memilih satu konteks kerja sebelum memproses layanan. Setelah
 * memilih, ia dibawa ke halaman yang tadi dituju (atau dasbor).
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
            ? 'Akun Anda memegang beberapa peran. Pilih satu peran untuk sesi kerja ini; menu dan tombol aksi akan mengikuti peran yang dipilih.'
            : 'Akun Anda memegang satu peran petugas; peran ini selalu aktif.';
    }

    protected function getViewData(): array
    {
        return ['roles' => ActiveRoles::for(auth()->user())->all()];
    }

    public function choose(string $role): void
    {
        if (ActiveRoleToast::switchTo(auth()->user(), $role) === null) {
            return;
        }

        $this->redirect(session()->pull('url.intended', Dashboard::getUrl(panel: 'admin')), navigate: true);
    }
}
