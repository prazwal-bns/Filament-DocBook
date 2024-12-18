<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReviewPolicy
{
    private function isAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }


    private function isPatient(User $user, Review $review= null): bool
    {
        return $user->role === 'patient';
    }

    private function isDoctor(User $user, Review $review= null): bool
    {
        return $user->role === 'doctor';
    }


    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Review $review): bool
    {
        return $this->isAdmin($user)
            || $this->isPatient($user, $review)
            || $this->isDoctor($user, $review);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $this->isDoctor($user);
    }

    /**= null
     * Determine whether the user can update the model.
     */
    public function update(User $user, Review $review): bool
    {
        return $this->isAdmin($user) || $this->isDoctor($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Review $review): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Review $review): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Review $review): bool
    {
        return false;
    }
}
