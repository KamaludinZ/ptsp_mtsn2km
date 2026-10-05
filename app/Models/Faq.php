<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class Faq extends Model
{
    protected $fillable = ['question', 'answer', 'category', 'sort', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'sort' => 'integer'];

    /** Published questions in display order: by category, then by hand-set order. */
    public function scopePublished($query)
    {
        return $query->where('is_active', true)->orderByRaw('category is null')->orderBy('category')->orderBy('sort')->orderBy('id');
    }

    public function scopeInCategory($query, ?string $category)
    {
        return filled($category) ? $query->where('category', $category) : $query->whereNull('category');
    }

    /** Categories already in use, for suggestions and filters. */
    public static function categories(): array
    {
        return static::query()->whereNotNull('category')->where('category', '<>', '')
            ->distinct()->orderBy('category')->pluck('category')->all();
    }

    /** Next position at the end of the list. */
    public static function nextSort(): int
    {
        return (int) static::max('sort') + 1;
    }

    /** "Umum" for an empty category. */
    public function categoryLabel(): string
    {
        return filled($this->category) ? \Illuminate\Support\Str::headline($this->category) : 'Umum';
    }

    /**
     * FAQ answers are rich text written in the admin panel and shown on the
     * public "Tentang" page; strip scripts, event handlers and unsafe URLs.
     */
    public function safeAnswer(): string
    {
        static $sanitizer;

        $sanitizer ??= new HtmlSanitizer(
            (new HtmlSanitizerConfig())
                ->allowSafeElements()
                ->allowLinkSchemes(['https', 'http', 'mailto', 'tel'])
                ->allowRelativeLinks()
        );

        return $sanitizer->sanitize((string) $this->answer);
    }
}
