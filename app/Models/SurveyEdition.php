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

    protected static function booted(): void
    {
        // Only one edition may be active: activating one switches the rest off
        // (a partial unique index backs this up in the database).
        static::saving(function (SurveyEdition $edition) {
            if ($edition->is_active && $edition->isDirty('is_active')) {
                static::query()
                    ->when($edition->exists, fn ($q) => $q->whereKeyNot($edition->getKey()))
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }
        });
    }

    /** The edition the public survey currently counts responses in, if any. */
    public static function current(): ?self
    {
        return static::active()->first();
    }

    /** "1 Jul – 30 Sep 2026" */
    public function dateRange(): ?string
    {
        if (! $this->start_date || ! $this->end_date) {
            return null;
        }

        return $this->start_date->translatedFormat('j M') . ' – ' . $this->end_date->translatedFormat('j M Y');
    }

    /** Survey responses counted in this edition. */
    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class, 'survey_edition_id');
    }

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
