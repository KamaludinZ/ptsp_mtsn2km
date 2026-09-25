<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Whistleblowing reports are complaints with complaint_type = whistleblowing.
 * Used by the admin dashboard widgets.
 */
class Whistleblowing extends Complaint
{
    protected $table = 'complaints';

    protected static function booted(): void
    {
        static::addGlobalScope('whistleblowing', function (Builder $query) {
            $query->where('complaints.complaint_type', 'whistleblowing');
        });

        static::creating(function (Whistleblowing $report) {
            $report->complaint_type = 'whistleblowing';
        });
    }
}
