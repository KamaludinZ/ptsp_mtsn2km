<?php

namespace App\Console\Commands;

use App\Models\SystemMetric;
use App\Models\User;
use App\Services\SystemMonitorService;
use App\Services\UpdateService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
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

    /** Hours before the same kind of problem is e-mailed again. */
    public const ALERT_COOLDOWN_HOURS = 6;

    public function handle(SystemMonitorService $monitor, UpdateService $updates): int
    {
        $previous = SystemMetric::latest('recorded_at')->latest('id')->first(['status', 'cpu_percent', 'memory_percent']);

        try {
            $metric = $monitor->record();
        } catch (Throwable $e) {
            Log::error('Monitoring: metrik gagal dicatat', ['error' => $e->getMessage()]);
            $this->error('Metrik gagal dicatat: ' . $e->getMessage());

            return self::FAILURE;
        }

        $updates->recordDeployment($monitor->version());

        if ($metric->status === 'danger') {
            $issues = $this->alertableIssues($monitor->lastOverall['critical'] ?? [], $previous);
            if ($issues) {
                $this->notifyAdmins($issues);
            }
        } elseif ($previous?->status === 'danger') {
            Log::info('Monitoring: status aplikasi pulih', ['status' => $metric->status]);
        }

        $this->info("Status {$metric->status} tercatat pukul {$metric->recorded_at->format('H:i')}.");

        return self::SUCCESS;
    }

    /**
     * The critical issues worth an e-mail now. CPU and memory are read from
     * the whole host, so a short spike (a Coolify build, a backup, another
     * app) only counts once it was also critical at the previous snapshot,
     * i.e. lasted about ten minutes. Each kind of problem is e-mailed at most
     * once every ALERT_COOLDOWN_HOURS, so a reading hovering around the
     * threshold does not flood the inbox.
     *
     * @param  array<int, string>  $issues  the critical ones
     * @return array<int, string>
     */
    private function alertableIssues(array $issues, ?SystemMetric $previous): array
    {
        $wasCritical = [
            'CPU' => SystemMonitorService::level($previous?->cpu_percent) === 'danger',
            'Memori' => SystemMonitorService::level($previous?->memory_percent) === 'danger',
        ];

        return array_values(array_filter($issues, function (string $issue) use ($wasCritical) {
            $kind = strtok($issue, ' ');
            if (array_key_exists($kind, $wasCritical) && ! $wasCritical[$kind]) {
                return false; // not sustained yet
            }

            return Cache::add('monitor:alert:' . $kind, true, now()->addHours(self::ALERT_COOLDOWN_HOURS));
        }));
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
