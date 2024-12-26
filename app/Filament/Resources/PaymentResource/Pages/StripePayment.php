<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Crypt;

class StripePayment extends Page
{
    protected static string $resource = PaymentResource::class;

    protected static string $view = 'filament.resources.payment-resource.pages.stripe-payment';

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $title = 'Pay with Stripe';
    protected static ?string $slug = 'stripe-payment';

    public $appointmentId;

    public function mount($appointmentId)
    {
        try {
            $this->appointmentId = Crypt::decryptString($appointmentId);
        } catch (\Exception $e) {
            abort(403, 'Invalid or expired appointment ID.');
        }
    }
}
