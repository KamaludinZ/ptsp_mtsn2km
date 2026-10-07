<?php

namespace App\Filament\Pages\System;

use App\Models\User;
use App\Support\ActiveRoles;
use App\Support\ActivityTrail as Trail;
use App\Support\RoleAccess;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Spatie\Activitylog\Models\Activity;

/**
 * Rekam jejak aktivitas (/cp/rekam-jejak): perpindahan peran, sesi ganti
 * akun, dan aksi tiket beserta konteksnya (peran aktif, tiket, layanan),
 * per hari, dengan saringan pengguna, peran, tanggal, dan aksi, serta rekap
 * per pengguna–peran. Hanya administrator (peran aktif). Data: activity_log
 * (App\Support\ActivityTrail), tidak dapat diubah atau dihapus.
 */
class ActivityTrail extends Page implements HasForms
{
    use InteractsWithForms;

    /** Jenis entri rekam jejak: label dan warna badge. */
    public const TYPES = [
        'role_switch' => ['label' => 'Perpindahan peran', 'color' => 'info', 'icon' => 'heroicon-m-arrows-right-left'],
        'impersonation' => ['label' => 'Ganti akun', 'color' => 'warning', 'icon' => 'heroicon-m-user-circle'],
        'ticket' => ['label' => 'Aksi tiket', 'color' => 'success', 'icon' => 'heroicon-m-ticket'],
    ];

    /** Entries shown in the timeline at once (newest first). */
    public const TIMELINE_LIMIT = 200;

    protected static ?string $navigationIcon = 'heroicon-o-finger-print';

    protected static ?string $navigationGroup = 'Manajemen Sistem';

    protected static ?string $navigationLabel = 'Rekam Jejak Aktivitas';

    protected static ?string $title = 'Rekam Jejak Aktivitas';

    protected static ?string $slug = 'rekam-jejak';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.activity-trail';

    /** Jenis yang ditampilkan ('' = semua). */
    #[Url(as: 'jenis')]
    public string $type = '';

    /** Saringan: user (id), role (key), from, until, action. */
    public ?array $filters = [];

    /** Catatan yang detailnya sedang dibuka. */
    public ?int $detailId = null;

    public static function canAccess(): bool
    {
        // Same rule as the Log & Audit panel: an administrator, not through a borrowed account.
        return SystemMonitor::canSeeRoleAudit();
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function getSubheading(): ?string
    {
        return 'Siapa melakukan apa, dengan peran aktif apa, dan pada tiket mana. Catatan tidak dapat diubah atau dihapus.';
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('filters')
            ->schema([
                Grid::make(['default' => 1, 'sm' => 2, 'lg' => 5])->schema([
                    Select::make('user')->label('Pengguna')->placeholder('Semua pengguna')
                        ->options(fn () => User::query()
                            ->whereIn('id', Activity::query()->whereIn('log_name', array_keys(Trail::LOGS))->where('causer_type', (new User)->getMorphClass())->select('causer_id'))
                            ->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()->live(),
                    Select::make('role')->label('Peran aktif')->placeholder('Semua peran')
                        ->options(collect(User::STAFF_ROLES)->mapWithKeys(fn ($r) => [$r => RoleAccess::roleLabel($r)])->all())->live(),
                    DatePicker::make('from')->label('Dari tanggal')->live(),
                    DatePicker::make('until')->label('Sampai tanggal')->live()->afterOrEqual('from'),
                    TextInput::make('action')->label('Aksi')->placeholder('mis. disposisi, unggah')->live(debounce: 400),
                ]),
            ]);
    }

    public function setType(string $type): void
    {
        $this->type = array_key_exists($type, self::TYPES) ? $type : '';
    }

    public function showDetail(int $id): void
    {
        $this->detailId = Trail::query()->whereKey($id)->exists() ? $id : null;

        if ($this->detailId) {
            $this->dispatch('open-modal', id: 'trail-detail');
        }
    }

    public function resetFilters(): void
    {
        $this->form->fill();
        $this->type = '';
    }

    /** The filters in ActivityTrail::query() terms. */
    private function queryFilters(bool $withType = true): array
    {
        $f = $this->filters ?? [];

        return [
            'pengguna' => $f['user'] ?? null,
            'peran' => $f['role'] ?? null,
            'aksi' => $f['action'] ?? null,
            'dari' => $f['from'] ?? null,
            'sampai' => $f['until'] ?? null,
            'jenis' => $withType && $this->type !== '' ? $this->type : null,
        ];
    }

    protected function getViewData(): array
    {
        $entries = Trail::query($this->queryFilters())->limit(self::TIMELINE_LIMIT)->get()->map(Trail::present(...));
        $all = Trail::query($this->queryFilters(withType: false))->reorder();

        $counts = (clone $all)->select('log_name', DB::raw('count(*) as total'))->groupBy('log_name')
            ->pluck('total', 'log_name')
            ->mapWithKeys(fn ($total, $log) => [Trail::LOGS[$log] => (int) $total]);

        // Rekap per pengguna–peran aktif (tanpa peran: pemohon atau sistem).
        $recap = (clone $all)
            ->select('causer_id', DB::raw("properties->>'peran_aktif' as role"), 'log_name', DB::raw('count(*) as total'), DB::raw('max(created_at) as last_at'))
            ->groupBy('causer_id', DB::raw("properties->>'peran_aktif'"), 'log_name')
            ->get()
            ->groupBy(fn ($row) => $row->causer_id . '|' . $row->role)
            ->map(fn ($rows) => [
                'user' => User::withTrashed()->find($rows->first()->causer_id)?->name ?? 'Sistem',
                'role' => $rows->first()->role ? RoleAccess::roleLabel($rows->first()->role) : 'Pemohon',
                'total' => (int) $rows->sum('total'),
                'by_type' => $rows->mapWithKeys(fn ($row) => [Trail::LOGS[$row->log_name] => (int) $row->total])->all(),
                'last' => Carbon::parse($rows->max('last_at')),
            ])
            ->sortByDesc('total')->values();

        $detail = $this->detailId ? Trail::query()->whereKey($this->detailId)->first() : null;

        return [
            'types' => self::TYPES,
            'days' => $entries->groupBy(fn (array $e) => $e['at']->toDateString()),
            'counts' => $counts,
            'recap' => $recap,
            'limited' => $entries->count() >= self::TIMELINE_LIMIT,
            'filtering' => collect($this->filters ?? [])->filter(fn ($v) => filled($v))->isNotEmpty(),
            'detail' => $detail ? Trail::present($detail) : null,
        ];
    }
}
