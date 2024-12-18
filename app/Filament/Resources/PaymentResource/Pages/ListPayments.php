<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Payment;
use Filament\Resources\Components\Tab;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $user = Auth::user();

        return [
            'All' => Tab::make()
                ->icon('heroicon-o-ellipsis-horizontal-circle')
                ->extraAttributes(['class' => 'text-secondary']),

            'Paid' => Tab::make()
                ->icon('heroicon-o-credit-card')
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->where('payment_status', 'paid') // Filter by 'paid' payment_status
                )
                ->badge(
                    // Count paid payments for the logged-in user based on appointment relationship
                    Payment::where('payment_status', 'paid')
                        ->when($user->role === 'patient', function ($query) use ($user) {
                            $query->whereHas('appointment', function ($query) use ($user) {
                                $query->where('patient_id', $user->patient->id);
                            });
                        })
                        ->when($user->role === 'doctor', function ($query) use ($user) {
                            $query->whereHas('appointment', function ($query) use ($user) {
                                $query->where('doctor_id', $user->doctor->id);
                            });
                        })
                        ->count() // Count paid payments for the logged-in user
                ),

            'Unpaid' => Tab::make()
                ->icon('heroicon-o-exclamation-circle')
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->where('payment_status', 'unpaid') // Filter by 'unpaid' payment_status
                )
                ->badge(
                    // Count unpaid payments for the logged-in user based on appointment relationship
                    Payment::where('payment_status', 'unpaid')
                        ->when($user->role === 'patient', function ($query) use ($user) {
                            $query->whereHas('appointment', function ($query) use ($user) {
                                $query->where('patient_id', $user->patient->id);
                            });
                        })
                        ->when($user->role === 'doctor', function ($query) use ($user) {
                            $query->whereHas('appointment', function ($query) use ($user) {
                                $query->where('doctor_id', $user->doctor->id);
                            });
                        })
                        ->count() // Count unpaid payments for the logged-in user
                ),
        ];
    }


}
