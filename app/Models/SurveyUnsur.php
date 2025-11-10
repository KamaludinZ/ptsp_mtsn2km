<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyUnsur extends Model
{
    protected $table = 'survey_unsur';
    protected $fillable = [
        'name',
        'code',
        'description',
        'survey_type',
        'order',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    // Relationship with survey questions
    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class, 'unsur_id');
    }

    // Scope for active unsur
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    // Scope for specific survey type
    public function scopeBySurveyType($query, $surveyType)
    {
        return $query->where('survey_type', $surveyType);
    }
}
