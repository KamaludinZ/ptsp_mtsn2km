<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/** One outgoing letter number in the register (buku register surat keluar). */
class SuratKeluar extends Model
{
    use LogsActivity;

    protected $table = 'surat_keluar';

    /** Reserving (per request) and deleting (with its reason) are logged by SuratKeluarService; changes per letter. */
    protected static $recordEvents = ['updated'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('surat_keluar')
            ->logOnly(['nomor_surat', 'tanggal_surat', 'tujuan_surat', 'perihal', 'jenis_surat', 'klasifikasi', 'lampiran', 'tembusan', 'keterangan', 'berkas_lampiran_nama'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $event) => match ($event) {
                'updated' => 'Mengubah data surat keluar ' . $this->nomor_surat,
                default => $event,
            });
    }

    protected $fillable = [
        'tahun',
        'nomor_urut',
        'nomor_surat',
        'tanggal_surat',
        'tujuan_surat',
        'perihal',
        'jenis_surat',
        'klasifikasi',
        'variabel',
        'lampiran',
        'tembusan',
        'keterangan',
        'berkas_lampiran',
        'berkas_lampiran_nama',
        'pembuat_id',
        'batch_id',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'tahun' => 'integer',
        'nomor_urut' => 'integer',
        'berkas_lampiran' => 'array',
        'berkas_lampiran_nama' => 'array',
    ];

    /** Letters whose jenis surat is really an SK (Persuratan::isSuratKeputusan), numbered here by mistake. */
    public function scopeNumberedAsSk(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('jenis_surat', '~*', '\\mkeputusan\\M|^\\s*sk\\M');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembuat_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SuratKeluarBatch::class, 'batch_id');
    }

    /** Reserved but the letter itself is not described yet. */
    public function isDraft(): bool
    {
        return blank($this->perihal) || blank($this->tujuan_surat);
    }

    /** Original name of an uploaded attachment, falling back to its stored name. */
    public function attachmentName(string $path): string
    {
        return $this->berkas_lampiran_nama[$path] ?? basename($path);
    }
}
