<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Filament\Notifications\Events\DatabaseNotificationsSent;
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

    // public function handleRecordCreation(array $data): Model
    // {
    //     $record = parent::handleRecordCreation($data);

    //     $startTime = strtotime($data['start_time']);
    //     $endTime = strtotime($data['end_time']);
    //     $durationInMinutes = ($endTime - $startTime) / 60;

    //     // Determine the price (Rs. 1000 for 30 minutes, proportional for other durations)
    //     $basePrice = 1000;
    //     $price = ($durationInMinutes / 30) * $basePrice;

    //     // Round the price to the nearest whole number
    //     $roundedPrice = round($price);

    //     // Create the payment record
    //     Payment::create([
    //         'appointment_id' => $record->id,
    //         'payment_status' => 'unpaid',
    //         'amount' => $roundedPrice,
    //     ]);

    //     // Notification::make()
    //     //     ->title('Appointment Booked!')
    //     //     ->body("Your appointment with Dr. {$record->doctor->user->name} for {$record->appointment_date} has been successfully booked.")
    //     //     ->success()
    //     //     ->sendToDatabase($record->patient); // Send notification to the patient

    //     // event(new DatabaseNotificationsSent($record)); // Ensure the notification is sent in real-time

    //     $this->sendNotifications($record);


    //     return $record;
    // }

    // protected function sendNotifications(Appointment $record)
    // {
    //     $this->sendAdminNotification($record);
    //     $this->sendPatientNotification($record);
    //     $this->sendDoctorNotification($record);
    // }

    // protected function sendAdminNotification(Appointment $record)
    // {
    //     $admin = User::where('role', 'admin')->first();

    //     Notification::make()
    //         ->title('New Appointment Created')
    //         ->body("An appointment has been created for Dr. {$record->doctor->user->name} on {$record->appointment_date}.")
    //         ->success()
    //         ->sendToDatabase($admin);

    // }

    // protected function sendPatientNotification(Appointment $record)
    // {
    //     Notification::make()
    //         ->title('Appointment Booked!')
    //         ->body("Your appointment with Dr. {$record->doctor->user->name} for {$record->appointment_date} has been successfully booked.")
    //         ->success()
    //         ->sendToDatabase($record->patient);

    //     event(new DatabaseNotificationsSent($record->patient->user));
    // }

    // protected function sendDoctorNotification(Appointment $record)
    // {
    //     Notification::make()
    //         ->title('New Appointment Scheduled')
    //         ->body("You have a new appointment with {$record->patient->user->name} scheduled for {$record->appointment_date}.")
    //         ->success()
    //         ->sendToDatabase($record->doctor->user);

    //     event(new DatabaseNotificationsSent($record->doctor->user));
    // }


    public function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation($data);

        $startTime = strtotime($data['start_time']);
        $endTime = strtotime($data['end_time']);
        $durationInMinutes = ($endTime - $startTime) / 60;

        $basePrice = 1000;
        $price = ($durationInMinutes / 30) * $basePrice;
        $roundedPrice = round($price);

        Payment::create([
            'appointment_id' => $record->id,
            'payment_status' => 'unpaid',
            'amount' => $roundedPrice,
        ]);

        $this->sendNotifications($record);

        return $record;
    }

    protected function sendNotifications(Appointment $record)
    {
        $this->sendAdminNotification($record);
        $this->sendPatientNotification($record);
        $this->sendDoctorNotification($record);
    }

    protected function sendAdminNotification(Appointment $record)
    {
        $adminUsers = User::where('role', 'admin')->get();

        foreach ($adminUsers as $admin) {
            Notification::make()
                ->title('New Appointment Booked !!')
                ->icon('heroicon-o-calendar')
                ->body("
                    An appointment with Dr. {$record->doctor->user->name} for {$record->appointment_date} has been successfully booked. <br>
                    <a href='" . route('filament.admin.resources.appointments.view', $record) . "' target='_blank'
                    style='color: #3b82f6; text-decoration: underline; transition: color 0.2s ease, text-decoration 0.2s ease;'>
                    Click here to view the appointment Details.
                    </a>
                ")
                ->sendToDatabase($admin);
        }
    }

    protected function sendPatientNotification(Appointment $record)
    {
        Notification::make()
            ->title('Appointment Booked!')
            ->icon('heroicon-o-calendar')
            ->body("
                    Your appointment with Dr. {$record->doctor->user->name} for {$record->appointment_date} has been successfully booked. <br>
                    <a href='" . route('filament.admin.resources.appointments.view', $record) . "' target='_blank'
                    style='color: #3b82f6; text-decoration: underline; transition: color 0.2s ease, text-decoration 0.2s ease;'>
                    Click here to view the appointment Details.
                    </a>
            ")
            ->success()
            ->sendToDatabase($record->patient->user);

        event(new DatabaseNotificationsSent($record->patient->user));
    }

    protected function sendDoctorNotification(Appointment $record)
    {
        Notification::make()
            ->title('New Appointment Scheduled')
            ->icon('heroicon-o-calendar')
            ->body("
                You have a new appointment with {$record->patient->user->name} scheduled for {$record->appointment_date}. <br>
                <a href='" . route('filament.admin.resources.appointments.view', $record) . "' target='_blank'
                style='color: #3b82f6; text-decoration: underline; transition: color 0.2s ease, text-decoration 0.2s ease;'>
                Click here to view the appointment Details.
                </a>
            ")
            ->success()
            ->sendToDatabase($record->doctor->user);

        event(new DatabaseNotificationsSent($record->doctor->user));
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
