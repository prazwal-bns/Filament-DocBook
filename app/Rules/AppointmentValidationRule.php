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


    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $appointmentDate = $value;
        $startTime = $this->formState['start_time'] ?? null;
        $endTime = $this->formState['end_time'] ?? null;
        $doctorId = $this->formState['doctor_id'] ?? null;
        $day = $this->formState['day'] ?? null;

        // Fetch existing appointment
        $appointmentId = $this->formState['appointment_id'] ?? null;
        dd($appointmentId);
        $existingAppointment = Appointment::find($appointmentId);

        if (!$existingAppointment) {
            $fail('Appointment record not found.');
            return;
        }

        // Check if any scheduling fields were updated
        $timeOrDateUpdated = $this->isTimeOrDateUpdated($appointmentDate, $startTime, $endTime);

        if ($timeOrDateUpdated) {
            if (!$startTime || !$endTime || !$doctorId || !$day) {
                $fail('Invalid form data.');
                return;
            }

            // Validate the doctor's availability
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
                return;
            }

            // Check for overlapping appointments
            $appointments = Appointment::where('doctor_id', $doctorId)
                ->where('appointment_date', $appointmentDate)
                ->when($appointmentId, function ($query) use ($appointmentId) {
                    $query->where('id', '!=', $appointmentId); // Exclude the current appointment
                })
                ->get();

            $overlappingAppointment = $appointments->contains(function ($appointment) use ($startTime, $endTime) {
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

                $fail("The selected doctor is already booked for this time slot. He/she not available during: {$schedule}");
            }
        }

        // Additional validation for fields that don't require time/date logic
        if (empty($this->formState['appointment_reason'])) {
            $fail('Appointment reason is required.');
        }
    }

    protected function isTimeOrDateUpdated($appointmentDate, $startTime, $endTime): bool
    {
        // Fetch the existing appointment
        $appointmentId = $this->formState['appointment_id'] ?? null;
        $existingAppointment = Appointment::find($appointmentId);

        if (!$existingAppointment) {
            return false; // No existing appointment to compare, assume no updates
        }

        // Compare each field with the existing appointment data
        return $appointmentDate !== $existingAppointment->appointment_date ||
            $startTime !== $existingAppointment->start_time ||
            $endTime !== $existingAppointment->end_time;
    }


}
