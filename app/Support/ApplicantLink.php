<?php

namespace App\Support;

use App\Models\User;

/**
 * Hasil penautan akun pemohon (loket dan pendaftaran portal): akun yang
 * dipakai, bagaimana akun itu didapat, dan lewat apa ia dicocokkan.
 */
final class ApplicantLink
{
    /** Dipilih petugas dari daftar pemohon terdaftar. */
    public const PICKED = 'terpilih';

    /** Akun yang sudah ada, ditemukan lewat email atau WhatsApp. */
    public const LINKED = 'tertaut';

    /** Belum ada yang cocok: akun pemohon baru dibuat. */
    public const CREATED = 'baru';

    /** @param  ?string  $matchedBy  'email' | 'whatsapp' (hanya untuk LINKED) */
    public function __construct(
        public readonly User $user,
        public readonly string $status,
        public readonly ?string $matchedBy = null,
    ) {
    }

    public function isNew(): bool
    {
        return $this->status === self::CREATED;
    }

    /** Kalimat untuk petugas, mis. "Permohonan masuk ke akun Bu Sari yang sudah ada (cocok lewat WhatsApp)." */
    public function message(): string
    {
        return match ($this->status) {
            self::PICKED => 'Permohonan masuk ke akun terpilih: ' . $this->user->name . '.',
            self::LINKED => 'Permohonan masuk ke akun ' . $this->user->name . ' yang sudah ada (cocok lewat ' . ($this->matchedBy === 'email' ? 'email' : 'WhatsApp') . ').',
            default => 'Akun pemohon baru dibuat untuk ' . $this->user->name . '.',
        };
    }

    /** @return array{status: string, dicocokkan_lewat: ?string, pemohon_id: int} */
    public function toArray(): array
    {
        return ['status' => $this->status, 'dicocokkan_lewat' => $this->matchedBy, 'pemohon_id' => $this->user->id];
    }
}
