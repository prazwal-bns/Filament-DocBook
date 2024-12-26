<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Payment;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Stripe\Exception\CardException;
use Stripe\Stripe;
use Xentixar\EsewaSdk\Esewa;

class PaymentController extends Controller
{
    public function esewaPay($appointmentId)
    {
        $payment = Payment::where('appointment_id', $appointmentId)->firstOrFail();

        $transaction_id = 'TXN-' . uniqid();
        $payment->update(['transaction_id' => $transaction_id]);

        $esewa = new Esewa();
        $esewa->config(
            route('payment.success'), // Success URL
            route('payment.failure'), // Failure URL
            $payment->amount,
            $transaction_id
        );

        return $esewa->init();
    }

    public function esewaPaySuccess()
    {
        $esewa = new Esewa();
        $response = $esewa->decode();

        if ($response) {
            if (isset($response['transaction_uuid'])) {
                $transactionUuid = $response['transaction_uuid'];
                $payment = Payment::where('transaction_id', $transactionUuid)->first();

                if ($payment) {
                    $payment->update([
                        'payment_status' => 'paid',
                        'payment_method' => 'esewa',
                    ]);

                    $payment->appointment->update(['status' => 'confirmed']);

                    // Filament notification for success
                    Notification::make()
                        ->title('Payment Successful')
                        ->body('The payment has been successfully completed.')
                        ->success()
                        ->send();

                    return redirect()->route('filament.admin.resources.appointments.index');
                }

                Notification::make()
                    ->title('Payment Record Not Found')
                    ->body('The transaction record could not be located.')
                    ->danger()
                    ->send();

                return redirect()->route('filament.admin.resources.payments.index');
            }

            Notification::make()
                ->title('Invalid Response')
                ->body('Received an invalid response from eSewa.')
                ->danger()
                ->send();

            return redirect()->route('filament.admin.resources.payments.index');
        }
    }

    public function esewaPayFailure()
    {
        // Filament notification for failure
        Notification::make()
            ->title('Payment Failed')
            ->body('The payment process has failed. Please try again.')
            ->danger()
            ->send();

        return redirect()->route('filament.admin.resources.payments.index');
    }

    // Stripe Payment Gateway
    public function stripePost(Request $request) {
        // Set Stripe API key
        // dd(env('STRIPE_SECRET'));
        Stripe::setApiKey(config('stripe.stripe_sk'));


        try {
            $appointmentId = Crypt::decryptString($request->appointment_id);
            $appointment = Appointment::findOrFail($appointmentId);

            $user = Auth::user();

            if ($user->role === 'patient') {
                $patient = $user->patient;
                if ($appointment->patient_id !== $patient->id) {
                    Notification::make()
                        ->title('Unauthorized Action')
                        ->body('You are not authorized to make payments for this appointment.')
                        ->danger()
                        ->send();

                    return redirect()->back();
                }
            }

            $payment = $appointment->payment;
            $amount = $payment->amount;

            $charge = \Stripe\Charge::create([
                'source' => $request->stripeToken,
                'description' => 'Payment for Appointment with '. $appointment->doctor->user->name,
                'amount' => $amount * 100,
                'currency' => 'NPR',
            ]);

            $appointment->payment->update([
                'payment_status' => 'paid',
                'payment_method' => 'stripe'
            ]);

            $appointment->update([
                'status' => 'confirmed'
            ]);

            Notification::make()
                        ->title('Payment Successful')
                        ->body('The payment has been successfully completed.')
                        ->success()
                        ->send();


            return redirect()->route('filament.admin.resources.appointments.index');

        } catch (CardException $e) {
            Notification::make()
                ->title('Invalid Response')
                ->body('Received an invalid response from Stripe.')
                ->danger()
                ->send();

            return redirect()->back();
        }
    }

}
