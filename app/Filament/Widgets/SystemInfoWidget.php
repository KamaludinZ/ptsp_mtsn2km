<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class SystemInfoWidget extends Widget
{
    protected static string $view = 'filament.widgets.system-info-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 6;

    public function getSystemInfo(): array
    {
        return [
            'app_name' => config('app.name', 'PTSP MTsN 2 KOTA MALANG'),
            'app_version' => '1.0.0',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'filament_version' => '3.x',
            'server_os' => PHP_OS,
            'uptime' => $this->getUptime(),
            'last_login' => auth()->user()?->last_login_at?->diffForHumans() ?? 'N/A',
        ];
    }

    private function getUptime(): string
    {
        // This is a simplified uptime calculation
        // In a real application, you might want to use more sophisticated methods
        $startTime = strtotime('today midnight');
        $now = time();
        $diff = $now - $startTime;
        
        $hours = floor($diff / 3600);
        $minutes = floor(($diff % 3600) / 60);
        
        return sprintf('%d jam %d menit', $hours, $minutes);
    }
}