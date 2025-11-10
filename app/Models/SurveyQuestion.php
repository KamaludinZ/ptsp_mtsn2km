<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'question',
        'options',
        'field_type',
        'order',
        'is_required',
        'is_active',
        'survey_type',
        'category',
        'unsur_id'
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'order' => 'integer'
    ];

    // Relationship with survey answers
    public function answers()
    {
        return $this->hasMany(SurveyAnswer::class, 'survey_question_id');
    }

    // Relationship with survey unsur
    public function unsur()
    {
        return $this->belongsTo(SurveyUnsur::class, 'unsur_id');
    }

    // Scope for active questions
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope by type
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Scope by survey type
    public function scopeBySurveyType($query, $surveyType)
    {
        return $query->where('survey_type', $surveyType);
    }

    // Scope by category
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Scope by unsur
    public function scopeByUnsur($query, $unsurId)
    {
        return $query->where('unsur_id', $unsurId);
    }

    // Scope ordered
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }
}