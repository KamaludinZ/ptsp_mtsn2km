<?php

namespace App\Filament\Pages;

use App\Support\ActiveRoles;
use App\Support\PanelGuide;
use Filament\Facades\Filament;
use Filament\Pages\Page;

/**
 * Panduan (/cp/panduan): cara memakai setiap menu yang tersedia untuk
 * peran aktif petugas. Dibuka dari tombol Panduan di header.
 */
class Guide extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $title = 'Panduan Menu';

    protected static ?string $slug = 'panduan';

    protected static string $view = 'filament.pages.guide';

    protected static bool $shouldRegisterNavigation = false;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isStaff();
    }

    public function getSubheading(): ?string
    {
        $role = ActiveRoles::for(auth()->user())->firstWhere('active', true);

        return 'Menu di bawah ini mengikuti peran aktif Anda'
            . ($role ? ' (' . $role['label'] . ')' : '')
            . '. Ganti peran di header untuk melihat panduan peran lain.';
    }

    protected function getViewData(): array
    {
        return ['sections' => PanelGuide::sections(Filament::getCurrentPanel())];
    }
}
