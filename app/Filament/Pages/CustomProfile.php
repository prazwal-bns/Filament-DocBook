<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Actions;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Specialization;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class CustomProfile extends Page
{
    use InteractsWithForms;

    protected static string $view = 'filament.pages.custom-profile';
    protected static ?string $navigationGroup = 'Profile';

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'Edit My Profile';

    // protected static ?string

    protected static ?int $navigationSort = 9;


    public ?array $data = [];
    public ?array $extraData = [];
    public array $specializations = [];

    public function mount(): void
    {
        $user = Auth::user();
        $this->data = collect($user)->only(['name', 'email', 'address', 'phone'])->toArray();

        if ($user->role === 'patient') {
            $patient = Patient::where('user_id', $user->id)->first();
            $this->extraData = $patient ? collect($patient)->only(['gender', 'dob'])->toArray() : [];
        } elseif ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            $this->extraData = $doctor ? collect($doctor)->only(['specialization_id', 'status', 'bio'])->toArray() : [];
            $this->specializations = Specialization::pluck('name', 'id')->toArray();
        }
    }




    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('Update')
                ->color('primary')
                ->submit('update'),
        ];
    }


    public function update(): void
    {
        $user = User::find(Auth::id());
        $user->update($this->data);

        if ($user->role == 'patient') {
            $patient = $user->patient ?? new Patient(['user_id' => $user->id]);
            $patient->update([
                'gender' => $this->extraData['gender'],
                'dob' => $this->extraData['dob'],
            ]);
        } elseif ($user->role == 'doctor') {
            $doctor = $user->doctor ?? new Doctor(['user_id' => $user->id]);
            $doctor->update([
                'specialization_id' => $this->extraData['specialization_id'],
                'bio' => $this->extraData['bio'],
            ]);
        }

        Notification::make()
            ->title('Profile updated successfully !!')
            ->icon('heroicon-o-user-circle')
            ->success()
            ->send();
    }

    public function updateDoctorStatus(): void
    {
        $user = User::find(Auth::id());

        $hasPendingAppointments = $user->doctor->appointments()
            ->where('status', '!=', 'completed')
            ->where('date', '>', now())
            ->exists();

        if ($hasPendingAppointments) {
            Notification::make()
                ->title('Cannot update status')
                ->body('You have pending appointments scheduled for the future.')
                ->icon('heroicon-o-exclamation-circle')
                ->danger()
                ->send();

            return;
        }
        $user->doctor()->update([
            'status' => $this->extraData['status'],
        ]);

        Notification::make()
        ->title('Doctor Status updated successfully!!')
        ->icon('heroicon-o-arrow-path')
        ->success()
        ->send();
    }
}
