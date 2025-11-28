<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengumumanResource\Pages;
use App\Models\Pengumuman;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PengumumanResource extends Resource
{
    protected static ?string $model = Pengumuman::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Pengumuman';

    protected static ?string $navigationGroup = 'Manajemen Pengumuman';

    protected static ?string $pluralModelLabel = 'Pengumuman';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengumuman')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('summary')
                            ->label('Ringkasan')
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('content')
                            ->label('Isi Pengumuman')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('author')
                            ->label('Penulis')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Tanggal dan Status')
                    ->schema([
                        Forms\Components\DatePicker::make('publish_date')
                            ->label('Tanggal Publikasi')
                            ->default(now())
                            ->required(),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Berakhir'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        Forms\Components\Select::make('priority')
                            ->label('Prioritas')
                            ->options([
                                'low' => 'Rendah',
                                'normal' => 'Normal',
                                'high' => 'Tinggi',
                                'urgent' => 'Darurat',
                            ])
                            ->default('normal'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Target Audience')
                    ->schema([
                        Forms\Components\CheckboxList::make('user_types')
                            ->label('Tipe Pengguna')
                            ->options([
                                'guru' => 'Guru',
                                'pegawai' => 'Pegawai',
                                'siswa' => 'Siswa',
                                'walimurid' => 'Wali Murid',
                                'alumni' => 'Alumni',
                                'instansi' => 'Instansi',
                                'umum' => 'Umum',
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('File Lampiran')
                    ->schema([
                        Forms\Components\FileUpload::make('attachment')
                            ->label('File Lampiran')
                            ->directory('pengumuman-attachments')
                            ->preserveFilenames()
                            ->maxSize(10240) // 10MB
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('publish_date')
                    ->label('Tanggal Publikasi')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Tanggal Berakhir')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioritas')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'info',
                        'normal' => 'gray',
                        'high' => 'warning',
                        'urgent' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'low' => 'Rendah',
                        'normal' => 'Normal',
                        'high' => 'Tinggi',
                        'urgent' => 'Darurat',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('author')
                    ->label('Penulis')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_active')
                    ->label('Aktif')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true)),
                Tables\Filters\SelectFilter::make('priority')
                    ->label('Prioritas')
                    ->options([
                        'low' => 'Rendah',
                        'normal' => 'Normal',
                        'high' => 'Tinggi',
                        'urgent' => 'Darurat',
                    ]),
                Tables\Filters\Filter::make('publish_date')
                    ->label('Tanggal Publikasi')
                    ->date(),
                Tables\Filters\Filter::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->date(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('publish_date', 'desc');
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
            'index' => Pages\ListPengumumen::route('/'),
            'create' => Pages\CreatePengumuman::route('/create'),
            'view' => Pages\ViewPengumuman::route('/{record}'),
            'edit' => Pages\EditPengumuman::route('/{record}/edit'),
        ];
    }
}