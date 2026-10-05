<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * One slide of the homepage hero. With no active slide the homepage keeps
 * its static hero, so the slider is optional content, not a requirement.
 */
class HeroSlider extends Model
{
    public const DISK = 'public';

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

    protected $attributes = ['is_active' => true, 'sort_order' => 0, 'text_color' => '#ffffff', 'overlay_color' => 'rgba(0,0,0,0.4)'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        // A replaced or deleted image is not kept.
        static::updated(function (HeroSlider $slide) {
            if ($slide->wasChanged('image') && $slide->getOriginal('image')) {
                Storage::disk(self::DISK)->delete($slide->getOriginal('image'));
            }
        });
        static::deleted(fn (HeroSlider $slide) => $slide->image && Storage::disk(self::DISK)->delete($slide->image));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function imageUrl(): ?string
    {
        return $this->image ? Storage::disk(self::DISK)->url($this->image) : null;
    }
}
