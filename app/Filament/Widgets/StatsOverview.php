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

        $roleSpecificStats = $this->getRoleSpecificStats($user);

        return $roleSpecificStats;

        // if ($user->role === 'doctor') {
        //     $doctorId = $user->doctor->id;

        //     return [

        //         Stat::make('Pending Appointments', Appointment::where('status', 'pending')->where('doctor_id', $doctorId)->count())
        //             ->description('Your pending appointments')
        //             ->descriptionIcon('heroicon-m-arrow-path')
        //             ->chart($this->generateTrendData('pending', 'doctor_id', $doctorId))
        //             ->url(route('filament.admin.resources.appointments.index'). '?activeTab=Pending')
        //             ->color('rose'),

        //         Stat::make('Confirmed Appointments', Appointment::where('status', 'confirmed')->where('doctor_id', $doctorId)->count())
        //             ->description('Your confirmed appointments')
        //             ->descriptionIcon('heroicon-m-check-circle')
        //             ->chart($this->generateTrendData('confirmed', 'doctor_id', $doctorId))
        //             ->color('info'),

        //         Stat::make('Completed Appointments', Appointment::where('status', 'completed')->where('doctor_id', $doctorId)->count())
        //             ->description('Your completed appointments')
        //             ->descriptionIcon('heroicon-m-clipboard-document-check')
        //             ->chart($this->generateTrendData('completed', 'doctor_id', $doctorId))
        //             ->color('success'),
        //     ];
        // }

        // if ($user->role === 'patient') {
        //     $patientId = $user->patient->id;

        //     return [
        //         Stat::make('Pending Appointments', Appointment::where('status', 'pending')->where('patient_id', $patientId)->count())
        //             ->description('Your pending appointments')
        //             ->descriptionIcon('heroicon-m-arrow-path')
        //             ->chart($this->generateTrendData('pending', 'patient_id', $patientId))
        //             ->color('rose'),

        //         Stat::make('Confirmed Appointments', Appointment::where('status', 'confirmed')->where('patient_id', $patientId)->count())
        //             ->description('Your confirmed appointments')
        //             ->descriptionIcon('heroicon-m-check-circle')
        //             ->chart($this->generateTrendData('confirmed', 'patient_id', $patientId))
        //             ->color('info'),

        //         Stat::make('Completed Appointments', Appointment::where('status', 'completed')->where('patient_id', $patientId)->count())
        //             ->description('Your completed appointments')
        //             ->descriptionIcon('heroicon-m-clipboard-document-check')
        //             ->chart($this->generateTrendData('completed', 'patient_id', $patientId))
        //             ->color('success'),
        //     ];
        // }

        return [];
    }

    private function getRoleSpecificStats($user): array
    {
        if ($user->role === 'doctor') {
            return $this->getAppointmentStats('doctor_id', $user->doctor->id, 'doctor');
        }

        if ($user->role === 'patient') {
            return $this->getAppointmentStats('patient_id', $user->patient->id, 'patient');
        }

        return [];
    }

    private function getAppointmentStats($column, $id, $role): array
    {
        return [
            $this->createStat('Pending Appointments', 'pending', $column, $id, 'Your pending appointments', 'heroicon-m-arrow-path', 'rose', $role),
            $this->createStat('Confirmed Appointments', 'confirmed', $column, $id, 'Your confirmed appointments', 'heroicon-m-check-circle', 'info', $role),
            $this->createStat('Completed Appointments', 'completed', $column, $id, 'Your completed appointments', 'heroicon-m-clipboard-document-check', 'success', $role),
        ];
    }

    private function createStat($label, $status, $column, $idValue, $description, $icon, $color, $role): Stat
    {
        $url = $this->generateUrl($role, $status);

        return Stat::make($label, Appointment::where('status', $status)->where($column, $idValue)->count())
            ->description($description)
            ->descriptionIcon($icon)
            ->chart($this->generateTrendData($status, $column, $idValue))
            ->url($url)
            ->color($color);
    }

    private function generateUrl($role, $status): string
    {
        if ($role === 'doctor') {
            return route('filament.admin.resources.appointments.index') . '?activeTab=' . ucfirst($status);
        } elseif ($role === 'patient') {
            return route('filament.admin.resources.appointments.index') . '?activeTab=' . ucfirst($status);
        }

        return '#';
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
