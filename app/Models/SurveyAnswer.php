<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_response_id',
        'survey_question_id',
        'answer_text',
        'rating_value',
        'selected_option'
    ];

    protected $casts = [
        'rating_value' => 'integer'
    ];

    // Relationship with survey response
    public function response()
    {
        return $this->belongsTo(SurveyResponse::class, 'survey_response_id');
    }

    // Relationship with survey question
    public function question()
    {
        return $this->belongsTo(SurveyQuestion::class, 'survey_question_id');
    }
}