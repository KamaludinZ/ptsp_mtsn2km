<?php

namespace App\Filament\Pages\System;

use App\Support\SecurityMonitor;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Administrators' security tools: health checks and scan, the blocked IP
 * list (enforced by CheckBlockedIP), maintenance mode and cache clearing.
 */
class Security extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Manajemen Sistem';

    protected static ?string $navigationLabel = 'Keamanan Sistem';

    protected static ?string $title = 'Keamanan Sistem';

    protected static ?string $slug = 'keamanan';

    protected static string $view = 'filament.pages.security';

    /** Administrators working as one: logs and the activity trail are not shown in another active role. */
    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof \App\Models\User && \App\Support\ActiveRoles::hasRole($user, 'admin');
    }

    private function monitor(): SecurityMonitor
    {
        return app(SecurityMonitor::class);
    }

    protected function getViewData(): array
    {
        return [
            'metrics' => $this->monitor()->metrics(),
            'scan' => $this->monitor()->lastScan(),
            'blocked' => $this->monitor()->blockedIps(),
            'isDown' => $this->monitor()->isDown(),
            'statusColor' => fn (?string $status) => match ($status) {
                'pass' => 'success',
                'warning' => 'warning',
                'fail' => 'danger',
                default => 'gray',
            },
        ];
    }

    public function unblock(string $ip): void
    {
        $done = $this->monitor()->unblockIp($ip, auth()->user()->email);

        Notification::make()->title($done ? "Blokir {$ip} dibuka." : "{$ip} tidak ada di daftar blokir.")
            ->{$done ? 'success' : 'warning'}()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('scan')
                ->label('Jalankan pemindaian')
                ->icon('heroicon-m-magnifying-glass-circle')
                ->action(function () {
                    $results = $this->monitor()->runScan();
                    Notification::make()->title('Pemindaian selesai')->body('Skor: ' . $results['overall_score'] . '%')->success()->send();
                }),
            Action::make('blockIp')
                ->label('Blokir IP')
                ->icon('heroicon-m-no-symbol')
                ->color('danger')
                ->form([
                    TextInput::make('ip')->label('Alamat IP')->required()->ip()
                        ->rule(fn () => function (string $attribute, $value, \Closure $fail) {
                            if ($value === request()->ip()) {
                                $fail('Alamat ini adalah IP Anda sendiri.');
                            }
                        }),
                    TextInput::make('reason')->label('Alasan')->required()->maxLength(255),
                    TextInput::make('hours')->label('Durasi (jam)')->numeric()->minValue(1)
                        ->helperText('Kosongkan untuk blokir permanen.'),
                ])
                ->action(function (array $data) {
                    $this->monitor()->blockIp($data['ip'], $data['reason'], $data['hours'] ? (int) $data['hours'] : null, auth()->user()->email);
                    Notification::make()->title("{$data['ip']} diblokir.")->success()->send();
                }),
            Action::make('maintenanceOn')
                ->label('Aktifkan mode pemeliharaan')
                ->icon('heroicon-m-wrench-screwdriver')
                ->color('warning')
                ->visible(fn () => ! $this->monitor()->isDown())
                ->requiresConfirmation()
                ->modalDescription('Seluruh situs, termasuk panel ini, menampilkan halaman pemeliharaan sampai mode ini dimatikan. Browser Anda tetap bisa masuk lewat kode akses.')
                ->form([
                    TextInput::make('secret')
                        ->label('Kode akses')
                        ->required()
                        ->alphaDash()
                        ->minLength(8)
                        ->maxLength(64)
                        ->default(fn () => \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(16)))
                        ->helperText('Catat kode ini. Dari perangkat lain, buka alamat situs diikuti /kode-akses untuk melewati halaman pemeliharaan.'),
                ])
                ->action(function (array $data) {
                    $this->monitor()->enableMaintenance($data['secret'], 60, auth()->user()->email);

                    // Visiting the secret URL gives this browser the bypass cookie.
                    $this->redirect(url($data['secret']));
                }),
            Action::make('maintenanceOff')
                ->label('Matikan mode pemeliharaan')
                ->icon('heroicon-m-check-circle')
                ->color('success')
                ->visible(fn () => $this->monitor()->isDown())
                ->requiresConfirmation()
                ->action(function () {
                    $this->monitor()->disableMaintenance(auth()->user()->email);
                    Notification::make()->title('Mode pemeliharaan dimatikan')->success()->send();
                }),
            Action::make('clearCache')
                ->label('Bersihkan cache')
                ->icon('heroicon-m-trash')
                ->color('gray')
                ->form([
                    Select::make('type')->label('Jenis cache')->options(SecurityMonitor::CACHE_TYPES)->default('all')->required(),
                ])
                ->action(function (array $data) {
                    $this->monitor()->clearCache($data['type'], auth()->user()->email);
                    Notification::make()->title('Cache dibersihkan')->success()->send();
                }),
        ];
    }
}
