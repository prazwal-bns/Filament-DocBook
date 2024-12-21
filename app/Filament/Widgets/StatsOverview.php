<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StatsOverview extends BaseWidget
{

    protected static ?int $sort = 1;
    protected function getStats(): array
    {
        $user = Auth::user();

        if($user->role === 'admin'){
            return [
                Stat::make('Total Users', User::count())
                    ->description('Total number of users in the system')
                    ->descriptionIcon('heroicon-m-users')
                    ->chart([7, 2, 10, 3, 15, 4, 17])
                    ->color('lime'),

                Stat::make('Total Patients', Patient::count())
                    ->description('Total number of patients')
                    ->descriptionIcon('heroicon-m-user-group')
                    ->chart([7, 2, 10, 3, 15, 4, 17])
                    ->color('warning'),

                Stat::make('Total Doctors', Doctor::count())
                    ->description('Total number of doctors')
                    ->descriptionIcon('heroicon-m-user')
                    ->chart([7, 2, 10, 3, 15, 4, 17])
                    ->color('teal'),
            ];
        }

        if ($user->role === 'doctor') {
            $doctorId = $user->doctor->id;

            return [

                Stat::make('Pending Appointments', Appointment::where('status', 'pending')->where('doctor_id', $doctorId)->count())
                    ->description('Your pending appointments')
                    ->descriptionIcon('heroicon-m-arrow-path')
                    ->chart($this->generateTrendData('pending', 'doctor_id', $doctorId))
                    ->color('rose'),

                Stat::make('Confirmed Appointments', Appointment::where('status', 'confirmed')->where('doctor_id', $doctorId)->count())
                    ->description('Your confirmed appointments')
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->chart($this->generateTrendData('confirmed', 'doctor_id', $doctorId))
                    ->color('info'),

                Stat::make('Completed Appointments', Appointment::where('status', 'completed')->where('doctor_id', $doctorId)->count())
                    ->description('Your completed appointments')
                    ->descriptionIcon('heroicon-m-clipboard-document-check')
                    ->chart($this->generateTrendData('completed', 'doctor_id', $doctorId))
                    ->color('success'),
            ];
        }

        if ($user->role === 'patient') {
            $patientId = $user->patient->id;

            return [
                Stat::make('Pending Appointments', Appointment::where('status', 'pending')->where('patient_id', $patientId)->count())
                    ->description('Your pending appointments')
                    ->descriptionIcon('heroicon-m-arrow-path')
                    ->chart($this->generateTrendData('pending', 'patient_id', $patientId))
                    ->color('rose'),

                Stat::make('Confirmed Appointments', Appointment::where('status', 'confirmed')->where('patient_id', $patientId)->count())
                    ->description('Your confirmed appointments')
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->chart($this->generateTrendData('confirmed', 'patient_id', $patientId))
                    ->color('info'),

                Stat::make('Completed Appointments', Appointment::where('status', 'completed')->where('patient_id', $patientId)->count())
                    ->description('Your completed appointments')
                    ->descriptionIcon('heroicon-m-clipboard-document-check')
                    ->chart($this->generateTrendData('completed', 'patient_id', $patientId))
                    ->color('success'),
            ];
        }

        return [];
    }

    private function generateTrendData(string $status, string $field, int $id): array
    {
        $trendData = [];

        for ($i = 6; $i >= 0; $i--) {
            $dayStart = now()->subDays($i)->startOfDay();
            $dayEnd = now()->subDays($i)->endOfDay();

            $count = Appointment::where('status', $status)
                ->where($field, $id)
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->count();

            $trendData[] = $count;
        }

        return $trendData;
    }

}
