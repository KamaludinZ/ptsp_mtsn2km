<?php

namespace App\Exceptions;

use App\Models\User;
use App\Support\ActiveRoles;
use App\Support\RoleAccess;

/**
 * Aksi tiket yang ditolak karena peran aktif tidak berwenang, padahal user
 * memegang peran lain yang berwenang. Pesannya menyebut peran aktif dan
 * peran yang perlu diaktifkan; panel menampilkannya dengan tombol ganti
 * peran (ActiveRoleToast::outsideRole).
 */
class OutsideActiveRoleException extends TicketActionException
{
    public function __construct(
        string $message,
        public readonly ?string $activeRole = null,
        public readonly ?string $suggestedRole = null,
    ) {
        parent::__construct($message);
    }

    /**
     * @param  string  $action  what was refused, e.g. "memutuskan permohonan ini"
     * @param  array<int, string>  $allowedRoles  roles that may take the action
     */
    public static function for(User $user, string $action, array $allowedRoles): self
    {
        $active = ActiveRoles::inContext($user);
        $suggested = ActiveRoles::for($user)->pluck('name')
            ->first(fn (string $role) => $role !== $active && in_array($role, $allowedRoles, true));

        $message = $active
            ? 'Peran aktif Anda (' . RoleAccess::roleLabel($active) . ") tidak berwenang {$action}."
            : "Pilih peran aktif dulu untuk {$action}.";
        if ($suggested) {
            $message .= ' Aktifkan peran ' . RoleAccess::roleLabel($suggested) . ' untuk melanjutkan.';
        }

        return new self($message, $active, $suggested);
    }
}
