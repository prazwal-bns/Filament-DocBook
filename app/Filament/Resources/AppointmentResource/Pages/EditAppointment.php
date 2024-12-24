<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditAppointment extends EditRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }



    protected function getSavedNotification(): Notification
    {
        $record = $this->record;

        return Notification::make()
            ->success()
            ->icon('heroicon-o-pencil-square')
            ->title('Appointment Updated!')
            ->body("Your appointment with Dr. {$record->doctor->user->name} for {$record->appointment_date} has been successfully updated.");
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }


    // protected function mutateFormDataBeforeFill(array $data): array
    // {
    //     return $data;
    //     $user = Auth::user();
    //     $appointmentId = null;

    //     if ($user->role == 'doctor') {
    //         $appointmentId = $user->doctor->appointment->id ?? null;
    //     }

    //     if ($user->role == 'patient') {
    //         $appointmentId = $user->patient->appointment->id ?? null;
    //     }


    //     $data['appointment_id'] = $appointmentId;

    //     dd($data);

    //     return $data;
    // }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record) {
            $data['id'] = $this->record->id ?? null;
        }

        // dd($data['appointment_id']);
        return $data;
    }

}
