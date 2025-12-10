<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LoginAttempt extends Model
{
    protected $fillable = [
        'email',
        'ip_address',
        'user_agent',
        'successful',
        'failure_reason',
        'attempted_at',
    ];

    protected $casts = [
        'successful' => 'boolean',
        'attempted_at' => 'datetime',
    ];

    /**
     * Log a login attempt
     */
    public static function log(string $email, bool $successful, ?string $failureReason = null): void
    {
        self::create([
            'email' => $email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'successful' => $successful,
            'failure_reason' => $failureReason,
            'attempted_at' => now(),
        ]);
    }

    /**
     * Get recent failed attempts for an email
     */
    public static function getRecentFailedAttempts(string $email, int $minutes = 60): int
    {
        return self::where('email', $email)
            ->where('successful', false)
            ->where('attempted_at', '>', Carbon::now()->subMinutes($minutes))
            ->count();
    }

    /**
     * Get recent failed attempts for an IP
     */
    public static function getRecentFailedAttemptsByIp(string $ip, int $minutes = 60): int
    {
        return self::where('ip_address', $ip)
            ->where('successful', false)
            ->where('attempted_at', '>', Carbon::now()->subMinutes($minutes))
            ->count();
    }

    /**
     * Clear old login attempts (cleanup job)
     */
    public static function clearOldAttempts(int $days = 30): int
    {
        return self::where('attempted_at', '<', Carbon::now()->subDays($days))->delete();
    }
}
