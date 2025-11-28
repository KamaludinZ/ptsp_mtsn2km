<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'is_active',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relationship with survey questions
    public function questions()
    {
        return $this->hasMany(SurveyQuestion::class);
    }

    // Relationship with survey responses
    public function responses()
    {
        return $this->hasMany(SurveyResponse::class);
    }

    // Constants for survey types
    const TYPE_SKM = 'skm';  // Survei Kepuasan Masyarakat
    const TYPE_SPAK = 'spak'; // Survei Persepsi Anti Korupsi
    const TYPE_OTHER = 'other';
}