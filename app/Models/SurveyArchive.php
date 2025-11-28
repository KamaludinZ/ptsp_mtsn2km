<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyArchive extends Model
{
    protected $fillable = [
        'type',           // 'spak' or 'skm'
        'period',         // Monthly period (e.g., '2025-01' for January 2025)
        'quarter',        // Quarter identifier (e.g., '2025-Q1' for Q1 2025)
        'year',           // Year (e.g., 2025)
        'data',           // JSON data containing calculated values
        'calculated_values',// Calculated values array (casted to associative array)
        'is_quarterly_archive', // Flag indicating if this is a quarterly archive
    ];

    protected $casts = [
        'data' => 'array',
        'calculated_values' => 'array',
        'is_quarterly_archive' => 'boolean',
    ];

    // Scope to get only SPAK archives
    public function scopeSpak($query)
    {
        return $query->where('type', 'spak');
    }

    // Scope to get only SKM archives
    public function scopeSkm($query)
    {
        return $query->where('type', 'skm');
    }

    // Scope to get current month's data
    public function scopeCurrentMonth($query)
    {
        return $query->where('period', now()->format('Y-m'));
    }

    // Scope to get by specific period
    public function scopeByPeriod($query, $period)
    {
        return $query->where('period', $period);
    }

    // Scope to get by specific quarter
    public function scopeByQuarter($query, $quarter)
    {
        return $query->where('quarter', $quarter);
    }

    // Scope to get by specific year
    public function scopeByYear($query, $year)
    {
        return $query->where('year', $year);
    }
}
