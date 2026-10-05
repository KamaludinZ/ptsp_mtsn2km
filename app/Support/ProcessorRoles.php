<?php

namespace App\Support;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Peran pemroses naskah: the back-office units that receive dispositions
 * (ServiceDisposition::RECIPIENTS), with what each one handles, who holds
 * the role and how many services send their dispositions to it.
 */
class ProcessorRoles
{
    public const DESCRIPTIONS = [
        'waka_humas' => 'Hubungan masyarakat, kerja sama, dan publikasi madrasah.',
        'waka_kesiswaan' => 'Urusan siswa: kegiatan, tata tertib, beasiswa, dan alumni.',
        'waka_kurikulum' => 'Kurikulum, jadwal, penilaian, dan administrasi pembelajaran.',
        'waka_sarpras' => 'Sarana prasarana: peminjaman, perawatan, dan inventaris.',
        'tata_usaha' => 'Administrasi umum, persuratan, kepegawaian, dan arsip.',
        'penjamin_mutu' => 'Penjaminan mutu, akreditasi, dan evaluasi layanan.',
    ];

    public const ICONS = [
        'waka_humas' => 'heroicon-o-megaphone',
        'waka_kesiswaan' => 'heroicon-o-academic-cap',
        'waka_kurikulum' => 'heroicon-o-book-open',
        'waka_sarpras' => 'heroicon-o-building-office-2',
        'tata_usaha' => 'heroicon-o-document-text',
        'penjamin_mutu' => 'heroicon-o-check-badge',
    ];

    /**
     * One row per processor role, in display order.
     *
     * @return array<int, array{name: string, label: string, description: string, icon: string, role: ?Role, holders: \Illuminate\Support\Collection, services: int}>
     */
    public static function overview(): array
    {
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', array_keys(ServiceDisposition::RECIPIENTS))
            ->get()
            ->keyBy('name');

        // Loaded per role, not eagerly: Spatie resolves the user model from the role's own guard,
        // and an eager load would fall back to the request's default guard (sanctum in the API).
        $holders = $roles->map(fn (Role $role) => $role->users()->select('users.id', 'users.name')->orderBy('name')->get());

        return collect(ServiceDisposition::RECIPIENTS)
            ->map(fn (string $label, string $name) => [
                'name' => $name,
                'label' => $label,
                'description' => self::DESCRIPTIONS[$name] ?? '',
                'icon' => self::ICONS[$name] ?? 'heroicon-o-user-group',
                'role' => $roles->get($name),
                'holders' => $holders->get($name) ?? collect(),
                'services' => Service::whereJsonContains('disposition_roles', $name)->count(),
            ])
            ->values()
            ->all();
    }

    /** @return array<int, string> Active staff who can open the back office: the only valid holders. */
    public static function eligibleStaff(): array
    {
        return User::permission('backoffice.access')
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * Penugasan staf: make exactly these users the holders of one unit.
     *
     * @param  array<int, int>  $userIds  eligibleStaff() keys
     */
    public static function assign(string $unit, array $userIds): Role
    {
        abort_unless(array_key_exists($unit, ServiceDisposition::RECIPIENTS), 404);

        $role = Role::findOrCreate($unit, 'web');
        $role->users()->sync($userIds);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $role;
    }

    /** @return array<int, string> The processor roles this user holds. */
    public static function unitsOf(?User $user): array
    {
        return $user
            ? $user->getRoleNames()->intersect(array_keys(ServiceDisposition::RECIPIENTS))->values()->all()
            : [];
    }

    /** Limit a ticket query to services that send their dispositions to one of these units. */
    public static function scopeTicketsFor(Builder $query, array $units): Builder
    {
        return $query->whereHas('service', fn (Builder $q) => $q->where(function (Builder $q) use ($units) {
            foreach ($units as $unit) {
                $q->orWhereJsonContains('disposition_roles', $unit);
            }
        }));
    }
}
