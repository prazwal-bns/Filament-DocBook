<?php

namespace Database\Seeders;

use App\Models\Appointment;
use Illuminate\Support\Str;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AppointmentSlugSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $appointments = Appointment::all();

        foreach ($appointments as $appointment) {
            $patientName = $appointment->patient->user->name;
            $doctorName = $appointment->doctor->user->name;

            $uniqueNo = uniqid();

            $slug = Str::slug("{$uniqueNo}-{$patientName}");

            while (Appointment::where('slug', $slug)->exists()) {
                $slug = Str::slug("{$uniqueNo}-{$patientName}" . now()->timestamp);
            }

            $appointment->update([
                'slug' => $slug
            ]);
        }
    }
}
