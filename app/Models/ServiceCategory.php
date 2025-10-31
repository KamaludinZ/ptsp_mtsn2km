<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'parent_id',
        'order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer'
    ];

    // Relationship with parent category
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    // Relationship with child categories
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    // Relationship with services
    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_category_service');
    }
}