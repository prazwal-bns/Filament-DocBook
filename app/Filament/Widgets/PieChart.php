<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\User;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\ChartWidget;

class PieChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            // Admin: Pie chart of total users (patients, doctors, admins)
            $patientsCount = User::where('role', 'patient')->count();
            $doctorsCount = User::where('role', 'doctor')->count();
            $adminsCount = User::where('role', 'admin')->count();

            return [
                'datasets' => [
                    [
                        'data' => [$patientsCount, $doctorsCount, $adminsCount],
                        'backgroundColor' => ['#FF6384', '#36A2EB', '#FFCD56'],
                    ],
                ],
                'labels' => ['Patients', 'Doctors', 'Admins'],
            ];
        }

        if ($user->role === 'doctor' || $user->role === 'patient') {
            $appointments = Appointment::query();

            if ($user->role === 'doctor') {
                $appointments->where('doctor_id', $user->doctor->id);
            }

            if ($user->role === 'patient') {
                $appointments->where('patient_id', $user->patient->id);
            }

            $statuses = $appointments->selectRaw('status, count(*) as count')
                                     ->groupBy('status')
                                     ->pluck('count', 'status');

            $statusLabels = ['pending', 'confirmed', 'completed'];

            $resultStatuses = [];
            foreach ($statusLabels as $status) {
                $resultStatuses[$status] = isset($statuses[$status]) ? $statuses[$status] : 0;
            }

            return [
                'datasets' => [
                    [
                        'data' => array_values($resultStatuses),
                        'backgroundColor' => ['#f43f5e', '#3b82f6', '#10b981'],
                    ],
                ],
                'labels' => array_keys($resultStatuses),
            ];
        }

        return [
            'datasets' => [
                [
                    'data' => [0],
                    'backgroundColor' => ['#FFFFFF'],
                ],
            ],
            'labels' => ['No Data Available'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    public function getHeading(): string
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return 'User Distribution';
        }

        if ($user->role === 'doctor') {
            return 'Appointment Status Distribution (Doctor)';
        }

        if ($user->role === 'patient') {
            return 'Appointment Status Distribution (Patient)';
        }

        return 'Chart';
    }
}
