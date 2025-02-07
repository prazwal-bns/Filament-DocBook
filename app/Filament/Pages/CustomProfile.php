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

    protected ?string $heading;

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

        $this->heading = 'Edit ' . $user->name . ' Profile';

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


    // public function update()
    // {
    //     $user = User::find(Auth::id());
    //     $user->update($this->data);

    //     if ($user->role == 'patient') {
    //         $patient = $user->patient ?? new Patient(['user_id' => $user->id]);
    //         $patient->update([
    //             'gender' => $this->extraData['gender'],
    //             'dob' => $this->extraData['dob'],
    //         ]);
    //     } elseif ($user->role == 'doctor') {
    //         $doctor = $user->doctor ?? new Doctor(['user_id' => $user->id]);
    //         $doctor->update([
    //             // 'specialization_id' => $this->extraData['specialization_id'],
    //             'bio' => $this->extraData['bio'],
    //         ]);
    //     }

    //     Notification::make()
    //         ->title('Profile updated successfully !!')
    //         ->icon('heroicon-o-user-circle')
    //         ->success()
    //         ->send();

    //     return redirect()->route('filament.admin.pages.view-profile');
    // }

    public function update()
    {
        $user = User::find(Auth::id());

        // Make email case-insensitive when updating
        if ($this->data['email']) {
            // Ensure the email is unique, ignoring case sensitivity
            $existingUser = User::whereRaw('LOWER(email) = ?', [strtolower($this->data['email'])])
                                ->where('id', '!=', $user->id) // Ensure the current user's email is not checked
                                ->exists();

            if ($existingUser) {
                // If the email already exists (ignoring case), throw an error
                Notification::make()
                    ->title('This email has already been taken.')
                    ->icon('heroicon-o-user-circle')
                    ->danger()
                    ->send();

                return redirect()->back()->withInput();
            }
        }

        // Update the user's general data
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
                'bio' => $this->extraData['bio'],
            ]);
        }

        Notification::make()
            ->title('Profile updated successfully !!')
            ->icon('heroicon-o-user-circle')
            ->success()
            ->send();

        return redirect()->route('filament.admin.pages.view-profile');
    }



    public function updateDoctorStatus(): void
    {
        $user = User::find(Auth::id());

        $hasPendingAppointments = $user->doctor->appointments()
            ->where('status', '!=', 'completed')
            ->where('appointment_date', '>', now())
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
