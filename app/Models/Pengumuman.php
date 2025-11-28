<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengumuman extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'category',
        'publish_date',
        'end_date',
        'is_active',
        'author',
        'user_id',
        'attachment',
        'url',
        'view_count',
    ];

    protected $casts = [
        'publish_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('publish_date', '<=', now())
                    ->where(function($query) {
                        $query->whereNull('end_date')
                              ->orWhere('end_date', '>=', now());
                    });
    }

    public function views()
    {
        return $this->hasMany(PengumumanView::class);
    }

    public function getUniqueViewCountAttribute()
    {
        return $this->views()->count();
    }
}
