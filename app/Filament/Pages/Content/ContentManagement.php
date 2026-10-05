<?php

namespace App\Filament\Pages\Content;

use App\Filament\Resources\FaqResource;
use App\Filament\Resources\PengumumanResource;
use App\Models\Faq;
use App\Models\Pengumuman;
use App\Support\ContentStatus;
use Filament\Pages\Page;

/** Manajemen Konten: announcements and FAQ at a glance, with what needs attention. */
class ContentManagement extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationGroup = 'Manajemen Pengumuman';

    protected static ?string $navigationLabel = 'Manajemen Konten';

    protected static ?string $title = 'Manajemen Konten';

    protected static ?string $slug = 'konten';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.content-management';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasRole('admin');
    }

    public function getSubheading(): ?string
    {
        return 'Pengumuman dan FAQ yang tampil di situs publik.';
    }

    public function summary(): array
    {
        return [
            'pengumuman' => ContentStatus::counts(),
            'faq' => ['aktif' => Faq::where('is_active', true)->count(), 'nonaktif' => Faq::where('is_active', false)->count()],
            'ending' => Pengumuman::query()->where('is_active', true)->whereNotNull('end_date')
                ->whereBetween('end_date', [today(), today()->addDays(7)])->orderBy('end_date')->limit(5)->get(),
            'latest' => Pengumuman::query()->latest('updated_at')->limit(6)->get(),
            'links' => [
                'pengumuman' => PengumumanResource::getUrl(),
                'pengumuman_baru' => PengumumanResource::getUrl('create'),
                'faq' => FaqResource::getUrl(),
                'publik_pengumuman' => route('pengumuman.index'),
                'publik_faq' => route('public.faq'),
            ],
        ];
    }
}
