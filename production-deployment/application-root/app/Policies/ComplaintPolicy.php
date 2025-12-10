<?php

namespace App\Policies;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ComplaintPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admin dapat melihat semua komplain
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Complaint $complaint): bool
    {
        // Admin dapat melihat semua komplain
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Pengguna hanya bisa melihat komplain milik mereka sendiri
        return $user->id === $complaint->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Semua pengguna terverifikasi bisa membuat komplain
        return $user->hasVerifiedEmail();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Complaint $complaint): bool
    {
        // Admin dapat mengupdate semua komplain
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Pengguna hanya bisa mengupdate komplain milik mereka sendiri
        return $user->id === $complaint->user_id && $complaint->status === 'submitted';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Complaint $complaint): bool
    {
        // Admin dapat menghapus semua komplain
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Pengguna hanya bisa menghapus komplain milik mereka sendiri
        return $user->id === $complaint->user_id && $complaint->status === 'submitted';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Complaint $complaint): bool
    {
        // Hanya admin yang bisa mengembalikan komplain yang dihapus
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Complaint $complaint): bool
    {
        // Hanya admin yang bisa menghapus permanen komplain
        return $user->hasRole('admin');
    }
}
