<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'survey_edition_id',
        'user_id',
        'ticket_id',
        'ticket_code',
        'respondent_email',
        'ip_address',
        'completed_at'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function edition()
    {
        return $this->belongsTo(SurveyEdition::class, 'survey_edition_id');
    }

    // Relationship with survey
    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    // Relationship with user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with ticket
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    // Relationship with survey answers
    public function answers()
    {
        return $this->hasMany(SurveyAnswer::class);
    }
}