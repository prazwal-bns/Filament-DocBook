<?php

namespace App\Services;
use App\Mail\AppointmentSent;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AppointmentService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    // for storing appointment
    public function storeAppointment($validated)
    {
        $day = Carbon::parse($validated['appointment_date'])->format('l');
        $validated['day'] = $day;

        $doctor = Doctor::findOrFail($validated['doctor_id']);

        if ($doctor->status === 'not_available') {
            return [
                'status' => 'error',
                'message' => 'The selected doctor is currently not available for appointments.',
            ];
        }

        // Retrieve the doctor’s schedule for the selected day
        $dayOfWeek = Carbon::parse($validated['appointment_date'])->format('l');
        $schedule = $doctor->schedules()->where('day', $dayOfWeek)->first();

        if (!$schedule) {
            return [
                'status' => 'error',
                'message' => 'The doctor does not have a schedule for the selected day.',
            ];
        }

        if ($schedule->status !== 'available') {
            return [
                'status' => 'error',
                'message' => 'The doctor is currently not available on the selected day.',
            ];
        }

        // Parse appointment times
        $appointmentStart = Carbon::parse($validated['start_time']);
        $appointmentEnd = Carbon::parse($validated['end_time']);
        $scheduleStart = Carbon::parse($schedule->start_time);
        $scheduleEnd = Carbon::parse($schedule->end_time);

        if (!($appointmentStart->format('H:i') >= $scheduleStart->format('H:i') &&
            $appointmentEnd->format('H:i') <= $scheduleEnd->format('H:i'))) {
            return [
                'status' => 'error',
                'message' => 'The selected time is not within the doctor\'s available schedule.',
            ];
        }

        // All checks passed, create the appointment
        $appointment = Appointment::create([
            'patient_id' => auth()->user()->patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => $validated['appointment_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'appointment_reason' => $validated['appointment_reason'],
            'status' => 'pending',
            'day' => $validated['day'],
        ]);

        $price = 1000;

        // Create the payment record
        Payment::create([
            'appointment_id' => $appointment->id,
            'payment_status' => 'unpaid',
            'amount' => $price,
        ]);

        // Notify the doctor via email
        $doctorEmail = $doctor->user->email;
        Mail::to($doctorEmail)->send(new AppointmentSent($appointment));

        return [
            'status' => 'success',
            'message' => 'Appointment booked successfully!',
            'appointment' => $appointment,
            'payment' => [
                'amount' => $price,
                'payment_status' => 'unpaid',
            ],
        ];
    }

    // for updating appointment
    public function updateAppointment(Appointment $appointment, array $validated)
    {
        // Parse the appointment date and add the day
        $day = Carbon::parse($validated['appointment_date'])->format('l');
        $validated['day'] = $day;

        // Check if the patient owns the appointment
        $patient = Auth::user()->patient;
        if (!$patient || $appointment->patient_id !== $patient->id) {
            throw new \Exception('You are not authorized to update this appointment.');
        }

        // Ensure the appointment is pending
        if ($appointment->status !== 'pending') {
            throw new \Exception('This appointment is already confirmed and cannot be edited.');
        }

        // Retrieve the doctor and check availability
        $doctor = Doctor::findOrFail($validated['doctor_id']);
        if ($doctor->status === 'not_available') {
            throw new \Exception('The selected doctor is currently not available for appointments.');
        }

        // Check if the doctor has a schedule for the selected day
        $schedule = $doctor->schedules()->where('day', Carbon::parse($validated['appointment_date'])->format('l'))->first();
        if (!$schedule) {
            throw new \Exception('The doctor does not have a schedule for the selected day.');
        }

        // Ensure the doctor is available on the selected day
        if ($schedule->status !== 'available') {
            throw new \Exception('The doctor is not available on the selected day.');
        }

        // Validate appointment times against doctor's schedule
        $appointmentStart = Carbon::parse($validated['start_time']);
        $appointmentEnd = Carbon::parse($validated['end_time']);
        $scheduleStart = Carbon::parse($schedule->start_time);
        $scheduleEnd = Carbon::parse($schedule->end_time);

        if (!($appointmentStart->format('H:i') >= $scheduleStart->format('H:i') &&
            $appointmentEnd->format('H:i') <= $scheduleEnd->format('H:i'))) {
            throw new \Exception('The selected time is not within the doctor\'s available schedule.');
        }

        // Update the appointment
        $appointment->update([
            'appointment_date' => $validated['appointment_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'appointment_reason' => $validated['appointment_reason'],
            'day' => $validated['day']
        ]);

        return $appointment;
    }

    // end

    
}
