<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Support\RoleAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    use \App\Filament\Concerns\AdminOnly;

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationGroup = 'Manajemen Pengguna';

    public static function form(Form $form): Form
    {
        $isSelf = fn (?User $record) => $record && $record->is(auth()->user());

        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengguna')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('whatsapp_number')
                            ->label('Nomor WhatsApp')
                            ->tel()
                            ->placeholder('08xxxxxxxxxx')
                            ->regex('/^(\+?62|0)\s?8[0-9][0-9\s-]{6,14}$/') // spaces and dashes are stripped when saved
                            ->validationMessages(['regex' => 'Gunakan nomor HP Indonesia, mis. 081234567890.'])
                            ->dehydrateStateUsing(fn (?string $state) => filled($state) ? preg_replace('/[^0-9+]/', '', $state) : null)
                            ->maxLength(20),
                        Forms\Components\Select::make('user_type')
                            ->label('Kategori')
                            ->options(\App\Services\FrontDeskService::APPLICANT_TYPES)
                            ->helperText('Untuk pemohon: menentukan layanan yang bisa diajukan.')
                            ->required(),
                        Forms\Components\TextInput::make('registration_code')
                            ->label('Kode Registrasi')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif (boleh masuk)')
                            ->default(true)
                            ->disabled($isSelf)
                            ->helperText(fn (?User $record) => $isSelf($record) ? 'Anda tidak dapat menonaktifkan akun sendiri.' : null),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Password')
                    ->description(fn (string $context) => $context === 'edit' ? 'Kosongkan bila password tidak diubah.' : null)
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label(fn (string $context) => $context === 'edit' ? 'Password baru' : 'Password')
                            ->password()
                            ->revealable()
                            ->rule(\Illuminate\Validation\Rules\Password::min(8)->letters()->numbers())
                            ->confirmed()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->helperText('Minimal 8 karakter, berisi huruf dan angka.')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Ulangi password')
                            ->password()
                            ->revealable()
                            ->dehydrated(false)
                            ->required(fn (string $context, Forms\Get $get): bool => $context === 'create' || filled($get('password'))),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Role & Permission')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->label('Role')
                            ->relationship('roles', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Role $record) => RoleAccess::SYSTEM_ROLES[$record->name] ?? $record->name)
                            ->helperText('Unit penerima disposisi: Waka Humas, Waka Kesiswaan, Waka Kurikulum, Waka Sarpras, Tata Usaha, Penjamin Mutu.')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->rules([fn (?User $record) => function (string $attribute, $value, \Closure $fail) use ($record, $isSelf) {
                                // An admin cannot take away their own admin role (they would lock themselves out).
                                if ($isSelf($record) && $record->hasRole('admin')
                                    && ! in_array(Role::findByName('admin')->id, array_map('intval', (array) $value), true)) {
                                    $fail('Anda tidak dapat menghapus peran Administrator dari akun sendiri.');
                                }
                            }]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('roles:id,name'))
            ->defaultSort('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->weight('semibold')
                    ->searchable()
                    ->sortable()
                    ->description(fn (User $record) => $record->email),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('whatsapp_number')
                    ->label('WhatsApp')
                    ->placeholder('–')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user_type')
                    ->label('Tipe User')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'guru' => 'success',
                        'pegawai' => 'info',
                        'siswa' => 'warning',
                        'walimurid' => 'gray',
                        'alumni' => 'purple',
                        'instansi' => 'indigo',
                        'umum' => 'slate',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'guru' => 'Guru',
                        'pegawai' => 'Pegawai',
                        'siswa' => 'Siswa',
                        'walimurid' => 'Wali Murid',
                        'alumni' => 'Alumni',
                        'instansi' => 'Instansi',
                        'umum' => 'Umum',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->formatStateUsing(fn (string $state) => RoleAccess::SYSTEM_ROLES[$state] ?? $state)
                    ->badge()
                    ->separator(','),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Terakhir masuk')
                    ->since()
                    ->sortable()
                    ->tooltip(fn (User $record) => $record->last_login_at?->translatedFormat('j F Y, H:i'))
                    ->placeholder('Belum pernah')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Email Terverifikasi')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('registration_code')
                    ->label('Kode Registrasi')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_type')
                    ->label('Tipe User')
                    ->options([
                        'guru' => 'Guru',
                        'pegawai' => 'Pegawai',
                        'siswa' => 'Siswa',
                        'walimurid' => 'Wali Murid',
                        'alumni' => 'Alumni',
                        'instansi' => 'Instansi',
                        'umum' => 'Umum',
                    ]),
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Peran')
                    ->relationship('roles', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => RoleAccess::SYSTEM_ROLES[$record->name] ?? $record->name)
                    ->multiple()
                    ->preload(),
                Tables\Filters\Filter::make('is_active')
                    ->label('Hanya Aktif')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->toggle(),
                Tables\Filters\Filter::make('never_logged_in')
                    ->label('Belum pernah masuk')
                    ->query(fn (Builder $query): Builder => $query->whereNull('last_login_at'))
                    ->toggle(),
                Tables\Filters\Filter::make('email_verified')
                    ->label('Email Terverifikasi')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('email_verified_at'))
                    ->toggle(),
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
            ->emptyStateIcon('heroicon-o-users')
            ->emptyStateHeading('Tidak ada pengguna')
            ->emptyStateDescription('Tidak ada akun yang cocok dengan tab, pencarian, atau saringan ini.');
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
