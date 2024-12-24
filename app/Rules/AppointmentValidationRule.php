<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Schedule;
use App\Models\Appointment;
use Carbon\Carbon;

class AppointmentValidationRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    protected $formState;

    public function __construct(array $formState)
    {
        $this->formState = $formState;
    }

    // public function validate(string $attribute, mixed $value, Closure $fail): void
    // {
    //     $appointmentDate = $value;
    //     $startTime = $this->formState['start_time'] ?? null;
    //     $endTime = $this->formState['end_time'] ?? null;
    //     $doctorId = $this->formState['doctor_id'] ?? null;
    //     $day = $this->formState['day'] ?? null;

    //     if (!$startTime || !$endTime || !$doctorId || !$day) {
    //         $fail('Invalid form data.');
    //         return;
    //     }

    //     $schedule = Schedule::where('doctor_id', $doctorId)
    //         ->where('day', $day)
    //         ->where('status', 'available')
    //         ->first();

    //     if (!$schedule) {
    //         $fail("The selected doctor is not available on this day.");
    //         return;
    //     }

    //     if (
    //         Carbon::parse($startTime)->lt($schedule->start_time) ||
    //         Carbon::parse($endTime)->gt($schedule->end_time)
    //     ) {
    //         $fail("The appointment must be scheduled between the doctor's available hours of {$schedule->start_time} - {$schedule->end_time}.");
    //     }

    //     $appointments = Appointment::where('doctor_id', $doctorId)
    //         ->where('appointment_date', $appointmentDate)
    //         ->get();

    //     $overlappingAppointment = $appointments->contains(function ($appointment) use ($startTime, $endTime) {
    //         return (
    //             (Carbon::parse($startTime)->between($appointment->start_time, $appointment->end_time)) ||
    //             (Carbon::parse($endTime)->between($appointment->start_time, $appointment->end_time)) ||
    //             (Carbon::parse($startTime)->lte($appointment->start_time) && Carbon::parse($endTime)->gte($appointment->end_time))
    //         );
    //     });

    //     if ($overlappingAppointment) {
    //         $schedule = $appointments->map(function ($appointment) {
    //             return Carbon::parse($appointment->start_time)->format('H:i') . ' - ' . Carbon::parse($appointment->end_time)->format('H:i');
    //         })->implode(', ');

    //         $fail("The selected doctor is already booked for this time slot. He/she not available during: {$schedule}");
    //     }
    // }


    // $appointmentId = $this->formState['id'];


    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $appointmentDate = $value;
        $startTime = $this->formState['start_time'] ?? null;
        $endTime = $this->formState['end_time'] ?? null;
        $doctorId = $this->formState['doctor_id'] ?? null;
        $day = $this->formState['day'] ?? null;
        $appointmentId = $this->formState['id'] ?? null; // Assuming you're passing the appointment ID

        if (!$startTime || !$endTime || !$doctorId || !$day) {
            $fail('Invalid form data.');
            return;
        }

        $schedule = Schedule::where('doctor_id', $doctorId)
            ->where('day', $day)
            ->where('status', 'available')
            ->first();

        if (!$schedule) {
            $fail("The selected doctor is not available on this day.");
            return;
        }

        if (
            Carbon::parse($startTime)->lt($schedule->start_time) ||
            Carbon::parse($endTime)->gt($schedule->end_time)
        ) {
            $fail("The appointment must be scheduled between the doctor's available hours of {$schedule->start_time} - {$schedule->end_time}.");
        }

        $appointments = Appointment::where('doctor_id', $doctorId)
            ->where('appointment_date', $appointmentDate)
            ->get();

        // Exclude the appointment being edited from the overlapping check
        $overlappingAppointment = $appointments->contains(function ($appointment) use ($startTime, $endTime, $appointmentId) {
            // Skip the current appointment if it's the one being edited
            if ($appointment->id === $appointmentId) {
                return false;
            }

            return (
                (Carbon::parse($startTime)->between($appointment->start_time, $appointment->end_time)) ||
                (Carbon::parse($endTime)->between($appointment->start_time, $appointment->end_time)) ||
                (Carbon::parse($startTime)->lte($appointment->start_time) && Carbon::parse($endTime)->gte($appointment->end_time))
            );
        });

        if ($overlappingAppointment) {
            $schedule = $appointments->map(function ($appointment) {
                return Carbon::parse($appointment->start_time)->format('H:i') . ' - ' . Carbon::parse($appointment->end_time)->format('H:i');
            })->implode(', ');

            $fail("The selected doctor is already booked for this time slot. He/she is not available during: {$schedule}");
        }
    }



}
