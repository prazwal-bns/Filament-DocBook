<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Schedule;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation($data);

        // Pass the created record to the afterCreate method
        if ($record->role === 'patient') {
            Patient::create([
                'user_id' => $record->id,
                'gender' => $data['gender'] ?? null,
            ]);
        } elseif ($record->role === 'doctor') {
            $doctor = Doctor::create([
                'user_id' => $record->id,
                'specialization_id' => $data['specialization_id'],
                'status' => 'available',
            ]);

            $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            foreach ($days as $day) {
                Schedule::create([
                    'doctor_id' => $doctor->id,
                    'day' => $day,
                    'status' => 'available',
                    'start_time' => '09:00',
                    'end_time' => '18:00',
                ]);
            }
        }

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
