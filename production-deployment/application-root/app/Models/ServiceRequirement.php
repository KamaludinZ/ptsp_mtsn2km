<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'requirement_name',
        'is_required',
        'description'
    ];

    protected $casts = [
        'is_required' => 'boolean'
    ];

    // Relationship with service
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}