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
        // Get current blocked IPs
        $blockedIPs = Cache::get('blocked_ips', []);

        // Remove localhost IPs if they were blocked
        $localhostIPs = ['127.0.0.1', '::1', 'localhost'];

        foreach ($localhostIPs as $ip) {
            if (isset($blockedIPs[$ip])) {
                unset($blockedIPs[$ip]);
                $this->info("Removed {$ip} from blocked IPs");
            } else {
                $this->info("{$ip} was not in the blocked list");
            }
        }

        // Update the blocked IPs cache
        Cache::put('blocked_ips', $blockedIPs, now()->addYears(10));

        $this->info('Successfully processed localhost IP addresses for development.');
        $this->info('Localhost IP addresses will now be excluded from blocking during development.');
    }
}
