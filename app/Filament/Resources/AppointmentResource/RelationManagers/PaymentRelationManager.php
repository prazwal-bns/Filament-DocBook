<?php

namespace App\Filament\Resources\AppointmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class PaymentRelationManager extends RelationManager
{
    // protected static string $relationship = 'payment';
    protected static string $relationship = 'payment';
    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('payment_status')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('appointment.patient.user.name')
                    ->label('Patient Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('appointment.doctor.user.name')
                    ->label('Doctor Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('appointment.appointment_date')
                    ->label('Appointment Date')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pid')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),

                BadgeColumn::make('payment_status')
                    ->label('Payment Status')
                    ->searchable()
                    ->icon(function ($record) {
                        return $record->payment_status === 'paid'
                            ? 'heroicon-o-credit-card'
                            : 'heroicon-o-exclamation-circle';
                    })
                    ->colors([
                        'success' => 'paid',
                        'danger' => 'unpaid',
                    ]),

                Tables\Columns\TextColumn::make('transaction_id')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->default("-")
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultPaginationPageOption(5)
            ->actions([
                ActionGroup::make([
                    Action::make('Pay with eSewa')
                        ->action(function ($record) {
                            return redirect()->route('payment.esewa', ['appointmentId' => $record->appointment_id]);
                        })
                        ->icon('heroicon-o-currency-dollar')
                        ->visible(fn ($record) => $record->payment_status !== 'paid')
                        // ->button()
                        ->color('success')
                        ->label('Pay via eSewa')
                        ->requiresConfirmation()
                        ->modalHeading('Confirmation Required')
                        ->modalSubheading('Are you sure you want to make the payment via E-Sewa?')
                        ->tooltip('Click to pay via eSewa'),

                    Action::make('Pay via Stripe')
                        ->action(function ($record) {
                            $encryptedAppointmentId = Crypt::encryptString($record->appointment_id);

                            return redirect()->route('filament.admin.resources.payments.stripe', ['appointmentId' => $encryptedAppointmentId]);

                            // return redirect()->route('filament.admin.resources.payments.stripe');
                        })
                        ->icon('heroicon-o-currency-dollar')
                        ->visible(fn ($record) => $record->payment_status !== 'paid')
                        ->color('secondary')
                        ->requiresConfirmation()
                        ->modalHeading('Confirmation Required')
                        ->modalSubheading('Are you sure you want to make the payment via Stripe?')
                        ->label('Pay via Stripe')
                        ->tooltip('Click to pay via Stripe')
                    ])
                ->label('Make Payment')
                ->icon('heroicon-m-credit-card')
                ->dropdownPlacement('top-start')
                ->size(ActionSize::Small)
                // ->color('purple')
                ->color('my-btn')
                ->visible(fn($record) => (Auth::user()->role === 'admin' || Auth::user()->role ==='patient'))
                ->button(),

                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
                // Action::make('Pay with eSewa')
                // ->action(function ($record) {
                //     return redirect()->route('payment.esewa', ['appointmentId' => $record->appointment_id]);
                // })
                // ->icon('heroicon-o-currency-dollar')
                // ->visible(fn($record) => $record->payment_status !== 'paid')
                // ->color('success')

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
