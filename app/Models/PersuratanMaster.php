<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** One routine choice in the persuratan master lists. */
class PersuratanMaster extends Model
{
    public const TYPES = [
        'tujuan_naskah' => 'Tujuan Naskah',
        'tembusan' => 'Tembusan',
        'klasifikasi' => 'Klasifikasi Surat',
        'instruksi_disposisi' => 'Instruksi Disposisi',
    ];

    protected $fillable = ['type', 'kode', 'nama', 'is_active', 'sort'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type)->where('is_active', true)->orderBy('sort')->orderBy('nama');
    }

    /** What the forms offer: the code for classifications, the name otherwise. */
    public function getValueAttribute(): string
    {
        return $this->type === 'klasifikasi' && $this->kode ? $this->kode : $this->nama;
    }
}
