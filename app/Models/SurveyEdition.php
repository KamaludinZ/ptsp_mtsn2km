<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyEdition extends Model
{
    protected $fillable = [
        'name',
        'type',
        'period',
        'year',
        'description',
        'start_date',
        'end_date',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    // Relationship with surveys
    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }

    // Scope for active editions
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    // Scope for specific year
    public function scopeByYear($query, $year)
    {
        return $query->where('year', $year);
    }
    
    // Scope for specific type
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
