<?php

namespace App\Console\Commands;

use App\Models\SystemMetric;
use App\Models\User;
use App\Services\SystemMonitorService;
use App\Services\UpdateService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Monitoring Sistem: records one health snapshot (scheduled every five
 * minutes), prunes old ones, notes a redeployed version in the riwayat
 * pembaruan, and alerts admins when the status turns critical.
 */
class CollectSystemMetrics extends Command
{
    protected $signature = 'monitor:collect';

    protected $description = 'Catat metrik kesehatan aplikasi & server untuk Monitoring Sistem';

    public function handle(SystemMonitorService $monitor, UpdateService $updates): int
    {
        $previous = SystemMetric::latest('recorded_at')->latest('id')->value('status');

        try {
            $metric = $monitor->record();
        } catch (Throwable $e) {
            Log::error('Monitoring: metrik gagal dicatat', ['error' => $e->getMessage()]);
            $this->error('Metrik gagal dicatat: ' . $e->getMessage());

            return self::FAILURE;
        }

        $updates->recordDeployment($monitor->version());

        if ($metric->status === 'danger' && $previous !== 'danger') {
            $this->notifyAdmins($monitor->lastOverall['issues'] ?? []);
        } elseif ($metric->status !== 'danger' && $previous === 'danger') {
            Log::info('Monitoring: status aplikasi pulih', ['status' => $metric->status]);
        }

        $this->info("Status {$metric->status} tercatat pukul {$metric->recorded_at->format('H:i')}.");

        return self::SUCCESS;
    }

    /** @param  array<int, string>  $issues */
    private function notifyAdmins(array $issues): void
    {
        Log::critical('Monitoring: status aplikasi kritis', ['issues' => $issues]);

        $body = "Monitoring Sistem PTSP mendeteksi status KRITIS pada " . now()->format('d/m/Y H:i') . ":\n\n- "
            . (implode("\n- ", $issues) ?: 'tidak ada rincian') . "\n\nPeriksa menu Monitoring Sistem: " . url('/cp');

        User::role('admin')->where('is_active', true)->whereNotNull('email')->pluck('email')
            ->each(function (string $email) use ($body) {
                try {
                    Mail::raw($body, fn ($message) => $message->to($email)->subject('[PTSP] Status aplikasi kritis'));
                } catch (Throwable $e) {
                    Log::warning('Monitoring: peringatan email gagal dikirim', ['error' => $e->getMessage()]);
                }
            });
    }
}
