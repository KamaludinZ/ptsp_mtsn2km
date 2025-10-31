<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'question_text',
        'question_type',
        'order',
        'is_required'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'order' => 'integer'
    ];

    // Relationship with survey
    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    // Relationship with survey answers
    public function answers()
    {
        return $this->hasMany(SurveyAnswer::class);
    }

    // Constants for question types
    const TYPE_RATING = 'rating';
    const TYPE_MULTIPLE_CHOICE = 'multiple_choice';
    const TYPE_TEXT = 'text';
    const TYPE_YES_NO = 'yes_no';
}