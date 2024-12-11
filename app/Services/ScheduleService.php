<?php

namespace App\Services;
use Carbon\Carbon;
use App\Models\Schedule;
use App\Models\Appointment;
use Illuminate\Support\Facades\Validator;

class ScheduleService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function createSchedule($request, $doctor)
    {
        $validated = $request->validate([
            'schedule.*.start_time' => 'required|date_format:H:i',
            'schedule.*.end_time' => 'required|date_format:H:i|after:schedule.*.start_time',
        ]);

        foreach ($request->schedule as $day => $times) {
            $start_time = Carbon::createFromFormat('H:i', $times['start_time'])->format('H:i');
            $end_time = Carbon::createFromFormat('H:i', $times['end_time'])->format('H:i');

            $existingSchedule = Schedule::where('doctor_id', $doctor->id)
                ->where('day', $day)
                ->first();

            if ($existingSchedule) {
                return [
                    'status' => 'error',
                    'message' => "Schedule already exists for {$day}.",
                ];
            }

            // If no existing schedule is found, create a new schedule
            Schedule::create([
                'doctor_id' => $doctor->id,
                'day' => $day,
                'start_time' => $start_time,
                'end_time' => $end_time,
            ]);
        }

        return [
            'status' => 'success',
            'message' => 'Schedule created successfully!',
        ];
    }


    public function updateSchedule($doctor, $day, $data)
    {
        $normalizedDay = ucfirst(strtolower($day));

        $schedule = Schedule::where('doctor_id', $doctor->id)
            ->where('day', $normalizedDay)
            ->first();

        if (!$schedule) {
            return [
                'status' => 'error',
                'message' => 'Schedule not found for the specified day.',
            ];
        }

        $hasAppointments = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', Carbon::now()->next($normalizedDay)->toDateString())
            ->exists();

        if ($hasAppointments) {
            return [
                'status' => 'error',
                'message' => 'Cannot update the schedule as there are appointments for this day.',
            ];
        }

        $validator = Validator::make($data, [
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'required|in:available,unavailable',
        ]);

        if ($validator->fails()) {
            return [
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ];
        }

        $schedule->update([
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'status' => $data['status'],
        ]);

        return [
            'status' => 'success',
            'message' => 'Schedule updated successfully.',
            'data' => $schedule,
        ];
    }


}
