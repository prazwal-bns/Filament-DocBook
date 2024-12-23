<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    // protected function handleRecordCreation(array $data): Model
    // protected function handleRecordUpdate(Model $record, array $data): Model
    // {
    //     $record = parent::handleRecordUpdate($record, $data);

    //     // Pass the created record to the afterCreate method
    //     if ($record->role === 'patient') {
    //         $record->user->patient->update([
    //             'user_id' => $record->id,
    //             'gender' => $data['gender'],
    //         ]);
    //     } elseif ($record->role === 'doctor') {
    //         $record->user->doctor->update([
    //             'user_id' => $record->id,
    //             'specialization_id' => $data['specialization_id'],
    //             'status' => 'available',
    //         ]);
    //     }

    //     return $record;
    // }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record = parent::handleRecordUpdate($record, $data);

        // Handle patient role
        if ($record->role === 'patient') {
            $patient = $record->patient; // Attempt to get the related patient record
            if ($patient) {
                $patient->update([
                    'gender' => $data['gender'],
                ]);
            } else {
                // Create the patient record if it doesn't exist
                Patient::create([
                    'user_id' => $record->id,
                    'gender' => $data['gender'],
                ]);
            }
        }

        // Handle doctor role
        if ($record->role === 'doctor') {
            $doctor = $record->doctor; // Attempt to get the related doctor record
            if ($doctor) {
                $doctor->update([
                    'specialization_id' => $data['specialization_id'],
                    'status' => 'available',
                ]);
            } else {
                // Create the doctor record if it doesn't exist
                Doctor::create([
                    'user_id' => $record->id,
                    'specialization_id' => $data['specialization_id'],
                    'status' => 'available',
                ]);
            }
        }

        return $record;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = User::find($data['id']);

        if ($data['role'] == 'doctor'){
            $data['specialization_id'] = $user->doctor->specialization->id ?? null;
        }

        if ($data['role'] == 'patient'){
            $data['gender'] = $user->patient->gender ?? null;
        }
        return $data;
    }


}
