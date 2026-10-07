<?php

namespace App\Support;

use App\Models\Service;
use App\Models\ServiceRequirement;
use App\Services\FrontDeskService;

/**
 * Standar pelayanan of a service: the 14 components of Permen PANRB 15/2014
 * plus the headline facts. One source for the public service page and the
 * request forms (portal and front desk), so both always show the same.
 * A services column edited in /cp wins over an older service_components row
 * for the same component; the row fills in when the column is empty.
 */
class ServiceStandard
{
    /**
     * key => [number, Font Awesome icon (public site), Heroicon (panel), title]
     *
     * @var array<string, array{0: int, 1: string, 2: string, 3: string}>
     */
    public const COMPONENTS = [
        'dasar_hukum' => [1, 'fa-scale-balanced', 'heroicon-o-scale', 'Dasar Hukum'],
        'persyaratan' => [2, 'fa-list-check', 'heroicon-o-clipboard-document-check', 'Persyaratan'],
        'mekanisme' => [3, 'fa-diagram-project', 'heroicon-o-arrows-right-left', 'Sistem, Mekanisme, dan Prosedur'],
        'jangka_waktu' => [4, 'fa-clock', 'heroicon-o-clock', 'Jangka Waktu Penyelesaian'],
        'biaya' => [5, 'fa-money-bill-wave', 'heroicon-o-banknotes', 'Biaya/Tarif'],
        'produk_layanan' => [6, 'fa-file-signature', 'heroicon-o-document-check', 'Produk Pelayanan'],
        'sarana_prasarana' => [7, 'fa-building', 'heroicon-o-building-office-2', 'Sarana dan Prasarana'],
        'kompetensi_pelaksana' => [8, 'fa-user-tie', 'heroicon-o-academic-cap', 'Kompetensi Pelaksana'],
        'pengawasan_internal' => [9, 'fa-user-shield', 'heroicon-o-shield-check', 'Pengawasan Internal'],
        'penanganan_pengaduan' => [10, 'fa-comments', 'heroicon-o-chat-bubble-left-right', 'Penanganan Pengaduan, Saran, dan Masukan'],
        'jumlah_pelaksana' => [11, 'fa-users', 'heroicon-o-user-group', 'Jumlah Pelaksana'],
        'jaminan_pelayanan' => [12, 'fa-handshake', 'heroicon-o-hand-thumb-up', 'Jaminan Pelayanan'],
        'jaminan_keamanan' => [13, 'fa-lock', 'heroicon-o-lock-closed', 'Jaminan Keamanan dan Keselamatan'],
        'evaluasi_kinerja' => [14, 'fa-chart-line', 'heroicon-o-chart-bar', 'Evaluasi Kinerja Pelaksana'],
    ];

    /** Components shown up front on request forms; the rest fold away. */
    public const ESSENTIAL = ['persyaratan', 'mekanisme', 'produk_layanan', 'penanganan_pengaduan'];

    public const MODES = ['online' => 'Online', 'offline' => 'Offline (Loket PTSP)', 'hybrid' => 'Online & Offline'];

    /**
     * The 14 components, in order.
     *
     * @return array<int, array{key: string, no: int, icon: string, heroicon: string, title: string, list: ?array, content: ?string, link: ?array{0: string, 1: string}}>
     */
    public static function items(Service $service): array
    {
        $components = $service->components->pluck('content', 'component_type');
        $requirementsText = $service->getAttributes()['requirements'] ?? null;
        $requirementsList = json_decode((string) $requirementsText, true);

        $values = [
            'persyaratan' => $requirementsText,
            'mekanisme' => $service->mechanism,
            'jangka_waktu' => $service->processing_time,
            'biaya' => self::fee($service, long: true),
            'produk_layanan' => $service->product,
            'penanganan_pengaduan' => $service->complaint_handling,
        ];
        $links = [
            'penanganan_pengaduan' => [route('supervision.complaints.dashboard'), 'Sampaikan pengaduan'],
            'evaluasi_kinerja' => [route('survey.form'), 'Isi Survei Kepuasan (SKM & SPAK)'],
        ];

        return collect(self::COMPONENTS)->map(fn (array $meta, string $key) => [
            'key' => $key,
            'no' => $meta[0],
            'icon' => $meta[1],
            'heroicon' => $meta[2],
            'title' => $meta[3],
            'list' => $key === 'persyaratan' && filled($values[$key] ?? null) && is_array($requirementsList) && array_is_list($requirementsList) ? $requirementsList : null,
            'content' => filled($values[$key] ?? null) ? $values[$key] : ($components[$key] ?? null),
            'link' => $links[$key] ?? null,
        ])->values()->all();
    }

    /**
     * Headline facts for the top of a request form.
     *
     * @return array<int, array{label: string, value: string, heroicon: string}>
     */
    public static function facts(Service $service): array
    {
        return [
            ['label' => 'Waktu penyelesaian', 'value' => $service->processing_time ?: 'Belum diisi', 'heroicon' => 'heroicon-o-clock'],
            ['label' => 'Biaya', 'value' => self::fee($service), 'heroicon' => 'heroicon-o-banknotes'],
            ['label' => 'Jalur pengajuan', 'value' => self::MODES[$service->mode] ?? 'Online & Offline', 'heroicon' => 'heroicon-o-arrows-pointing-in'],
            ['label' => 'Disposisi', 'value' => match ($mode = $service->disposition_mode) {
                'none' => 'Langsung diproses petugas',
                'custom' => 'Disposisi pimpinan',
                default => 'Disposisi ' . ServiceDisposition::mode($mode),
            }, 'heroicon' => 'heroicon-o-arrow-path-rounded-square'],
            ['label' => 'Dapat diajukan oleh', 'value' => self::applicants($service), 'heroicon' => 'heroicon-o-user-group'],
        ];
    }

    /** @return \Illuminate\Support\Collection<int, ServiceRequirement> requirement rows set up for the service */
    public static function requirementRows(Service $service)
    {
        return $service->requirements()->orderByDesc('is_required')->orderBy('id')->get();
    }

    public static function fee(Service $service, bool $long = false): string
    {
        return (float) $service->fee > 0
            ? 'Rp ' . number_format((float) $service->fee, 0, ',', '.')
            : ($long ? 'Gratis (Rp 0)' : 'Gratis');
    }

    public static function applicants(Service $service): string
    {
        $types = (array) $service->user_types_allowed;

        return $types === []
            ? 'Semua pemohon'
            : collect($types)->map(fn (string $type) => ['umum' => 'Masyarakat Umum'][$type] ?? FrontDeskService::APPLICANT_TYPES[$type] ?? ucfirst($type))->join(', ');
    }
}
