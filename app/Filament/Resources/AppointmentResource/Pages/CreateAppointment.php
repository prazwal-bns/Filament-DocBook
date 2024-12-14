<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Payment;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getCreatedNotification(): Notification
    {
        $record = $this->record;

        return Notification::make()
            ->success()
            ->icon('heroicon-o-calendar')
            ->title('Appointment Booked!')
            ->body("Your appointment with Dr. {$record->doctor->user->name} for {$record->appointment_date} has been successfully booked.");
    }

    public function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation($data);

        $price = 1000;

        // Create the payment record
        Payment::create([
            'appointment_id' => $record->id,
            'payment_status' => 'unpaid',
            'amount' => $price,
        ]);

        return $record;
    }
}
