<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ViewProfile extends Page
{
    protected static ?string $navigationLabel = 'View Profile';
    protected static ?string $navigationIcon = 'heroicon-o-eye';
    protected static string $view = 'filament.pages.view-profile';
    protected static ?string $navigationGroup = 'Profile';

    protected static ?int $navigationSort = 10;

    public ?array $userData = [];

    public function mount(): void
    {
        $user = Auth::user();
        $this->userData = $user->only(['name', 'email', 'address', 'phone']);

        if ($user->role === 'patient') {
            $patient = $user->patient;
            $this->userData = array_merge($this->userData, $patient ? $patient->only(['gender', 'dob']) : []);
        }
        elseif ($user->role === 'doctor') {
            $doctor = $user->doctor;

            if ($doctor) {
                // Populate basic user data
                $this->userData['status'] = $doctor->status;
                $this->userData['bio'] = $doctor->bio;

                // Populate specialization name
                $this->userData['specialization_name'] = $doctor->specialization->name;
            }
        }


    }
}
