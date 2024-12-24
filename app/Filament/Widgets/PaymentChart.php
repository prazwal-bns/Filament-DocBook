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
                ],
            ],
            'labels' => ['Completed Payments', 'Pending Payments'],
        ];
    }

    protected function getType(): string
    {
        return 'pie'; // Change this to 'pie' for a pie chart
    }
}
