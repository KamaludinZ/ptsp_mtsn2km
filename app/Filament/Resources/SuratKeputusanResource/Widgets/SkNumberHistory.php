<?php

namespace App\Filament\Resources\SuratKeputusanResource\Widgets;

use App\Filament\Resources\SuratKeputusanResource;
use App\Support\RoleAccess;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

/**
 * Riwayat nomor SK, like Riwayat permintaan nomor agenda on the surat keluar
 * register: every number taken and every SK deleted (with its reason), by
 * whom, in which active role and whether through ganti akun sementara. It
 * accounts for the gaps deleted SK leave in the register.
 */
class SkNumberHistory extends TableWidget
{
    public const EVENTS = ['created' => 'Nomor diambil', 'deleted' => 'SK dihapus'];

    protected int | string | array $columnSpan = 'full';

    protected static bool $isLazy = false;

    protected static ?string $heading = 'Riwayat nomor SK';

    public static function canView(): bool
    {
        return SuratKeputusanResource::canViewAny();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Activity::query()->inLog('surat_keputusan')->whereIn('event', array_keys(self::EVENTS))->with('causer:id,name'))
            ->defaultSort('id', 'desc')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i')
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('id', $direction)),
                Tables\Columns\TextColumn::make('event')->label('Kejadian')->badge()
                    ->formatStateUsing(fn (string $state) => self::EVENTS[$state] ?? $state)
                    ->color(fn (string $state) => $state === 'deleted' ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('nomor_sk')->label('Nomor SK')
                    ->state(fn (Activity $activity) => self::sk($activity)['nomor_sk'] ?? null)
                    ->description(fn (Activity $activity) => ($perihal = self::sk($activity)['perihal'] ?? null) ? 'Tentang ' . $perihal : null)
                    ->weight('semibold')
                    ->wrap(),
                Tables\Columns\TextColumn::make('oleh')->label('Oleh')
                    ->state(fn (Activity $activity) => $activity->causer?->name ?? ($activity->causer_id ? 'Akun sudah dihapus' : 'Sistem'))
                    ->description(fn (Activity $activity) => self::actingAs($activity)),
                Tables\Columns\TextColumn::make('alasan')->label('Alasan hapus')
                    ->state(fn (Activity $activity) => data_get($activity->properties, 'alasan'))
                    ->placeholder('–')
                    ->wrap(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')->label('Kejadian')->options(self::EVENTS),
                Tables\Filters\SelectFilter::make('tahun')->label('Tahun')
                    ->options(fn () => SuratKeputusanResource::yearOptions())
                    ->query(fn (Builder $query, array $data) => filled($data['value'] ?? null)
                        ? $query->where(fn (Builder $q) => $q->where('properties->attributes->tahun', (string) $data['value'])
                            ->orWhere('properties->old->tahun', (string) $data['value']))
                        : $query),
            ])
            ->emptyStateHeading('Belum ada riwayat nomor SK')
            ->emptyStateDescription('Setiap nomor SK yang diambil dan setiap SK yang dihapus tercatat di sini.')
            ->emptyStateIcon('heroicon-o-hashtag');
    }

    /** The SK as Log & Audit kept it: when its number was taken, or just before it was deleted. */
    private static function sk(Activity $activity): array
    {
        return (array) data_get($activity->properties, $activity->event === 'deleted' ? 'old' : 'attributes', []);
    }

    /** "Back Office · lewat ganti akun oleh …": the active role, and the administrator using the account. */
    private static function actingAs(Activity $activity): ?string
    {
        $parts = array_filter([
            ($role = data_get($activity->properties, 'peran_aktif')) ? RoleAccess::roleLabel($role) : null,
            ($admin = data_get($activity->properties, 'impersonated_by.admin')) ? 'lewat ganti akun oleh ' . $admin : null,
        ]);

        return $parts ? implode(' · ', $parts) : null;
    }
}
