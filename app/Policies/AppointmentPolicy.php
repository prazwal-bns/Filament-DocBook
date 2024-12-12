<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AppointmentPolicy
{
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
    public function view(User $user, Appointment $appointment): bool
    {
        // return $user->patient->id == $appointment->patient_id || $user->role == 'admin';
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Appointment $appointment): bool
    {
        // return $user->patient->id == $appointment->patient_id || $user->role == 'admin';
        return true;
    }

    public function payment(User $user, Appointment $appointment): bool
    {
        return true;
        // return $user->patient->id == $appointment->patient_id || $user->role == 'admin';
    }


    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Appointment $appointment): bool
    {
        return true;
        // return $user->patient->id == $appointment->patient_id || $user->role == 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Appointment $appointment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Appointment $appointment): bool
    {
        return false;
    }

    public function addReview(User $user, Appointment $appointment){
        return $user->doctor->id === $appointment->doctor->id && $appointment->status=='completed' || $user->role == 'admin';
    }


    // public function viewReview(User $user, Appointment $appointment)
    // {
    //     return $user->doctor->id == $appointment->doctor_id || $user->patient->id == $appointment->patient_id;
    // }

    public function viewReview(User $user, Appointment $appointment)
    {
        $isDoctor = $user->doctor && $user->doctor->id === $appointment->doctor_id;
        $isPatient = $user->patient && $user->patient->id === $appointment->patient_id;

        return $isDoctor || $isPatient;
    }

}
