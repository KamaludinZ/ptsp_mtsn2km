<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeroSliderResource\Pages;
use App\Models\HeroSlider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Hero slider beranda: slides shown at the top of the public homepage, in
 * order. Without an active slide the homepage shows its static hero.
 */
class HeroSliderResource extends Resource
{
    protected static ?string $model = HeroSlider::class;

    protected static ?string $slug = 'hero-slider';

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Manajemen Pengumuman';

    protected static ?string $navigationLabel = 'Hero Slider Beranda';

    protected static ?string $modelLabel = 'slide';

    protected static ?string $pluralModelLabel = 'Hero Slider';

    protected static ?int $navigationSort = 5;

    public static function can(string $action, ?Model $record = null): bool
    {
        return (bool) auth()->user()?->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        $url = fn (string $field) => Forms\Components\TextInput::make($field)->label('Tautan')->maxLength(255)
            ->placeholder('/layanan atau https://…')
            ->rule('regex:/^(\/|https?:\/\/)\S*$/')
            ->validationMessages(['regex' => 'Awali dengan / untuk halaman situs ini atau https:// untuk situs lain.']);

        return $form->schema([
            Forms\Components\Section::make('Isi slide')->columns(2)->schema([
                Forms\Components\FileUpload::make('image')->label('Gambar latar')
                    ->image()
                    ->disk(HeroSlider::DISK)
                    ->directory('hero-slides')
                    ->visibility('public')
                    ->imageEditor()
                    ->maxSize(3072)
                    ->helperText('Disarankan lanskap 1920×800 piksel, maksimal 3 MB.')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('title')->label('Judul')->maxLength(120)->columnSpanFull(),
                Forms\Components\TextInput::make('subtitle')->label('Subjudul')->maxLength(160),
                Forms\Components\Textarea::make('description')->label('Deskripsi')->rows(2)->maxLength(300),
            ]),
            Forms\Components\Section::make('Tombol')->columns(2)->collapsible()->schema([
                Forms\Components\TextInput::make('button1_text')->label('Tombol utama')->maxLength(40),
                $url('button1_url')->requiredWith('button1_text'),
                Forms\Components\TextInput::make('button2_text')->label('Tombol kedua')->maxLength(40),
                $url('button2_url')->requiredWith('button2_text'),
            ]),
            Forms\Components\Section::make('Tampilan')->columns(3)->collapsible()->schema([
                Forms\Components\ColorPicker::make('text_color')->label('Warna teks')->default('#ffffff'),
                Forms\Components\ColorPicker::make('overlay_color')->label('Lapisan gelap')->rgba()->default('rgba(0,0,0,0.4)')
                    ->helperText('Menjaga teks tetap terbaca di atas gambar.'),
                Forms\Components\Toggle::make('is_active')->label('Tampilkan di beranda')->default(true)->inline(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('image')->label('Gambar')->disk(HeroSlider::DISK)->height(56),
                Tables\Columns\TextColumn::make('title')->label('Judul')->weight('semibold')->wrap()->placeholder('Tanpa judul')
                    ->description(fn (HeroSlider $record) => $record->subtitle),
                Tables\Columns\TextColumn::make('button1_text')->label('Tombol')->placeholder('–')
                    ->description(fn (HeroSlider $record) => $record->button2_text),
                Tables\Columns\ToggleColumn::make('is_active')->label('Tampil'),
                Tables\Columns\TextColumn::make('updated_at')->label('Diubah')->since()->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Status')->trueLabel('Tampil')->falseLabel('Disembunyikan'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->modalDescription('Slide dan gambarnya dihapus dari beranda.'),
            ])
            ->emptyStateIcon('heroicon-o-photo')
            ->emptyStateHeading('Belum ada slide')
            ->emptyStateDescription('Tanpa slide aktif, beranda menampilkan hero bawaan. Tambahkan slide untuk menampilkan banner bergilir.');
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
