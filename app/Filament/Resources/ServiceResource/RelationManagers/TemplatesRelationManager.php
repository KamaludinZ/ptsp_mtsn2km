<?php

namespace App\Filament\Resources\ServiceResource\RelationManagers;

use App\Models\ServiceTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/** Template berkas: blank forms applicants download for this service. */
class TemplatesRelationManager extends RelationManager
{
    protected static string $relationship = 'templates';

    protected static ?string $title = 'Template Berkas';

    protected static ?string $modelLabel = 'template';

    public const MIME_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    public function form(Form $form): Form
    {
        return $form->schema(\App\Filament\Resources\ServiceTemplateResource::fields(withService: false))->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama')
            ->defaultSort('sort')
            ->columns([
                Tables\Columns\TextColumn::make('nama')->label('Nama')->wrap()
                    ->url(fn (ServiceTemplate $record) => $record->downloadUrl())
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('file_name')->label('Berkas')->placeholder('–')->toggleable(),
                Tables\Columns\TextColumn::make('versi')->label('Versi')->formatStateUsing(fn (int $state) => 'v' . $state)->badge()->color('gray'),
                Tables\Columns\IconColumn::make('is_required')->label('Wajib')->boolean(),
                Tables\Columns\ToggleColumn::make('is_active')->label('Aktif'),
                Tables\Columns\TextColumn::make('sort')->label('Urutan')->alignEnd(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Tambah template'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->emptyStateHeading('Belum ada template berkas')
            ->emptyStateDescription('Jika diisi, pemohon dapat mengunduh template ini dari halaman layanan dan saat mengajukan.');
    }
}
