<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Guest-book choices: who to meet (tujuan) and the reason (keperluan). */
class VisitorMaster extends Model
{
    public const TYPES = ['tujuan' => 'Tujuan kunjungan', 'keperluan' => 'Keperluan'];

    /** The free-text choice, always offered last. */
    public const OTHER = 'Lainnya';

    protected $fillable = ['type', 'nama', 'is_active', 'sort'];

    protected $casts = ['is_active' => 'boolean', 'sort' => 'integer'];

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type)->where('is_active', true)->orderBy('sort')->orderBy('nama');
    }

    /** @return array<int, string> active names, with "Lainnya" last */
    public static function options(string $type): array
    {
        $names = static::ofType($type)->pluck('nama')->reject(fn ($name) => $name === self::OTHER)->values()->all();

        return [...$names, self::OTHER];
    }
}
