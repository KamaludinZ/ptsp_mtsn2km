<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One health snapshot recorded by the scheduler (monitor:record). */
class SystemMetric extends Model
{
    public $timestamps = false;

    /** Snapshots older than this are pruned. */
    public const KEEP_DAYS = 30;

    protected $fillable = [
        'recorded_at', 'app_up', 'database_ok', 'database_latency_ms', 'queue_pending', 'queue_failed',
        'scheduler_ok', 'cpu_percent', 'memory_percent', 'disk_percent', 'status',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'app_up' => 'boolean',
        'database_ok' => 'boolean',
        'scheduler_ok' => 'boolean',
        'database_latency_ms' => 'float',
        'cpu_percent' => 'float',
        'memory_percent' => 'float',
        'disk_percent' => 'float',
    ];
}
