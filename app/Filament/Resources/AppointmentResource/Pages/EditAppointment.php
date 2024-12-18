<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

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

    
}
