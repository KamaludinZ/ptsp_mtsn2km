<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkflowResource\Pages;
use App\Models\Workflow;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WorkflowResource extends Resource
{
    use \App\Filament\Concerns\AdminOnly;

    protected static ?string $model = Workflow::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationLabel = 'Alur Kerja';

    protected static ?string $navigationGroup = 'Manajemen Sistem';

    protected static ?string $pluralModelLabel = 'Alur Kerja';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Alur Kerja')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Alur Kerja')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->label('Kode')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        Forms\Components\Select::make('service_type')
                            ->label('Tipe Layanan')
                            ->options([
                                'all' => 'Semua Layanan',
                                'specific' => 'Layanan Tertentu',
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        Forms\Components\Toggle::make('requires_approval')
                            ->label('Memerlukan Persetujuan')
                            ->default(false)
                            ->live(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Deskripsi')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Approval')
                    ->schema([
                        Forms\Components\CheckboxList::make('approval_roles')
                            ->label('Role yang Bisa Menyetujui')
                            ->relationship('roles', 'name')
                            ->visible(fn ($get) => $get('requires_approval'))
                            ->columns(3)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($get) => $get('requires_approval'))
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service_type')
                    ->label('Tipe Layanan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'all' => 'info',
                        'specific' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\IconColumn::make('requires_approval')
                    ->label('Perlu Persetujuan')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_active')
                    ->label('Aktif')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true)),
                Tables\Filters\Filter::make('requires_approval')
                    ->label('Perlu Persetujuan')
                    ->query(fn (Builder $query): Builder => $query->where('requires_approval', true)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkflows::route('/'),
            'create' => Pages\CreateWorkflow::route('/create'),
            'view' => Pages\ViewWorkflow::route('/{record}'),
            'edit' => Pages\EditWorkflow::route('/{record}/edit'),
        ];
    }
}