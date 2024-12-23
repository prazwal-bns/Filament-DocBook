<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PDFController extends Controller
{
    public function downloadPdf($appointmentId)
    {
        $appointment = Appointment::findOrFail($appointmentId);

        $patientName = $appointment->patient->user->name ?? 'Unknown Patient';

        $randomUniqueNumber = uniqid();

        $filename = "appointment_details_{$patientName}_" . $randomUniqueNumber . ".pdf";

        $data = [
            'appointment' => $appointment,
        ];

        $pdf = Pdf::loadView('appointments.pdf', $data);

        return $pdf->download($filename);
    }
}
