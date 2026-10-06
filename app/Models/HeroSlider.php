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

    /*
     * The slide has the height of the static hero (--hero-height in home.css:
     * about 4:1 on desktop, 2.8:1 on a laptop, portrait on a phone). Images are
     * stored 16:5 at 1920×600 for computers, and optionally 2:3 at 1080×1620 for phones.
     */
    public const ASPECT_RATIO = '16:5';

    public const WIDTH = 1920;

    public const HEIGHT = 600;

    public const MOBILE_ASPECT_RATIO = '2:3';

    public const MOBILE_WIDTH = 1080;

    public const MOBILE_HEIGHT = 1620;

    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'image',
        'image_mobile',
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
            foreach (['image', 'image_mobile'] as $column) {
                if ($slide->wasChanged($column) && $slide->getOriginal($column)) {
                    Storage::disk(self::DISK)->delete($slide->getOriginal($column));
                }
            }
        });
        static::deleted(fn (HeroSlider $slide) => Storage::disk(self::DISK)->delete(array_filter([$slide->image, $slide->image_mobile])));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Root-relative URL (/storage/…): the page's own host and port, whatever
     * APP_URL says, so the image always satisfies the img-src 'self' policy.
     */
    public function imageUrl(): ?string
    {
        return $this->image ? '/storage/' . ltrim($this->image, '/') : null;
    }

    /** Portrait image for phones, if one was uploaded. */
    public function mobileImageUrl(): ?string
    {
        return $this->image_mobile ? '/storage/' . ltrim($this->image_mobile, '/') : null;
    }
}
