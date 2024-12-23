<?php

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Resources\ReviewResource;
use App\Models\Appointment;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewReview extends ViewRecord
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (!empty($data['appointment_id'])) {
            $appointment = Appointment::with(['patient', 'doctor'])->find($data['appointment_id']);
            if ($appointment) {
                $data['appointment_id'] = "{$appointment->patient->user->name} with {$appointment->doctor->user->name} on {$appointment->appointment_date}";
            }
        }

        return $data;
    }
}
