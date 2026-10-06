<?php

namespace App\Support;

use App\Models\Ticket;
use App\Models\User;

/**
 * Data tiruan untuk panel "Aksi untuk peran aktif" di halaman tiket
 * (Fase 2, frontend): matriks statis aksi → peran yang berwenang. Bentuk
 * tiap baris adalah kontrak yang nanti diisi backend dari TicketPolicy
 * (izin per layanan dan status tiket); sampai saat itu panel memakai
 * matriks ini dan tidak menilai status tiket.
 */
class TicketRoleActionStub
{
    /** Aksi tiket utama dan peran yang (secara umum) berwenang mengambilnya. */
    public const ACTIONS = [
        'approve' => [
            'label' => 'Disposisi / tolak permohonan',
            'icon' => 'heroicon-o-check-badge',
            'roles' => ['kepala_sekolah', 'kepala_tu', 'admin'],
        ],
        'process' => [
            'label' => 'Proses tiket (tugaskan, ubah status, unggah hasil)',
            'icon' => 'heroicon-o-cog-6-tooth',
            'roles' => ['back_office', 'kepala_tu', 'kepala_sekolah', 'admin', 'waka_humas', 'waka_kesiswaan', 'waka_kurikulum', 'waka_sarpras', 'tata_usaha', 'penjamin_mutu'],
        ],
        'handOver' => [
            'label' => 'Serahkan produk layanan ke pemohon',
            'icon' => 'heroicon-o-hand-raised',
            'roles' => ['front_desk', 'kepala_tu', 'admin'],
        ],
        'delete' => [
            'label' => 'Hapus tiket',
            'icon' => 'heroicon-o-trash',
            'roles' => ['admin'],
        ],
    ];

    /**
     * @return list<array{key: string, label: string, icon: string, allowed: bool, roles: list<string>, switch_to: ?array{name: string, label: string}}>
     */
    public static function for(Ticket $ticket, User $user): array
    {
        $active = ActiveRoles::inContext($user);
        $held = ActiveRoles::for($user)->pluck('name');

        return collect(self::ACTIONS)->map(function (array $action, string $key) use ($active, $held) {
            $allowed = $active !== null && in_array($active, $action['roles'], true);
            // A role the user holds that would allow it, to offer a quick switch.
            $other = $allowed ? null : $held->first(fn (string $role) => in_array($role, $action['roles'], true));

            return [
                'key' => $key,
                'label' => $action['label'],
                'icon' => $action['icon'],
                'allowed' => $allowed,
                'roles' => array_map(RoleAccess::roleLabel(...), $action['roles']),
                'switch_to' => $other ? ['name' => $other, 'label' => RoleAccess::roleLabel($other)] : null,
            ];
        })->values()->all();
    }
}
