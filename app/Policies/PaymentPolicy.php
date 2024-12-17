<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaymentPolicy
{
    private function isAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }


    private function isPatient(User $user, Payment $payment = null): bool
    {
        return $user->role === 'patient';
    }

    private function isDoctor(User $user, Payment $appointment = null): bool
    {
        return $user->role === 'doctor';
    }


    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ($user->patient || $user->doctor) {
            return true; // the results are filtered through  query
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Payment $payment): bool
    {
        return $this->isAdmin($user)
            || $this->isPatient($user, $payment)
            || $this->isDoctor($user, $payment);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $this->isPatient($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Payment $payment): bool
    {
        return $this->isAdmin($user) || $this->isPatient($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Payment $payment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Payment $payment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Payment $payment): bool
    {
        return false;
    }
}
