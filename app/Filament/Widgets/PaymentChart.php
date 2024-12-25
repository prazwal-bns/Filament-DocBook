<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Models\Appointment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class PaymentChart extends ChartWidget
{
    protected static ?string $heading = 'Payment Status';

    protected static ?int $sort = 3;

    protected static ?string $maxHeight = '252px';

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user->role !== 'admin';
    }

    protected function getData(): array
    {
        $user = Auth::user();

        $paidAmount = 0;
        $unpaidAmount = 0;

        if ($user->role === 'doctor') {
            $appointments = Appointment::where('doctor_id', $user->doctor->id)->get();
            $payments = Payment::whereIn('appointment_id', $appointments->pluck('id'))->get();
        } elseif ($user->role === 'patient') {
            $appointments = Appointment::where('patient_id', $user->patient->id)->get();
            $payments = Payment::whereIn('appointment_id', $appointments->pluck('id'))->get();
        } else {
            return [
                'datasets' => [
                    [
                        'data' => [0, 0],
                        'backgroundColor' => ['#FF6384', '#36A2EB'],
                    ],
                ],
                'labels' => ['Paid', 'Unpaid'],
            ];
        }

        foreach ($payments as $payment) {
            if ($payment->payment_status === 'paid') {
                $paidAmount += $payment->amount;
            } else {
                $unpaidAmount += $payment->amount;
            }
        }

        return [
            'datasets' => [
                [
                    'data' => [$paidAmount, $unpaidAmount],
                    'backgroundColor' => ['#36A2EB', '#FF6384'],
                    'label' => 'Amount Status', // Label for the dataset
                ],
            ],
            'labels' => ['Completed Payments', 'Pending Payments'], // X-axis labels
            'options' => [
                'scales' => [
                    'x' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Payment Status',  // X-axis label
                        ],
                    ],
                    'y' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Amount',  // Y-axis label
                        ],
                        'ticks' => [
                            'beginAtZero' => true,  // Ensure the Y-axis starts at zero
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Change this to 'bar' for a bar chart
    }
}
