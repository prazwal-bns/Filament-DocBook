<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Specialization;
use App\Models\Schedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
{
    // Admin User
    $admin = User::factory()->create([
        'name' => 'Admin User',
        'role' => 'admin',
        'email' => 'admin@gmail.com',
        'password' => bcrypt('@admin123'),
    ]);

    // Specializations
    $specializations = [
        'Cardiology', 'Neurology', 'Pediatrics', 'Orthopedics', 'Dermatology'
    ];
    foreach ($specializations as $specialization) {
        Specialization::create(['name' => $specialization]);
    }

    // Doctor Users
    $doctor1 = User::factory()->create([
        'name' => 'Dr. John Doe',
        'role' => 'doctor',
        'email' => 'doctor1@gmail.com',
        'password' => bcrypt('@doctor123'),
    ]);
    
    $doctor1 = Doctor::create([
        'user_id' => $doctor1->id,
        'specialization_id' => 1, // Cardiology
        'status' => 'available',
        'bio' => 'Expert in Cardiology.'
    ]);

    $doctor2 = User::factory()->create([
        'name' => 'Dr. Jane Smith',
        'role' => 'doctor',
        'email' => 'doctor2@gmail.com',
        'password' => bcrypt('@doctor123'),
    ]);
    
    $doctor2 = Doctor::create([
        'user_id' => $doctor2->id,
        'specialization_id' => 2, // Neurology
        'status' => 'available',
        'bio' => 'Expert in Neurology.'
    ]);

    // Patient Users
    $patient1 = User::factory()->create([
        'name' => 'Patient One',
        'role' => 'patient',
        'email' => 'patient1@gmail.com',
        'password' => bcrypt('@patient123'),
    ]);

    $patient2 = User::factory()->create([
        'name' => 'Patient Two',
        'role' => 'patient',
        'email' => 'patient2@gmail.com',
        'password' => bcrypt('@patient123'),
    ]);

    // Creating patient records without medical history column
    Patient::create([
        'user_id' => $patient1->id,
        'gender' => 'Male',  // Example data, replace with actual details
        'dob' => '2000-01-01', // Example date
    ]);

    Patient::create([
        'user_id' => $patient2->id,
        'gender' => 'Female',  // Example data, replace with actual details
        'dob' => '1995-05-05', // Example date
    ]);

    // Days of the week
    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    // Create schedule for Doctor 1 (Dr. John Doe)
    foreach ($days as $day) {
        Schedule::create([
            'doctor_id' => $doctor1->id,
            'day' => $day,
            'start_time' => '06:00',
            'end_time' => '18:00',
            'status' => 'available'
        ]);
    }

    // Create schedule for Doctor 2 (Dr. Jane Smith)
    foreach ($days as $day) {
        Schedule::create([
            'doctor_id' => $doctor2->id,
            'day' => $day,
            'start_time' => '06:00',
            'end_time' => '18:00',
            'status' => 'available'
        ]);
    }
    
    // You can add more users (doctors or patients) if necessary
}
}
