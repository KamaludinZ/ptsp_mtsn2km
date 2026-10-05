<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Support\RoleAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as RoleModel;
use Spatie\Permission\PermissionRegistrar;

/**
 * Roles and their permissions. Built-in roles (RoleAccess::SYSTEM_ROLES)
 * cannot be renamed or deleted and always keep their staff-area access; a
 * role still held by users cannot be deleted.
 */
class RoleResource extends Resource
{
    use \App\Filament\Concerns\AdminOnly;

    protected static ?string $model = RoleModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Peran';

    protected static ?string $navigationGroup = 'Manajemen Sistem';

    protected static ?string $modelLabel = 'peran';

    protected static ?string $pluralModelLabel = 'Peran';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('guard_name', 'web')->withCount(['users', 'permissions']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Peran')
                    ->description(fn (?RoleModel $record) => RoleAccess::isSystemRole($record?->name)
                        ? 'Peran bawaan sistem: namanya tidak bisa diubah dan peran ini tidak bisa dihapus.'
                        : null)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Kode peran')
                            ->helperText('Huruf kecil, angka, dan garis bawah. Contoh: operator_perpustakaan')
                            ->required()
                            ->maxLength(64)
                            ->regex('/^[a-z][a-z0-9_]*$/')
                            ->validationMessages(['regex' => 'Gunakan huruf kecil, angka, dan garis bawah; diawali huruf.'])
                            ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->where('guard_name', 'web'))
                            ->dehydrateStateUsing(fn (?string $state) => strtolower(trim((string) $state)))
                            ->disabled(fn (?RoleModel $record) => RoleAccess::isSystemRole($record?->name))
                            ->dehydrated(fn (?RoleModel $record) => ! RoleAccess::isSystemRole($record?->name)),
                        Forms\Components\Placeholder::make('label')
                            ->label('Nama tampilan')
                            ->content(fn (?RoleModel $record) => $record ? RoleAccess::roleLabel($record->name) : '–'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Izin')
                    ->description(fn (?RoleModel $record) => ($required = RoleAccess::requiredPermissions($record?->name))
                        ? 'Izin area berikut selalu dipertahankan untuk peran ini: ' . collect($required)->map(fn ($p) => RoleAccess::permissionLabel($p))->implode(', ') . '.'
                        : 'Centang izin yang dimiliki peran ini.')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->hiddenLabel()
                            ->relationship('permissions', 'name', fn (Builder $query) => $query->where('guard_name', 'web')->orderBy('name'))
                            ->getOptionLabelFromRecordUsing(fn (Permission $record) => RoleAccess::permissionLabel($record->name))
                            ->columns(2)
                            ->searchable()
                            ->bulkToggleable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label('Peran')
                    ->state(fn (RoleModel $record) => RoleAccess::roleLabel($record->name))
                    ->description(fn (RoleModel $record) => $record->name)
                    ->weight('semibold')
                    ->searchable(query: fn (Builder $query, string $search) => $query->where('name', 'ilike', "%{$search}%")),
                Tables\Columns\TextColumn::make('kind')
                    ->label('Jenis')
                    ->badge()
                    ->state(fn (RoleModel $record) => RoleAccess::isSystemRole($record->name) ? 'Bawaan' : 'Kustom')
                    ->color(fn (string $state) => $state === 'Bawaan' ? 'gray' : 'info'),
                Tables\Columns\TextColumn::make('areas')
                    ->label('Akses')
                    ->badge()
                    ->state(fn (RoleModel $record) => collect(RoleAccess::AREA_PERMISSIONS)
                        ->filter(fn (string $permission) => $record->hasPermissionTo($permission))
                        ->map(fn (string $permission) => ['frontdesk.access' => 'Loket', 'backoffice.access' => 'Back Office', 'supervision.access' => 'Pengawasan'][$permission])
                        ->values()->all() ?: ['Portal pemohon'])
                    ->color(fn (string $state) => match ($state) {
                        'Loket' => 'warning',
                        'Back Office' => 'primary',
                        'Pengawasan' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Pengguna')
                    ->sortable()
                    ->alignCenter()
                    ->color('primary')
                    ->url(fn (RoleModel $record) => UserResource::getUrl('index', ['tableFilters[roles][values][0]' => $record->id])),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('Izin')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('system')
                    ->label('Jenis')
                    ->trueLabel('Bawaan')
                    ->falseLabel('Kustom')
                    ->queries(
                        true: fn (Builder $query) => $query->whereIn('name', array_keys(RoleAccess::SYSTEM_ROLES)),
                        false: fn (Builder $query) => $query->whereNotIn('name', array_keys(RoleAccess::SYSTEM_ROLES)),
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (RoleModel $record) => static::canBeDeleted($record))
                    ->after(fn () => static::flushPermissionCache()),
            ])
            ->defaultSort('name');
    }

    public static function canBeDeleted(RoleModel $record): bool
    {
        return ! RoleAccess::isSystemRole($record->name) && ! $record->users()->exists();
    }

    /** Keep a system role's area permissions and drop Spatie's permission cache. */
    public static function afterSave(RoleModel $record): void
    {
        if ($required = RoleAccess::requiredPermissions($record->name)) {
            $record->givePermissionTo($required);
        }

        static::flushPermissionCache();
    }

    public static function flushPermissionCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
