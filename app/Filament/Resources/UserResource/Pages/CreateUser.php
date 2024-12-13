<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Doctor;
use App\Models\Patient;
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
                'gender' => $data['gender'],
            ]);
        } elseif ($record->role === 'doctor') {
            Doctor::create([
                'user_id' => $record->id,
                'specialization_id' => $data['specialization_id'],
                'status' => 'available',
            ]);
        }

        return $record;
    }
}
