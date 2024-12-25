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
use Illuminate\Support\Facades\Auth;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

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

        $startTime = strtotime($data['start_time']);
        $endTime = strtotime($data['end_time']);
        $durationInMinutes = ($endTime - $startTime) / 60;

        // Determine the price (Rs. 1000 for 30 minutes, proportional for other durations)
        $basePrice = 1000;
        $price = ($durationInMinutes / 30) * $basePrice;

        // Round the price to the nearest whole number
        $roundedPrice = round($price);

        // Create the payment record
        Payment::create([
            'appointment_id' => $record->id,
            'payment_status' => 'unpaid',
            'amount' => $roundedPrice,
        ]);

        return $record;
    }

    public function mutateFormDataBeforeCreate(array $data): array
    {
        if (Auth::user()->role === 'patient') {
            $data['patient_id'] = Auth::user()->patient->id;
        }

        if (Auth::user()->role === 'doctor') {
            $data['doctor_id'] = Auth::user()->doctor->id;
        }

        return $data;
    }

}
