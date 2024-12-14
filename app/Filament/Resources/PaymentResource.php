<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Payment;
use Filament\Forms;
use Filament\Tables\Actions\Action;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Manage Appointments';


    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('appointment_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('pid'),
                Forms\Components\TextInput::make('payment_status')
                    ->required(),
                Forms\Components\TextInput::make('transaction_id'),
                Forms\Components\TextInput::make('payment_method'),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('appointment.patient.user.name')
                    ->label('Patient Name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('appointment.doctor.user.name')
                    ->label('Doctor Name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('appointment.appointment_date')
                    ->label('Appointment Date')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pid')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('transaction_id')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_method')
                    // ->default()
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
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
                // Action::make('Pay with eSewa')
                // ->action(function ($record) {
                //     return redirect()->route('payment.esewa', ['appointmentId' => $record->appointment_id]);
                // })
                // ->icon('heroicon-o-currency-dollar')
                // ->visible(fn($record) => $record->payment_status !== 'paid')
                // ->color('success')
                Action::make('Pay with eSewa')
                    ->action(function ($record) {
                        return redirect()->route('payment.esewa', ['appointmentId' => $record->appointment_id]);
                    })
                    ->icon('heroicon-o-currency-dollar')
                    ->visible(fn ($record) => $record->payment_status !== 'paid')
                    ->button()
                    ->color('success')
                    ->label('Pay via eSewa')
                    ->tooltip('Click to pay via eSewa'),
                
                Action::make('Pay via Stripe')
                    ->url(fn ($record) => route('filament.admin.resources.payments.stripe', ['appointmentId' => $record->id]))
                    ->icon('heroicon-o-currency-dollar')
                    ->visible(fn ($record) => $record->payment_status !== 'paid')
                    ->button()
                    ->color('primary')
                    ->label('Pay via Stripe')
                    ->tooltip('Click to pay via Stripe')
                

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'stripe' => Pages\StripePayment::route('/stripe/{appointmentId}'), 
            // 'view' => Pages\ViewPayment::route('/{record}'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
