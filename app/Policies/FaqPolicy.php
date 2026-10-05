<?php

namespace App\Policies;

use App\Models\Faq;
use App\Models\User;

/** FAQ (Manajemen Konten): administrators manage it; the public list is open to all. */
class FaqPolicy
{
    public function viewAny(User $user): bool
    {
        return PengumumanPolicy::isContentAdmin($user);
    }

    public function view(User $user, Faq $faq): bool
    {
        return PengumumanPolicy::isContentAdmin($user);
    }

    public function create(User $user): bool
    {
        return PengumumanPolicy::isContentAdmin($user);
    }

    public function update(User $user, Faq $faq): bool
    {
        return PengumumanPolicy::isContentAdmin($user);
    }

    /** Change the display order (drag rows, one step up/down). */
    public function reorder(User $user): bool
    {
        return PengumumanPolicy::isContentAdmin($user);
    }

    public function delete(User $user, Faq $faq): bool
    {
        return PengumumanPolicy::isContentAdmin($user);
    }

    public function deleteAny(User $user): bool
    {
        return PengumumanPolicy::isContentAdmin($user);
    }
}
