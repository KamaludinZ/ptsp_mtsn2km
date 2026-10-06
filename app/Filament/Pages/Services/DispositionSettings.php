<?php

namespace App\Filament\Pages\Services;

use App\Models\Service;
use App\Support\ProcessorRoles;
use App\Support\ServiceDisposition;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Pengaturan disposisi per layanan: for each service, who decides (mode),
 * which back-office units receive the disposition, and whether TTD or TTE
 * is recommended for its documents.
 */
class DispositionSettings extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Manajemen Layanan';

    protected static ?string $navigationLabel = 'Pengaturan Disposisi';

    protected static ?string $title = 'Pengaturan Disposisi per Layanan';

    protected static ?string $slug = 'layanan/pengaturan-disposisi';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.table-page';

    public static function canAccess(): bool
    {
        // Administrators, while working in the admin role.
        $user = auth()->user();

        return $user !== null && \App\Support\ActiveRoles::hasRole($user, 'admin');
    }

    public function getSubheading(): ?string
    {
        return 'Tentukan pejabat yang mendisposisi, unit penerima disposisi, dan anjuran tanda tangan untuk setiap layanan.';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Service::query())
            ->defaultSort('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Layanan')->searchable()->sortable()->wrap()->weight('semibold')
                    ->description(fn (Service $record) => $record->is_active ? null : 'Nonaktif'),
                Tables\Columns\TextColumn::make('disposition_mode')->label('Mode disposisi')->badge()
                    ->formatStateUsing(fn (string $state) => ServiceDisposition::mode($state))
                    ->color(fn (string $state, Service $record) => match (true) {
                        ServiceDisposition::usesDefault($record) => 'warning',
                        $state === 'none' => 'gray',
                        $state === 'custom' => 'warning',
                        default => 'primary',
                    })
                    ->icon(fn (Service $record) => ServiceDisposition::usesDefault($record) ? 'heroicon-m-exclamation-circle' : null)
                    ->description(fn (Service $record) => ServiceDisposition::usesDefault($record) ? 'Belum diatur — memakai aturan bawaan' : null),
                Tables\Columns\TextColumn::make('disposition_roles')->label('Penerima disposisi')->wrap()
                    ->state(fn (Service $record) => ServiceDisposition::recipients($record->disposition_roles)),
                Tables\Columns\TextColumn::make('signature_recommendation')->label('Anjuran tanda tangan')
                    ->state(fn (Service $record) => $record->signature_recommendation)
                    ->formatStateUsing(fn (?string $state) => strtoupper((string) $state))
                    ->badge()
                    ->color(fn (?string $state) => $state === 'tte' ? 'info' : 'gray')
                    ->placeholder('–'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('approval_required')->label('Butuh disposisi')
                    ->trueLabel('Butuh disposisi')->falseLabel('Tanpa disposisi'),
                Tables\Filters\Filter::make('unset')->label('Belum diatur (aturan bawaan)')->toggle()
                    ->query(fn (Builder $query) => $query->where('approval_required', true)
                        ->where(fn (Builder $q) => $q->whereNull('approval_roles')->orWhereRaw("approval_roles::jsonb = '[]'::jsonb"))
                        ->where(fn (Builder $q) => $q->whereNull('approval_users')->orWhereRaw("approval_users::jsonb = '[]'::jsonb"))),
                Tables\Filters\SelectFilter::make('recipient')->label('Penerima disposisi')
                    ->options(ServiceDisposition::RECIPIENTS)
                    ->query(fn (Builder $query, array $data) => $query->when(
                        $data['value'],
                        fn (Builder $q, string $role) => $q->whereJsonContains('disposition_roles', $role),
                    )),
                Tables\Filters\TernaryFilter::make('is_active')->label('Status layanan')
                    ->trueLabel('Aktif')->falseLabel('Nonaktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Atur')
                    ->modalHeading(fn (Service $record) => 'Pengaturan disposisi · ' . $record->name)
                    ->fillForm(fn (Service $record) => [
                        'disposition_mode' => $record->disposition_mode,
                        'disposition_roles' => $record->disposition_roles ?? [],
                        'signature_recommendation' => $record->signature_recommendation ?? 'none',
                    ])
                    ->form([
                        Placeholder::make('custom_notice')
                            ->hiddenLabel()
                            ->content('Layanan ini memakai pengaturan persetujuan khusus (peran/pengguna tertentu). Memilih mode di bawah akan menggantinya.')
                            ->visible(fn (Service $record) => $record->disposition_mode === 'custom'),
                        Radio::make('disposition_mode')
                            ->label('Mode disposisi')
                            ->options(ServiceDisposition::MODES)
                            ->descriptions(ServiceDisposition::MODE_DESCRIPTIONS)
                            ->required(fn (Service $record) => $record->disposition_mode !== 'custom')
                            ->live(),
                        CheckboxList::make('disposition_roles')
                            ->label('Penerima disposisi (Back Office)')
                            ->options(ServiceDisposition::RECIPIENTS)
                            ->descriptions(fn () => self::recipientHolders())
                            ->helperText('Unit yang menerima dan menindaklanjuti disposisi pimpinan. Pemegang tiap unit diatur di Peran Pemroses Naskah.')
                            ->bulkToggleable()
                            ->columns(2)
                            ->visible(fn (Get $get) => $get('disposition_mode') !== 'none'),
                        Radio::make('signature_recommendation')
                            ->label('Anjuran tanda tangan pada berkas layanan')
                            ->helperText('Ditampilkan sebagai informasi bagi petugas yang memproses naskah.')
                            ->options(ServiceDisposition::SIGNATURES + ['none' => 'Tidak ada anjuran'])
                            ->required(),
                    ])
                    ->using(fn (Service $record, array $data) => ServiceDisposition::apply(
                        $record,
                        $data['disposition_mode'] ?? null,
                        $data['disposition_roles'] ?? [],
                        $data['signature_recommendation'] === 'none' ? null : $data['signature_recommendation'],
                    ))
                    ->successNotification(fn (Service $record) => Notification::make()->success()
                        ->title('Pengaturan disposisi disimpan')
                        ->body($record->name . ': ' . ServiceDisposition::mode($record->disposition_mode) . '.')),
            ])
            ->emptyStateHeading('Belum ada layanan')
            ->emptyStateDescription('Tambahkan layanan di menu Layanan terlebih dahulu.');
    }

    /** @return array<string, string> Recipient unit => who holds it, for the checklist. */
    private static function recipientHolders(): array
    {
        return collect(ProcessorRoles::overview())
            ->mapWithKeys(fn (array $row) => [$row['name'] => $row['holders']->isEmpty()
                ? 'Belum ada pemegang — disposisi ke unit ini belum sampai ke siapa pun.'
                : 'Dipegang: ' . $row['holders']->pluck('name')->join(', ')])
            ->all();
    }
}
