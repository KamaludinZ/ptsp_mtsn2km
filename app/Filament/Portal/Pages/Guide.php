<?php

namespace App\Filament\Portal\Pages;

use App\Support\PanelGuide;
use Filament\Facades\Filament;
use Filament\Pages\Page;

/** Panduan (/portal/panduan): cara memakai menu portal pemohon. */
class Guide extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $title = 'Panduan Menu';

    protected static ?string $slug = 'panduan';

    protected static string $view = 'filament.pages.guide';

    protected static bool $shouldRegisterNavigation = false;

    public function getSubheading(): ?string
    {
        return 'Cara memakai setiap menu portal untuk mengajukan dan memantau permohonan layanan.';
    }

    protected function getViewData(): array
    {
        return ['sections' => PanelGuide::sections(Filament::getCurrentPanel())];
    }
}
