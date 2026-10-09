<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Services\FrontDeskService;
use App\Support\RoleAccess;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    /** Kolom berkas import akun, urut seperti di template. */
    public const IMPORT_COLUMNS = ['nama', 'email', 'nomor_whatsapp', 'tipe_pengguna', 'kode_registrasi', 'role'];

    public function getSubheading(): ?string
    {
        return 'Akun petugas dan pemohon. Peran menentukan menu yang bisa dibuka; akun nonaktif tidak bisa masuk.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('excel')
                ->label('Unduh Excel')
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->tooltip(fn () => 'Berisi akun pada tab ' . $this->activeTabLabel() . ', sesuai pencarian dan filter yang aktif.')
                // Berkas Excel menyusul (UserExport); sementara hanya menyebut isi yang akan diunduh.
                ->action(fn () => Notification::make()
                    ->title('Unduh Excel belum tersedia')
                    ->body($this->getFilteredSortedTableQuery()->count() . ' akun pada tab ' . $this->activeTabLabel() . ' akan diunduh.')
                    ->info()
                    ->send()),
            self::importAction(),
            Actions\CreateAction::make()->label('Tambah pengguna')->icon('heroicon-m-user-plus'),
        ];
    }

    /** Nama tab yang sedang dibuka, mis. "Petugas". */
    public function activeTabLabel(): string
    {
        return $this->getTabs()[$this->activeTab ?? 'semua']?->getLabel() ?? 'Semua';
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
            ->extraModalFooterActions([
                Actions\Action::make('template')
                    ->label('Unduh template')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(fn () => response()->streamDownload(function () {
                        $out = fopen('php://output', 'w');
                        fputcsv($out, self::IMPORT_COLUMNS);
                        fputcsv($out, ['Contoh Guru', 'guru@contoh.sch.id', '081234567890', 'guru', 'GURU-001', 'guru']);
                        fclose($out);
                    }, 'template-import-akun.csv', ['Content-Type' => 'text/csv'])),
            ])
            ->form([
                Section::make('Template & petunjuk')
                    ->description('Unduh template lewat tombol di bawah, isi satu baris per akun, lalu unggah kembali.')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Placeholder::make('import_columns')
                            ->label('Kolom template')
                            ->content(new HtmlString('<span class="font-mono text-sm">' . e(implode(', ', self::IMPORT_COLUMNS)) . '</span>')),
                        Placeholder::make('import_user_types')
                            ->label('Isian tipe_pengguna')
                            ->content(self::codeList(FrontDeskService::APPLICANT_TYPES)),
                        Placeholder::make('import_roles')
                            ->label('Isian role (' . count(RoleAccess::SYSTEM_ROLES) . ' peran)')
                            ->helperText('Tulis kodenya, mis. back_office. Role di luar daftar ini dilaporkan gagal.')
                            ->content(self::codeList(RoleAccess::SYSTEM_ROLES)),
                    ]),
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
            ->action(function (array $data, self $livewire) {
                $livewire->replaceMountedAction('importReport', ['report' => self::demoReport()]);
            });
    }

    /** Ringkasan hasil import: jumlah akun dibuat dan baris yang gagal beserta alasannya. */
    public function importReportAction(): Actions\Action
    {
        return Actions\Action::make('importReport')
            ->modalHeading('Hasil import akun')
            ->modalContent(fn (array $arguments) => view('filament.resources.user-resource.import-report', [
                'report' => $arguments['report'] ?? ['created' => 0, 'failed' => []],
            ]))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup')
            ->modalWidth('3xl')
            ->after(fn () => $this->resetTable());
    }

    /** Data tiruan sampai UserImport tersedia: bentuknya sama dengan laporan import sungguhan. */
    private static function demoReport(): array
    {
        return [
            'demo' => true,
            'created' => 12,
            'failed' => [
                ['row' => 4, 'email' => 'guru@contoh.sch.id', 'reason' => 'Email sudah terdaftar.'],
                ['row' => 7, 'email' => 'siswa-baru', 'reason' => 'Format email tidak valid.'],
                ['row' => 9, 'email' => 'tu@contoh.sch.id', 'reason' => 'Role "operator" tidak dikenal.'],
            ],
        ];
    }

    /** Kode → label sebagai daftar dua kolom untuk petunjuk import. */
    private static function codeList(array $items): HtmlString
    {
        return new HtmlString('<dl class="grid grid-cols-[auto_1fr] gap-x-3 gap-y-0.5 text-sm">'
            . collect($items)->map(fn (string $label, string $code) => '<dt class="font-mono text-gray-950 dark:text-white">' . e($code) . '</dt><dd class="text-gray-500 dark:text-gray-400">' . e($label) . '</dd>')->join('')
            . '</dl>');
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
