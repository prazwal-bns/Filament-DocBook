<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AppointmentPolicy
{
      /**
     * Determine if the user is an admin.
     */
    private function isAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine if the user is a patient and the appointment relates to them.
     */
    private function isPatient(User $user, Appointment $appointment = null): bool
    {
        return $user->patient && (!$appointment || $user->patient->id === $appointment->patient_id);
    }

    /**
     * Determine if the user is a doctor and the appointment relates to them.
     */
    private function isDoctor(User $user, Appointment $appointment = null): bool
    {
        return $user->doctor && (!$appointment || $user->doctor->id === $appointment->doctor_id);
    }


    /**
      * Allow admins to view all, patients to view their own, and doctors to view their specific appointments.
    */
    // public function viewAny(User $user): bool
    // {
    //     return $this->isAdmin($user) || $user->patient || $user->doctor;
    // }

    public function viewAny(User $user): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ($user->patient || $user->doctor) {
            return true;
        }

        return false;
    }

    /**
         * Allow viewing only if:
         * - Admin
         * - The appointment relates to the doctor
         * - The appointment relates to the patient
     */
    public function view(User $user, Appointment $appointment): bool
    {
        return $this->isAdmin($user)
            || $this->isPatient($user, $appointment)
            || $this->isDoctor($user, $appointment);
    }

    /**
     * Allow creation of appointments for everyone.
     */
    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $user->patient;
    }

    /**
         * Allow updating if:
         * - Admin
         * - The appointment relates to the patient
    */
    public function update(User $user, Appointment $appointment): bool
    {
        return $this->isAdmin($user) || $this->isPatient($user, $appointment);
    }


    /**
         * Allow deletion if:
         * - Admin
         * - The appointment relates to the patient
    */
    public function delete(User $user, Appointment $appointment): bool
    {
        return $this->isAdmin($user) || $this->isPatient($user, $appointment);
    }

        /**
     * Disallow restoration.
     */
    public function restore(User $user, Appointment $appointment): bool
    {
        return false;
    }

    /**
     * Disallow force deletion.
     */
    public function forceDelete(User $user, Appointment $appointment): bool
    {
        return false;
    }

    // public function addReview(User $user, Appointment $appointment){
    //     return $user->doctor->id === $appointment->doctor->id && $appointment->status=='completed' || $user->role == 'admin';
    // }

    // public function payment(User $user, Appointment $appointment): bool
    // {
    //     return true;
    //     // return $user->patient->id == $appointment->patient_id || $user->role == 'admin';
    // }

    // public function viewReview(User $user, Appointment $appointment)
    // {
    //     $isDoctor = $user->doctor && $user->doctor->id === $appointment->doctor_id;
    //     $isPatient = $user->patient && $user->patient->id === $appointment->patient_id;

    //     return $isDoctor || $isPatient;
    // }

}
