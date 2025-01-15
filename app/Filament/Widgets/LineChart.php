<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\ChartWidget;

class LineChart extends ChartWidget
{
    protected static ?string $heading = 'Users';


    protected static ?int $sort = 2;

        protected static string $color = 'warning';

    // protected int | string | array $columnSpan = 'full';

    public function getHeading(): string
    {
        $user = Auth::user();


        if ($user->role === 'admin') {
            return 'Users Registered This Week';
        }

        if ($user->role === 'doctor') {
            return "Appointments This Week ({$user->name})";
        }

        if ($user->role === 'patient') {
            return "Appointments This Week ({$user->name})";
        }

        return 'No Data Available';
    }


    protected function getData(): array
    {
        $user = Auth::user();
        $labels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        if ($user->role === 'admin') {
            $weeklyUserStats = User::selectRaw('strftime("%w", created_at) as day, COUNT(*) as count')
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->groupBy('day')
                ->get()
                ->pluck('count', 'day');

            $data = array_map(
                fn($day) => $weeklyUserStats[$day] ?? 0,
                ['0', '1', '2', '3', '4', '5', '6'] // Days as numeric values
            );

            // Map numeric days to labels
            $labels = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

            return [
                'datasets' => [
                    [
                        'label' => 'Users Registered',
                        'data' => $data,
                        // 'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                        // 'borderColor' => 'rgba(75, 192, 192, 1)',
                        'borderWidth' => 2,
                    ],
                ],
                'labels' => $labels,
            ];

        }

        if ($user->role === 'doctor') {
            // Doctor: Show their weekly appointment count
            $weeklyAppointments = Appointment::selectRaw('strftime("%w", appointment_date) as day, COUNT(*) as count')
                ->where('doctor_id', $user->doctor->id)
                ->whereBetween('appointment_date', [now()->startOfWeek(), now()->endOfWeek()])
                ->groupBy('day')
                ->get()
                ->pluck('count', 'day');

            $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            $data = array_map(
                fn($day) => $weeklyAppointments[$day] ?? 0,
                range(0, 6)
            );

            return [
                'datasets' => [
                    [
                        'label' => 'Appointments',
                        'data' => $data,
                        'backgroundColor' => 'rgba(255, 159, 64, 0.2)',
                        'borderColor' => 'rgba(255, 159, 64, 1)',
                        'borderWidth' => 2,
                    ],
                ],
                'labels' => $dayNames,
            ];

        }

        if ($user->role === 'patient') {
            $weeklyAppointments = Appointment::selectRaw('strftime("%w", appointment_date) as day, COUNT(*) as count')
                ->where('patient_id', $user->patient->id)
                ->whereBetween('appointment_date', [now()->startOfWeek(), now()->endOfWeek()])
                ->groupBy('day')
                ->get()
                ->pluck('count', 'day');

            // Map SQLite weekday numbers (0 = Sunday, 1 = Monday, ...) to actual names
            $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            $data = array_map(
                fn($day) => $weeklyAppointments[$day] ?? 0,
                range(0, 6)
            );

            return [
                'datasets' => [
                    [
                        'label' => 'Appointments',
                        'data' => $data,
                        'backgroundColor' => 'rgba(255, 159, 64, 0.2)',
                        'borderColor' => 'rgba(255, 159, 64, 1)',
                        'borderWidth' => 2,
                    ],
                ],
                'labels' => $dayNames,
            ];
        }

        // Default fallback for unknown roles
        return [
            'datasets' => [
                [
                    'label' => 'No Data',
                    'data' => array_fill(0, 7, 0),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
