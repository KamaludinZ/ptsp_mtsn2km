<?php

namespace App\Support;

use App\Models\Service;

/**
 * Pengaturan disposisi per layanan: who decides (mode), which back-office
 * units receive the disposition, and the recommended signature model.
 */
class ServiceDisposition
{
    public const MODES = [
        'kepsek_tu' => 'Kepala Madrasah + Kepala TU',
        'kepsek' => 'Kepala Madrasah saja',
        'tu' => 'Kepala TU saja',
        'none' => 'Tanpa disposisi',
    ];

    public const MODE_DESCRIPTIONS = [
        'kepsek_tu' => 'Kepala Madrasah atau Kepala TU dapat mendisposisi permohonan.',
        'kepsek' => 'Hanya Kepala Madrasah yang mendisposisi.',
        'tu' => 'Hanya Kepala TU yang mendisposisi.',
        'none' => 'Permohonan langsung diproses petugas tanpa menunggu disposisi.',
    ];

    /** Mode => the approval_roles it stands for. */
    public const MODE_ROLES = [
        'kepsek_tu' => ['kepala_sekolah', 'kepala_tu'],
        'kepsek' => ['kepala_sekolah'],
        'tu' => ['kepala_tu'],
        'none' => [],
    ];

    /** Back-office units that can receive a disposition. */
    public const RECIPIENTS = [
        'waka_humas' => 'Waka Humas',
        'waka_kesiswaan' => 'Waka Kesiswaan',
        'waka_kurikulum' => 'Waka Kurikulum',
        'waka_sarpras' => 'Waka Sarpras',
        'tata_usaha' => 'Tata Usaha',
        'penjamin_mutu' => 'Penjamin Mutu',
    ];

    public const SIGNATURES = [
        'ttd' => 'TTD (tanda tangan basah)',
        'tte' => 'TTE (tanda tangan elektronik)',
    ];

    /** Routine instructions offered when disposing (until the persuratan master exists). */
    public const INSTRUCTIONS = [
        'Untuk diproses',
        'Untuk ditindaklanjuti',
        'Untuk diketahui',
        'Untuk dikoordinasikan',
        'Mohon saran/pertimbangan',
        'Untuk diarsipkan',
    ];

    /** Signature models for a disposition; the key prefix is what tickets.signature_type stores. */
    public const SIGNATURE_MODELS = [
        'ttd_upload' => 'TTD — cetak, tanda tangani, lalu unggah',
        'tte_upload' => 'TTE — unduh, tanda tangani elektronik, lalu unggah',
        'acknowledged_by' => 'Tanda "telah didisposisi oleh" (tanpa berkas)',
    ];

    /** tickets.signature_type value for a signature model, and back. */
    public const SIGNATURE_TYPES = ['ttd_upload' => 'ttd', 'tte_upload' => 'tte', 'acknowledged_by' => 'ack'];

    /** "TTE", "TTD" or "telah didisposisi" for a tickets.signature_type value. */
    public static function signatureTypeLabel(?string $type): string
    {
        return $type === 'ack' ? 'telah didisposisi' : strtoupper((string) $type);
    }

    public static function defaultSignatureModel(?Service $service): ?string
    {
        return match ($service?->signature_recommendation) {
            'ttd' => 'ttd_upload',
            'tte' => 'tte_upload',
            default => null,
        };
    }

    /** Mode for the stored approval fields; 'custom' when set up by hand (e.g. specific users). */
    public static function modeFor(bool $required, ?array $roles, ?array $users = null): string
    {
        if (! $required) {
            return 'none';
        }

        $roles = collect($roles ?? [])->sort()->values()->all();

        if (! $roles && ! $users) {
            return 'kepsek_tu'; // no approvers configured: the school leadership decides
        }

        foreach (self::MODE_ROLES as $mode => $modeRoles) {
            if ($modeRoles && $roles === collect($modeRoles)->sort()->values()->all() && ! $users) {
                return $mode;
            }
        }

        return 'custom';
    }

    /** Approval is on but nobody was picked, so the default rule (school leadership) applies. */
    public static function usesDefault(Service $service): bool
    {
        return (bool) $service->approval_required && ! $service->approval_roles && ! $service->approval_users;
    }

    public static function mode(?string $mode): string
    {
        return self::MODES[$mode] ?? 'Kustom';
    }

    public static function recipients(?array $roles): string
    {
        return collect($roles ?? [])->map(fn (string $role) => self::RECIPIENTS[$role] ?? $role)->join(', ') ?: '–';
    }

    public static function signature(?string $signature): string
    {
        return self::SIGNATURES[$signature] ?? 'Tidak ada anjuran';
    }

    /** "Anjuran: TTE …" shown to staff handling the service's documents. */
    public static function recommendationHint(?Service $service): ?string
    {
        return $service?->signature_recommendation
            ? 'Anjuran layanan ini: ' . self::signature($service->signature_recommendation) . '.'
            : null;
    }
}
