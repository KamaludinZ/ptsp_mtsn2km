<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'user_types_allowed',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'user_types_allowed' => 'array',
        'is_active' => 'boolean',
    ];

    // Relationship with users who created the service
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relationship with service requirements
    public function requirements()
    {
        return $this->hasMany(ServiceRequirement::class);
    }

    // Relationship with service components
    public function components()
    {
        return $this->hasMany(ServiceComponent::class);
    }

    // Relationship with tickets
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    // Relationship with service categories
    public function categories()
    {
        return $this->belongsToMany(ServiceCategory::class, 'service_category_service');
    }

    // Relationship with workflow
    public function workflow()
    {
        return $this->hasOne(Workflow::class);
    }
}