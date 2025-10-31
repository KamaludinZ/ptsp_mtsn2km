<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Visitor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'institution',
        'purpose',
        'person_to_meet',
        'check_in_time',
        'check_out_time',
        'photo_path',
        'visitor_card_number',
        'status',
        'created_by'
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
    ];

    // Relationship with staff who registered the visitor
    public function staff()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Constants for status
    const STATUS_ACTIVE = 'active';
    const STATUS_CHECKED_OUT = 'checked_out';
}