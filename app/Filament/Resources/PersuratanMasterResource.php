<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersuratanMasterResource\Pages;
use App\Models\PersuratanMaster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Master persuratan: routine choices for letters and dispositions, one tab
 * per list. Kept by Tata Usaha; officers can still type their own value.
 */
class PersuratanMasterResource extends Resource
{
    protected static ?string $model = PersuratanMaster::class;

    protected static ?string $slug = 'master-persuratan';

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationGroup = 'Persuratan';

    protected static ?string $navigationLabel = 'Master Persuratan';

    protected static ?string $modelLabel = 'pilihan';

    protected static ?string $pluralModelLabel = 'Master Persuratan';

    protected static ?int $navigationSort = 2;

    /** Who keeps the lists: administrators and Tata Usaha (the letters' owners). */
    public const MANAGERS = ['admin', 'kepala_tu', 'tata_usaha'];

    /** Back office reads the lists; only MANAGERS change them. */
    public static function can(string $action, ?Model $record = null): bool
    {
        $user = auth()->user();

        return match ($action) {
            'viewAny', 'view' => (bool) $user?->can('backoffice.access'),
            default => (bool) $user?->hasAnyRole(self::MANAGERS),
        };
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('type')->label('Daftar')
                ->options(PersuratanMaster::TYPES)
                ->required()
                ->native(false)
                ->live()
                ->validationMessages(['required' => 'Pilih daftar tempat pilihan ini disimpan.']),
            Forms\Components\TextInput::make('kode')->label('Kode klasifikasi')
                ->placeholder('mis. PP.00')
                ->helperText('Huruf atau angka, boleh dipisah titik, garis miring, atau tanda hubung. Disimpan dalam huruf kapital.')
                ->maxLength(50)
                ->regex('/^\s*[A-Za-z0-9]+([.\/-][A-Za-z0-9]+)*\s*$/')
                ->rules([fn (Get $get, ?Model $record) => self::distinct('kode', $get('type'), $record)])
                ->dehydrateStateUsing(fn (?string $state) => filled($state) ? mb_strtoupper(trim($state)) : null)
                ->required(fn (Get $get) => $get('type') === 'klasifikasi')
                ->visible(fn (Get $get) => $get('type') === 'klasifikasi')
                ->validationMessages([
                    'required' => 'Kode klasifikasi wajib diisi.',
                    'regex' => 'Gunakan format kode seperti PP.00 atau KP.01.2.',
                    'max' => 'Kode paling panjang 50 karakter.',
                ]),
            Forms\Components\TextInput::make('nama')->label('Nama')->required()->minLength(3)->maxLength(255)
                ->placeholder(fn (Get $get) => self::PLACEHOLDERS[$get('type')] ?? null)
                ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule, Get $get) => $rule->where('type', $get('type')))
                ->rules([fn (Get $get, ?Model $record) => self::distinct('nama', $get('type'), $record)])
                ->dehydrateStateUsing(fn (?string $state) => Str::squish((string) $state))
                ->validationMessages([
                    'required' => 'Nama pilihan wajib diisi.',
                    'min' => 'Nama pilihan minimal 3 karakter.',
                    'max' => 'Nama pilihan paling panjang 255 karakter.',
                    'unique' => 'Pilihan ini sudah ada di daftar yang sama.',
                ]),
            Forms\Components\TextInput::make('sort')->label('Urutan')
                ->helperText('Angka kecil tampil lebih dulu.')
                ->numeric()->integer()->minValue(0)->maxValue(9999)->default(0)
                ->validationMessages([
                    'integer' => 'Urutan harus berupa bilangan bulat.',
                    'min' => 'Urutan tidak boleh negatif.',
                    'max' => 'Urutan paling besar 9999.',
                ]),
            Forms\Components\Toggle::make('is_active')->label('Tampilkan sebagai pilihan')
                ->helperText('Matikan untuk menyembunyikan dari form tanpa menghapusnya.')
                ->default(true),
        ])->columns(1);
    }

    /** Example per list, shown as the name placeholder. */
    private const PLACEHOLDERS = [
        'tujuan_naskah' => 'mis. Kepala Dinas Pendidikan Kota Malang',
        'tembusan' => 'mis. Kepala Tata Usaha',
        'klasifikasi' => 'mis. Pendidikan',
        'instruksi_disposisi' => 'mis. Untuk ditindaklanjuti',
    ];

    /** Refuse a value already in the same list, ignoring case and extra spaces. */
    private static function distinct(string $column, ?string $type, ?Model $record): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($column, $type, $record) {
            $value = mb_strtolower(Str::squish((string) $value));

            if ($value === '' || ! $type) {
                return;
            }

            $taken = PersuratanMaster::query()
                ->where('type', $type)
                ->whereRaw("lower(trim({$column})) = ?", [$value])
                ->when($record, fn ($q) => $q->whereKeyNot($record->getKey()))
                ->exists();

            if ($taken) {
                $fail($column === 'kode' ? 'Kode ini sudah dipakai di daftar klasifikasi.' : 'Pilihan ini sudah ada di daftar yang sama.');
            }
        };
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode')->label('Kode')->searchable()->placeholder('–')
                    ->visible(fn ($livewire) => in_array($livewire->activeTab ?? null, ['klasifikasi', null], true)),
                Tables\Columns\TextColumn::make('nama')->label('Nama')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('type')->label('Daftar')->badge()->color('gray')
                    ->formatStateUsing(fn (string $state) => PersuratanMaster::TYPES[$state] ?? $state)
                    ->visible(fn ($livewire) => blank($livewire->activeTab ?? null)),
                Tables\Columns\ToggleColumn::make('is_active')->label('Aktif'),
                Tables\Columns\TextColumn::make('sort')->label('Urutan')->sortable()->alignEnd(),
            ])
            ->defaultSort('sort')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->modalDescription('Pilihan ini hilang dari form. Surat yang sudah memakainya tidak berubah. Untuk menyembunyikan sementara, matikan "Aktif".'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')->label('Aktifkan')->icon('heroicon-m-eye')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('deactivate')->label('Nonaktifkan')->icon('heroicon-m-eye-slash')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-list-bullet')
            ->emptyStateHeading(fn ($livewire) => filled($livewire->tableSearch ?? null)
                ? 'Tidak ada pilihan yang cocok'
                : trim('Belum ada pilihan ' . mb_strtolower(PersuratanMaster::TYPES[$livewire->activeTab ?? ''] ?? '')))
            ->emptyStateDescription(fn ($livewire) => filled($livewire->tableSearch ?? null)
                ? 'Coba kata kunci lain atau hapus pencarian.'
                : 'Tambahkan pilihan rutin agar petugas cukup memilih dari daftar. Petugas tetap dapat mengetik isian sendiri.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make('emptyCreate')
                    ->label('Tambah pilihan')
                    ->icon('heroicon-m-plus')
                    ->model(PersuratanMaster::class)
                    ->form(fn (Form $form) => static::form($form))
                    ->fillForm(fn ($livewire) => ['type' => $livewire->activeTab ?: 'tujuan_naskah', 'is_active' => true, 'sort' => 0])
                    ->hidden(fn ($livewire) => filled($livewire->tableSearch ?? null)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPersuratanMasters::route('/'),
        ];
    }
}
