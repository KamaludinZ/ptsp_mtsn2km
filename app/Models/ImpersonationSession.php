<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * Satu sesi ganti akun sementara (log impersonation). Bukti audit: tidak
 * dapat dihapus; selain menutup sesi yang masih terbuka, tidak dapat diubah
 * (model ini dan trigger basis data menolaknya).
 */
class ImpersonationSession extends Model
{
    public $timestamps = false;

    public const END_REASONS = [
        'selesai' => 'Kembali ke akun admin',
        'keluar' => 'Keluar dari aplikasi',
        'kedaluwarsa' => 'Ditutup otomatis',
    ];

    protected $fillable = [
        'admin_id', 'admin_name', 'target_id', 'target_name', 'target_role',
        'reason', 'ip_address', 'user_agent', 'started_at', 'ended_at', 'end_reason',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function (ImpersonationSession $session) {
            $closing = $session->getOriginal('ended_at') === null
                && array_diff(array_keys($session->getDirty()), ['ended_at', 'end_reason']) === [];

            if (! $closing) {
                throw new LogicException('Log sesi ganti akun tidak dapat diubah.');
            }
        });
        static::deleting(fn () => throw new LogicException('Log sesi ganti akun tidak dapat dihapus.'));
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function target()
    {
        return $this->belongsTo(User::class, 'target_id');
    }

    public function scopeOpen($query)
    {
        return $query->whereNull('ended_at');
    }

    /**
     * Saringan log ganti akun (API dan panel): admin, akun (yang dipakai),
     * cari (nama/alasan), status (berjalan|selesai), cara (END_REASONS),
     * dari / sampai (tanggal mulai).
     */
    public function scopeFiltered($query, array $filters)
    {
        $like = filled($filters['cari'] ?? null) ? '%' . addcslashes((string) $filters['cari'], '%_\\') . '%' : null;

        return $query
            ->when($filters['admin'] ?? null, fn ($q, $id) => $q->where('admin_id', $id))
            ->when($filters['akun'] ?? null, fn ($q, $id) => $q->where('target_id', $id))
            ->when($like, fn ($q) => $q->where(fn ($q) => $q->where('admin_name', 'ilike', $like)
                ->orWhere('target_name', 'ilike', $like)->orWhere('reason', 'ilike', $like)))
            ->when(($filters['status'] ?? null) === 'berjalan', fn ($q) => $q->whereNull('ended_at'))
            ->when(($filters['status'] ?? null) === 'selesai', fn ($q) => $q->whereNotNull('ended_at'))
            ->when($filters['cara'] ?? null, fn ($q, $reason) => $q->where('end_reason', $reason))
            ->when($filters['dari'] ?? null, fn ($q, $date) => $q->whereDate('started_at', '>=', $date))
            ->when($filters['sampai'] ?? null, fn ($q, $date) => $q->whereDate('started_at', '<=', $date));
    }

    public function isOpen(): bool
    {
        return $this->ended_at === null;
    }
}
