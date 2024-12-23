<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PDFController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::middleware(['auth','verified'])->group(function(){
    Route::get('/payment/{appointmentId}/pay', [PaymentController::class, 'esewaPay'])->name('payment.esewa');

    Route::match(['get', 'post'],'/payment/success', [PaymentController::class, 'esewaPaySuccess'])->name('payment.success');
    Route::get('/payment/failure', [PaymentController::class, 'esewaPayFailure'])->name('payment.failure');

    // Stripe payment
    Route::controller(PaymentController::class)->group(function(){
        Route::post('/stripe/payment', 'stripePost')->name('stripe.post');
    });

    Route::get('/appointments/pdf/{appointmentId}', [PDFController::class, 'downloadPdf'])->name('appointments.downloadPdf');
});

