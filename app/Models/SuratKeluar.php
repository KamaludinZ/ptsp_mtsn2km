<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One outgoing letter number in the register (buku register surat keluar). */
class SuratKeluar extends Model
{
    protected $table = 'surat_keluar';

    protected $fillable = [
        'tahun',
        'nomor_urut',
        'nomor_surat',
        'tanggal_surat',
        'tujuan_surat',
        'perihal',
        'jenis_surat',
        'klasifikasi',
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
