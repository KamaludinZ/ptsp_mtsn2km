<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Several letter numbers reserved in one go. */
class SuratKeluarBatch extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['tahun', 'jumlah_diminta', 'nomor_awal', 'nomor_akhir', 'created_by'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function letters(): HasMany
    {
        return $this->hasMany(SuratKeluar::class, 'batch_id');
    }
}
