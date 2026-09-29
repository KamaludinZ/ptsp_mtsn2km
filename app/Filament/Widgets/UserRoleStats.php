<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class UserRoleStats extends BaseWidget
{
    protected static bool $isDiscovered = false;

    /** Part of the administrator's dashboard. */
    public static function canView(): bool
    {
        return (bool) auth()->user()?->hasRole('admin');
    }

    protected static ?int $sort = 21;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return Cache::remember('admin_dashboard_user_role_stats', 120, function () {
            try {
                // Count users by role
                $usersByRole = [];
                $users = User::with('roles')->get();
                
                foreach ($users as $user) {
                    $roleNames = $user->roles->pluck('name')->toArray();
                    foreach ($roleNames as $roleName) {
                        $usersByRole[$roleName] = ($usersByRole[$roleName] ?? 0) + 1;
                    }
                }
                
                // Create stats for each role - show breakdown by role
                $stats = [];
                $roleColors = [
                    'super-admin' => 'danger',
                    'admin' => 'success',
                    'frontdesk' => 'primary',
                    'backoffice' => 'info',
                    'kepala-sekolah' => 'warning',
                    'waka' => 'purple',
                    'kepala-tu' => 'indigo',
                    'gtk' => 'cyan',
                    'pemohon' => 'gray',
                ];

                foreach ($usersByRole as $role => $countValue) {
                    $color = $roleColors[$role] ?? 'primary';
                    $stats[] = Stat::make(ucwords(str_replace('-', ' ', $role)), $countValue)
                        ->description('Pengguna dengan role ' . ucfirst($role))
                        ->descriptionIcon('heroicon-m-user-circle')
                        ->color($color);
                }

                if (empty($stats)) {
                    $stats = [
                        Stat::make('Belum Ada Pengguna', '0')
                            ->description('Tidak ada pengguna dengan role')
                            ->descriptionIcon('heroicon-m-users')
                            ->color('gray')
                    ];
                }
                
            } catch (\Exception $e) {
                report($e);
                return [
                    Stat::make('Error', 'Database Error')
                        ->description('Could not load user stats')
                        ->color('danger'),
                ];
            }

            return $stats;
        });
    }
}