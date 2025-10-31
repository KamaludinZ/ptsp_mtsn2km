<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeroSliderResource\Pages;
use App\Models\HeroSlider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class HeroSliderResource extends Resource
{
    protected static ?string $model = HeroSlider::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?string $label = 'Hero Slider';

    protected static ?string $pluralLabel = 'Hero Slider';

    protected static ?string $slug = 'hero-sliders';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Konten Slider')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Utama')
                            ->helperText('Judul utama yang akan ditampilkan di slider'),

                        Forms\Components\TextInput::make('subtitle')
                            ->label('Subjudul')
                            ->helperText('Subjudul di bawah judul utama'),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->helperText('Deskripsi singkat tentang slider'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Gambar & Overlay')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label('Gambar Slider')
                            ->image()
                            ->imageEditor()
                            ->directory('hero-sliders')
                            ->required()
                            ->helperText('Upload gambar slider (rekomendasi 1920x1080px)'),

                        Forms\Components\ColorPicker::make('overlay_color')
                            ->label('Warna Overlay')
                            ->default('rgba(0,0,0,0.4)')
                            ->helperText('Warna overlay untuk kontras teks'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Tombol Aksi')
                    ->schema([
                        Forms\Components\TextInput::make('button1_text')
                            ->label('Teks Tombol 1')
                            ->helperText('Teks untuk tombol pertama'),

                        Forms\Components\TextInput::make('button1_url')
                            ->label('URL Tombol 1')
                            ->helperText('Link untuk tombol pertama'),

                        Forms\Components\TextInput::make('button2_text')
                            ->label('Teks Tombol 2')
                            ->helperText('Teks untuk tombol kedua'),

                        Forms\Components\TextInput::make('button2_url')
                            ->label('URL Tombol 2')
                            ->helperText('Link untuk tombol kedua'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Pengaturan')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Apakah slider ini ditampilkan'),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0)
                            ->helperText('Urutan tampilan slider (angka kecil = tampil pertama)'),

                        Forms\Components\ColorPicker::make('text_color')
                            ->label('Warna Teks')
                            ->default('#ffffff')
                            ->helperText('Warna teks pada slider'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Gambar')
                    ->width(150)
                    ->height(100)
                    ->square(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('subtitle')
                    ->label('Subjudul')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diupdate')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif')
                    ->queries(
                        true: fn ($query) => $query->where('is_active', true),
                        false: fn ($query) => $query->where('is_active', false),
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Slider')
                    ->modalDescription('Apakah Anda yakin ingin menghapus slider ini?'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Slider Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus slider yang dipilih?'),
                ]),
            ])
            ->reorderable('sort_order');
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
            'index' => Pages\ListHeroSliders::route('/'),
            'create' => Pages\CreateHeroSlider::route('/create'),
            'edit' => Pages\EditHeroSlider::route('/{record}/edit'),
        ];
    }
}