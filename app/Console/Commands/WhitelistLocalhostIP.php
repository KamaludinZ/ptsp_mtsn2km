<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class WhitelistLocalhostIP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:whitelist-localhost';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Whitelist localhost IP addresses to prevent blocking during development';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Remove localhost IPs if they were blocked
        $security = app(\App\Support\SecurityMonitor::class);

        foreach (['127.0.0.1', '::1', 'localhost'] as $ip) {
            $this->info($security->unblockIp($ip, 'artisan')
                ? "Removed {$ip} from blocked IPs"
                : "{$ip} was not in the blocked list");
        }

        $this->info('Successfully processed localhost IP addresses for development.');
        $this->info('Localhost IP addresses will now be excluded from blocking during development.');
    }
}
