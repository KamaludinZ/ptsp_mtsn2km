<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/** One routine choice in the persuratan master lists. */
class PersuratanMaster extends Model
{
    use LogsActivity {
        shouldLogEvent as private shouldLogModelEvent;
    }

    /**
     * Master Jenis SK goes to Log & Audit (log "jenis_sk"): who added,
     * renamed, switched off, reordered or deleted a jenis. The other lists
     * are routine choices and are not logged.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('jenis_sk')
            ->logOnly(['nama', 'is_active', 'sort'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $event) => match ($event) {
                'created' => 'Menambah jenis SK ' . $this->nama,
                'updated' => 'Mengubah jenis SK ' . $this->nama,
                'deleted' => 'Menghapus jenis SK ' . $this->nama,
                default => $event,
            });
    }

    protected function shouldLogEvent(string $eventName): bool
    {
        return $this->type === 'jenis_sk' && $this->shouldLogModelEvent($eventName);
    }

    public const TYPES = [
        'tujuan_naskah' => 'Tujuan Naskah',
        'jenis_surat' => 'Jenis Surat',
        'tembusan' => 'Tembusan',
        'klasifikasi' => 'Klasifikasi Surat',
        'instruksi_disposisi' => 'Instruksi Disposisi',
        'jenis_sk' => 'Jenis SK',
    ];

    protected $fillable = ['type', 'kode', 'nama', 'is_active', 'sort', 'format_nomor', 'mode_bulan', 'singkatan_unit_kerja', 'variabel_nomor', 'klasifikasi_arsip'];

    public const MODE_BULAN = ['arab' => 'Angka Arab', 'romawi' => 'Angka Romawi'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort' => 'integer',
        'variabel_nomor' => 'array',
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

    /** Jenis SK: the PTSP services whose SK start with this jenis. */
    public function layanan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Service::class, 'jenis_sk_id');
    }
}
