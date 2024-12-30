<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;

class PDFController extends Controller
{
    // public function downloadPdf($appointmentId)
    // {
    //     $appointment = Appointment::findOrFail($appointmentId);

    //     $patientName = $appointment->patient->user->name ?? 'Unknown Patient';

    //     $randomUniqueNumber = uniqid();

    //     $filename = "appointment_details_{$patientName}_" . $randomUniqueNumber . ".pdf";

    //     $data = [
    //         'appointment' => $appointment,
    //     ];

    //     $pdf = Pdf::loadView('appointments.pdf', $data);

    //     $pdf->download($filename);

    //     return redirect()->back();

    // }

    public function downloadPdf($appointmentId)
    {
        $appointment = Appointment::findOrFail($appointmentId);

        $patientName = $appointment->patient->user->name ?? 'Unknown_Patient';
        $randomUniqueNumber = uniqid();
        $sanitizedPatientName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $patientName);

        $filename = "appointment_details_{$sanitizedPatientName}_{$randomUniqueNumber}.pdf";

        $data = [
            'appointment' => $appointment,
        ];

        $pdf = Pdf::loadView('appointments.pdf', $data);

        // Return PDF for download
        return Response::make($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

}
