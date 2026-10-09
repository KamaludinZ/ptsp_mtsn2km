<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getSubheading(): ?string
    {
        return 'Akun petugas dan pemohon. Peran menentukan menu yang bisa dibuka; akun nonaktif tidak bisa masuk.';
    }

    protected function getHeaderActions(): array
    {
        return [
            self::importAction(),
            Actions\CreateAction::make()->label('Tambah pengguna')->icon('heroicon-m-user-plus'),
        ];
    }

    /** Import akun masal: unggah berkas Excel/CSV berisi akun baru. */
    public static function importAction(): Actions\Action
    {
        return Actions\Action::make('import')
            ->label('Unggah berkas akun')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('gray')
            ->modalHeading('Import akun dari berkas')
            ->modalDescription('Satu baris satu akun: nama, email, nomor WhatsApp, tipe pengguna, kode registrasi, dan role. Password dibuat otomatis dan tidak dikirim ke pengguna.')
            ->modalSubmitActionLabel('Proses import')
            ->form([
                FileUpload::make('file')
                    ->label('Berkas Excel/CSV')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'text/csv',
                        'text/plain',
                    ])
                    ->disk('local')
                    ->directory('imports/users')
                    ->maxSize(5120)
                    ->required()
                    ->helperText('Format .xlsx atau .csv, maks. 5 MB. Baris pertama berisi judul kolom.')
                    ->validationMessages(['required' => 'Pilih berkas yang akan diimport.']),
            ])
            ->action(function (array $data) {
                // Proses import menyusul (UserImport); sementara berkas hanya diterima.
                Notification::make()
                    ->title('Berkas diterima')
                    ->body('Pemrosesan akun dari berkas ini belum tersedia.')
                    ->info()
                    ->send();
            });
    }

    public function getTabs(): array
    {
        // Filament injects closure arguments by name: the query must be called $query.
        $staff = fn (Builder $query) => $query->whereHas('roles', fn (Builder $r) => $r->whereIn('name', User::STAFF_ROLES));

        return [
            'semua' => Tab::make('Semua')->badge(User::count()),
            'petugas' => Tab::make('Petugas')->icon('heroicon-m-briefcase')
                ->badge($staff(User::query())->count())
                ->modifyQueryUsing($staff),
            'pemohon' => Tab::make('Pemohon')->icon('heroicon-m-user')
                ->badge(User::whereDoesntHave('roles', fn (Builder $r) => $r->whereIn('name', User::STAFF_ROLES))->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDoesntHave('roles', fn (Builder $r) => $r->whereIn('name', User::STAFF_ROLES))),
            'nonaktif' => Tab::make('Nonaktif')->icon('heroicon-m-no-symbol')
                ->badge(User::where('is_active', false)->count() ?: null)->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false)),
        ];
    }
}
