<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use App\Models\Service;
use App\Services\FrontDeskService;
use App\Support\ServiceDisposition;
use App\Support\TicketLabels;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

/** Detail layanan: syarat, alur, hasil, template berkas and disposition, as applicants and staff see them. */
class ViewService extends ViewRecord
{
    protected static string $resource = ServiceResource::class;

    protected function resolveRecord(int | string $key): Service
    {
        return parent::resolveRecord($key)->load(['categories:id,name', 'workflow.steps' => fn ($q) => $q->orderBy('step_number'), 'templates' => fn ($q) => $q->orderBy('sort')]);
    }

    public function getSubheading(): ?string
    {
        return 'Syarat dan alur layanan seperti yang dilihat pemohon di portal.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('portal')
                ->label('Lihat di portal')
                ->icon('heroicon-m-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn (Service $record) => route('onlineportal.service.detail', $record->slug))
                ->openUrlInNewTab()
                ->visible(fn (Service $record) => $record->is_active && $record->slug),
            ServiceResource::activationAction(Actions\Action::class),
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $html = fn (?string $state) => filled(\App\Support\RichText::plain($state)) ? \App\Support\RichText::render($state) : null;

        return $infolist->schema([
            Section::make('Ringkasan')
                ->icon('heroicon-o-information-circle')
                ->columns(['default' => 1, 'sm' => 2, 'lg' => 4])
                ->schema([
                    TextEntry::make('code')->label('Kode'),
                    TextEntry::make('categories.name')->label('Kategori')->badge()->placeholder('–'),
                    TextEntry::make('mode')->label('Jalur')->badge()->formatStateUsing(fn (?string $state) => TicketLabels::mode($state)),
                    TextEntry::make('is_active')->label('Status')->badge()
                        ->formatStateUsing(fn (bool $state) => $state ? 'Aktif' : 'Nonaktif')
                        ->color(fn (bool $state) => $state ? 'success' : 'gray'),
                    TextEntry::make('processing_time')->label('Standar waktu')->placeholder('–'),
                    TextEntry::make('fee')->label('Biaya')
                        ->formatStateUsing(fn ($state) => (float) $state > 0 ? 'Rp ' . number_format((float) $state, 0, ',', '.') : 'Gratis')
                        ->default(0),
                    TextEntry::make('user_types_allowed')->label('Dapat diajukan oleh')
                        ->state(fn (Service $record) => blank($record->user_types_allowed) ? 'Semua pemohon'
                            : collect($record->user_types_allowed)->map(fn (string $type) => FrontDeskService::APPLICANT_TYPES[$type] ?? $type)->join(', '))
                        ->columnSpan(['lg' => 2]),
                    TextEntry::make('description')->label('Deskripsi')->formatStateUsing($html)->prose()->placeholder('–')->columnSpanFull(),
                ]),
            Grid::make(['default' => 1, 'lg' => 2])->schema([
                Section::make('Persyaratan')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                        TextEntry::make('requirements')->hiddenLabel()->formatStateUsing($html)->prose()->placeholder('Belum ada persyaratan tertulis.'),
                        RepeatableEntry::make('requirements_list')
                            ->label('Berkas persyaratan')
                            ->state(fn (Service $record) => $record->requirements()->get()->map(fn ($r) => [
                                'nama' => $r->requirement_name, 'wajib' => $r->is_required ? 'Wajib' : 'Opsional', 'keterangan' => $r->description,
                            ])->all())
                            ->visible(fn (Service $record) => $record->requirements()->get()->isNotEmpty())
                            ->contained(false)
                            ->schema([
                                TextEntry::make('nama')->hiddenLabel()->weight('semibold'),
                                TextEntry::make('wajib')->hiddenLabel()->badge()->color(fn (string $state) => $state === 'Wajib' ? 'danger' : 'gray'),
                                TextEntry::make('keterangan')->hiddenLabel()->placeholder(''),
                            ])->columns(3),
                    ]),
                Section::make('Alur layanan')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->schema([
                        TextEntry::make('mechanism')->hiddenLabel()->formatStateUsing($html)->prose()->placeholder('Belum ada alur tertulis.'),
                        RepeatableEntry::make('workflow_steps')
                            ->label('Langkah workflow')
                            ->state(fn (Service $record) => $record->workflow?->steps->map(fn ($step) => [
                                'langkah' => $step->step_number . '. ' . $step->name . ($step->is_optional ? ' (opsional)' : ''),
                                'durasi' => '± ' . $step->estimated_duration_days . ' hari',
                                'keterangan' => $step->description,
                            ])->all() ?? [])
                            ->visible(fn (Service $record) => (bool) $record->workflow?->steps->isNotEmpty())
                            ->contained(false)
                            ->schema([
                                TextEntry::make('langkah')->hiddenLabel()->weight('semibold'),
                                TextEntry::make('durasi')->hiddenLabel()->color('gray'),
                                TextEntry::make('keterangan')->hiddenLabel()->placeholder('')->columnSpanFull(),
                            ])->columns(2),
                    ]),
            ]),
            Grid::make(['default' => 1, 'lg' => 2])->schema([
                Section::make('Hasil & penanganan keluhan')
                    ->icon('heroicon-o-document-check')
                    ->schema([
                        TextEntry::make('is_digital_product')->label('Bentuk hasil')
                            ->formatStateUsing(fn (bool $state) => $state ? 'Berkas digital (diunduh pemohon)' : 'Diambil di loket'),
                        TextEntry::make('product')->label('Produk layanan')->formatStateUsing($html)->prose()->placeholder('–'),
                        TextEntry::make('complaint_handling')->label('Penanganan pengaduan')->formatStateUsing($html)->prose()->placeholder('–'),
                    ]),
                Section::make('Template berkas & disposisi')
                    ->icon('heroicon-o-document-duplicate')
                    ->schema([
                        RepeatableEntry::make('templates')
                            ->label('Template berkas')
                            ->contained(false)
                            ->placeholder('Belum ada template berkas.')
                            ->schema([
                                TextEntry::make('nama')->hiddenLabel()->icon('heroicon-m-document-arrow-down')->color('primary')
                                    ->url(fn ($record) => $record->downloadUrl())->openUrlInNewTab()
                                    ->helperText(fn ($record) => collect([$record->fileInfo(), $record->is_required ? 'wajib dilengkapi' : null])->filter()->join(' · ')),
                            ]),
                        TextEntry::make('disposition_mode')->label('Mode disposisi')->formatStateUsing(fn (string $state) => ServiceDisposition::mode($state)),
                        TextEntry::make('disposition_roles')->label('Diteruskan kepada')
                            ->state(fn (Service $record) => $record->disposition_roles ? ServiceDisposition::recipients($record->disposition_roles) : null)
                            ->placeholder('Dipilih pimpinan saat disposisi'),
                        TextEntry::make('signature_recommendation')->label('Anjuran tanda tangan')
                            ->formatStateUsing(fn (?string $state) => ServiceDisposition::SIGNATURES[$state] ?? $state)
                            ->placeholder('Tidak ada anjuran'),
                    ]),
            ]),
        ]);
    }
}
