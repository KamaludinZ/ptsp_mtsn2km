<?php

namespace App\Filament\Pages\System;

use App\Models\User;
use App\Support\ActiveRoles;
use App\Support\RoleAccess;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Ganti akun sementara (/cp/pengguna/ganti-akun): administrator memilih
 * pengguna lalu masuk sebagai pengguna itu untuk meninjau tampilannya,
 * dengan alasan yang dicatat. Hanya dari peran aktif Administrator.
 * Masih tampilan (Fase 3, frontend): "Masuk sebagai" belum memulai sesi.
 */
class Impersonation extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'Manajemen Pengguna';

    protected static ?string $navigationLabel = 'Ganti Akun Sementara';

    protected static ?string $title = 'Ganti Akun Sementara';

    protected static ?string $slug = 'pengguna/ganti-akun';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.table-page';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User && ActiveRoles::hasRole($user, 'admin');
    }

    public function getSubheading(): ?string
    {
        return 'Masuk sebagai pengguna lain untuk meninjau tampilan dan masalah yang ia alami. Setiap sesi dicatat beserta alasannya; Anda dapat kembali ke akun sendiri kapan saja.';
    }

    protected function getFooterWidgets(): array
    {
        return [\App\Filament\Widgets\ImpersonationLog::class];
    }

    /** Why a user cannot be chosen, or null when they can. */
    public static function blockedReason(User $target): ?string
    {
        return match (true) {
            $target->is(auth()->user()) => 'Ini akun Anda sendiri.',
            $target->is_active === false => 'Akun nonaktif tidak dapat dimasuki.',
            default => null,
        };
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query()->with('roles:id,name'))
            ->defaultSort('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama')->weight('semibold')->searchable()->sortable()
                    ->description(fn (User $record) => $record->email),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('roles.name')->label('Peran')
                    ->formatStateUsing(fn (string $state) => RoleAccess::roleLabel($state))
                    ->badge()->separator(',')->placeholder('Pemohon'),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
                Tables\Columns\TextColumn::make('last_login_at')->label('Terakhir masuk')->since()->sortable()->placeholder('Belum pernah'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')->label('Peran')
                    ->options(RoleAccess::SYSTEM_ROLES)
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'], fn (Builder $q, string $role) => $q->role($role))),
                Tables\Filters\TernaryFilter::make('is_active')->label('Status akun')
                    ->trueLabel('Aktif')->falseLabel('Nonaktif'),
            ])
            ->actions([
                \App\Filament\Actions\ImpersonateAction::table(),
            ])
            ->emptyStateHeading('Tidak ada pengguna yang cocok');
    }
}
