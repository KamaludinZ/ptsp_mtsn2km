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

    /** Overlay presets (darkening layer over the slide image). */
    public const OVERLAYS = [
        'rgba(0,0,0,0)' => 'Tanpa lapisan',
        'rgba(0,0,0,0.25)' => 'Gelap tipis (25%)',
        'rgba(0,0,0,0.4)' => 'Gelap sedang (40%)',
        'rgba(0,0,0,0.6)' => 'Gelap pekat (60%)',
        'rgba(20,83,45,0.55)' => 'Hijau madrasah (55%)',
        'rgba(255,255,255,0.35)' => 'Terang (35%)',
    ];

    /** Image upload cropped to $ratio and scaled to $width×$height before it is sent. */
    private static function slideImage(string $field, string $label, string $ratio, int $width, int $height): Forms\Components\FileUpload
    {
        return Forms\Components\FileUpload::make($field)->label($label)
            ->image()
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->disk(HeroSlider::DISK)
            ->directory('hero-slides')
            ->visibility('public')
            ->imageEditor()
            ->imageEditorAspectRatios([$ratio])
            ->imageCropAspectRatio($ratio)
            ->imageResizeMode('cover')
            ->imageResizeTargetWidth((string) $width)
            ->imageResizeTargetHeight((string) $height)
            ->imageResizeUpscale(false)
            ->imagePreviewHeight('180')
            ->maxSize(5120);
    }

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
                // Cropped and scaled in the browser before upload, so every slide has
                // the hero's shape and a light file (see HeroSlider::ASPECT_RATIO).
                static::slideImage('image', 'Gambar (komputer)', HeroSlider::ASPECT_RATIO, HeroSlider::WIDTH, HeroSlider::HEIGHT)
                    ->helperText('Dipotong 16:5 dan diperkecil ke 1920×600 piksel. Taruh teks/objek penting di tengah; tinggi slider sama dengan hero beranda.')
                    ->required()
                    ->columnSpanFull(),
                static::slideImage('image_mobile', 'Gambar HP (opsional)', HeroSlider::MOBILE_ASPECT_RATIO, HeroSlider::MOBILE_WIDTH, HeroSlider::MOBILE_HEIGHT)
                    ->helperText('Potret 2:3 (1080×1620) untuk layar HP. Jika kosong, HP menampilkan bagian tengah gambar komputer.')
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
            Forms\Components\Section::make('Efek')->columns(3)->collapsible()->schema([
                Forms\Components\Select::make('image_fit')->label('Ukuran gambar')
                    ->options(HeroSlider::IMAGE_FITS)->default('contain')->native(false)->required()
                    ->helperText('Utuh cocok untuk poster/bagan; Penuh cocok untuk foto. Di HP tanpa gambar HP, slide selalu memakai Penuh.'),
                Forms\Components\Select::make('zoom_effect')->label('Efek zoom')
                    ->options(HeroSlider::ZOOM_EFFECTS)->default('in')->native(false)->required()
                    ->helperText('Bergerak pelan selama slide tampil; mati otomatis bila pengguna memilih kurangi gerakan.'),
                Forms\Components\Select::make('text_backdrop')->label('Latar judul & deskripsi')
                    ->options(HeroSlider::TEXT_BACKDROPS)->default('glass')->native(false)->required(),
            ]),
            Forms\Components\Section::make('Tampilan')->columns(3)->collapsible()->schema([
                // Native colour input and presets: no lazily loaded Alpine component,
                // which can fail to register after SPA navigation in the panel.
                Forms\Components\TextInput::make('text_color')->label('Warna teks')
                    ->type('color')
                    ->default('#ffffff')
                    ->regex('/^#[0-9a-fA-F]{6}$/')
                    ->required(),
                Forms\Components\Select::make('overlay_color')->label('Lapisan gelap')
                    ->options(self::OVERLAYS)
                    ->default('rgba(0,0,0,0.4)')
                    ->native(false)
                    ->required()
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
