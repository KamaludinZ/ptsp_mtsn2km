<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroSlider extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'image',
        'is_active',
        'sort_order',
        'button1_text',
        'button1_url',
        'button2_text',
        'button2_url',
        'text_color',
        'overlay_color',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope untuk mendapatkan slider yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Get the URL for the image
     */
    public function getImageUrlAttribute()
    {
        return $this->image ? asset($this->image) : null;
    }
}