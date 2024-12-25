<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Payment;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\PaymentResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Filament\Resources\PaymentResource\RelationManagers\AppointmentRelationManager;
use Filament\Infolists\Components\Section;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Manage Appointments';


    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();

        if ($user->role === 'patient') {
            return static::getModel()::whereHas('appointment', function ($query) use ($user) {
                $query->where('patient_id', $user->patient->id);
            })->count();
        } elseif ($user->role === 'doctor') {
            return static::getModel()::whereHas('appointment', function ($query) use ($user) {
                $query->where('doctor_id', $user->doctor->id);
            })->count();
        }

        return static::getModel()::count();
    }


    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'info' ;
    }


    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = Auth::user();

        if ($user->role === 'admin') {
            return $query; // Admins can see all appointments
        }

        if ($user->role === 'patient') {
            return $query->whereHas('appointment', function ($query) use ($user) {
                $query->where('patient_id', $user->patient->id);
            });
        }

        // For doctors, filter payments based on their associated appointments
        if ($user->role === 'doctor') {
            return $query->whereHas('appointment', function ($query) use ($user) {
                $query->where('doctor_id', $user->doctor->id);
            });
        }

        return $query->where('id', null);
    }


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
                        ->tooltip('Click to pay via eSewa'),

                    Action::make('Pay via Stripe')
                        ->url(fn ($record) => route('filament.admin.resources.payments.stripe', ['appointmentId' => $record->id]))
                        ->icon('heroicon-o-currency-dollar')
                        ->visible(fn ($record) => $record->payment_status !== 'paid')
                        // ->button()
                        ->color('secondary')
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

    public static function getRelations(): array
    {
        return [
            AppointmentRelationManager::class,
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Payment Information')
                ->schema([
                   TextEntry::make('payment_status'),
                   TextEntry::make('amount'),
                ])
                ->columns(2),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'stripe' => Pages\StripePayment::route('/stripe/{appointmentId}'),
            'view' => Pages\ViewPayment::route('/{record}'),
            // 'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }


    public static function canEdit(Model $record): bool
    {
        return false; // Disables the edit functionality
    }

}
