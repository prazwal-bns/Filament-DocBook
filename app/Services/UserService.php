<?php

namespace App\Services;

use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function registerUser($data)
    {
        // Hash the password
        $data['password'] = Hash::make($data['password']);

        // Create the user
        $user = User::create($data);

        // Handle specific logic for doctors and patients
        if ($data['role'] === 'doctor') {
            Doctor::create([
                'user_id' => $user->id,
                'specialization_id' => $data['specialization_id'],
                'status' => 'available',
                'bio' => $data['bio'] ?? null,
            ]);
        } elseif ($data['role'] === 'patient') {
            Patient::create([
                'user_id' => $user->id,
                'gender' => $data['gender'] ?? '',
                'dob' => $data['dob'] ?? null,
            ]);
        }

        return $user;
    }
}
