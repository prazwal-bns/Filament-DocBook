<?php

namespace App\Filament\Resources\AppointmentResource\Pages;


use App\Filament\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Doctor;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Actions\SelectAction;
use Filament\Forms\Components\Actions as ComponentsActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Actions\BulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Filament\Forms\Components\TimePicker;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;

class ListAppointments extends ListRecords
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }


    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                // TextColumn::make('id')
                //     ->label('Appointment ID')
                //     ->sortable(),

                TextColumn::make('patient.user.name')
                    ->label('Patient Name')
                    ->extraAttributes(['style' => 'color: red !important;'])
                    ->searchable(),

                TextColumn::make('doctor.user.name')
                    ->label('Doctor Name')
                    ->searchable(),

                TextColumn::make('appointment_date')
                    ->label('Date')
                    ->date()
                    ->searchable(),


                BadgeColumn::make('payment.payment_status')
                    ->label('Payment')
                    ->colors([
                        'success' => 'paid',
                        'danger' => 'unpaid',
                    ])->searchable(),

                TextColumn::make('day')
                        ->searchable(),

                TextColumn::make('start_time')
                    ->label('Start Time')
                    ->formatStateUsing(function ($state) {
                        return Carbon::parse($state)->format('g:i A'); // 12-hour format with AM/PM
                    }),

                TextColumn::make('end_time')
                    ->label('End Time')
                    ->formatStateUsing(function ($state) {
                        return Carbon::parse($state)->format('g:i A'); // 12-hour format with AM/PM
                    }),


                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'secondary' => 'confirmed',
                        'success' => 'completed',
                    ])
                    ->sortable(),
            ])
            ->defaultPaginationPageOption(5)
            ->defaultSort('appointment_date')
            ->filters([
                Filter::make('appointment_date')
                ->label('Appointment Date')
                ->form([
                    DatePicker::make('date')
                        ->placeholder('Select Appointment Date')
                        ->label('Select Appointment Date'),
                ])
                ->query(function (Builder $query, array $data) {
                    if ($data['date']) {
                        // Filter the records based on the selected appointment date
                        $query->whereDate('appointment_date', Carbon::parse($data['date'])->toDateString());
                    }
                })
                ->indicateUsing(function (array $data): ?string {
                    if (! isset($data['date']) || ! $data['date']) {
                        return null;
                    }

                    // Display the selected date in a user-friendly format
                    return 'Appointment on ' . Carbon::parse($data['date'])->toFormattedDateString();
                }),

                SelectFilter::make('day')
                    ->options([
                        'Sunday' => 'Sunday',
                        'Monday' => 'Monday',
                        'Tuesday' => 'Tuesday',
                        'Wednesday' => 'Wednesday',
                        'Thursday' => 'Thursday',
                        'Friday' => 'Friday',
                        'Saturday' => 'Saturday',
                    ])
                    ->label('Day')
                    ->placeholder('All Days'),

                SelectFilter::make('doctor_id')
                    ->options(function () {
                        return Doctor::all()->pluck('user.name', 'id')->toArray();
                    })
                    ->label('Doctor')
                    ->hidden(fn()=> Auth::user()->role === 'doctor')
                    ->placeholder('Select Doctor'),
            ])
            ->actions([
                // Action::make('Download')
                //     ->url(fn ($record) => route('appointments.downloadPdf', ['appointmentId' => $record->id]))
                //     ->icon('heroicon-o-arrow-down-tray')
                //     ->visible(fn ($record) => $record->payment_status !== 'paid')
                //     ->color('fuchsia')
                //     ->iconButton()
                //     ->tooltip('Download Appointment Details'),

                Action::make('Download')
                    ->url(fn ($record) => route('appointments.downloadPdf', ['appointmentId' => $record->id]))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn ($record) => $record->payment_status !== 'paid')
                    ->color('fuchsia')
                    ->iconButton()
                    ->tooltip('Download Appointment Details')
                    ->openUrlInNewTab(),

                Tables\Actions\ViewAction::make()->hidden(true),
                Tables\Actions\EditAction::make()->visible(fn($record) => $record->status === 'pending')->iconButton(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record) => $record->status === 'pending')
                    ->successNotification(function ($record) {
                        return Notification::make()
                            ->success()
                            ->icon('heroicon-o-trash')
                            ->title('Appointment Removed!')
                            ->body("The appointment with Dr. {$record->doctor->user->name} for {$record->patient->user->name} on {$record->appointment_date} has been removed.");
                    }),

                ActionGroup::make([
                    Action::make('review')
                        ->label('Leave a Review')
                        ->color('teal')
                        ->icon('heroicon-o-pencil-square')
                        ->visible(fn ($record) => ($record->status === 'completed' && !$record->review) &&
                             (Auth::user()->role === 'admin' || Auth::user()->doctor))
                        ->url(fn ($record) => route('filament.admin.resources.reviews.create', ['appointment_id' => $record->id])),


                    Action::make('view_review')
                        ->label('View Review')
                        ->color('indigo')
                        ->icon('heroicon-o-eye')
                        ->visible(fn ($record) => $record->review)
                        ->url(fn ($record) => route('filament.admin.resources.reviews.view', ['record' => $record->review])),

                    Action::make('updateStatus')
                        ->label('Update Status')
                        ->color('primary')
                        ->icon('heroicon-o-pencil')
                        ->visible(fn ($record) => Auth::user()->role === 'admin' || Auth::user()->doctor)
                        ->hidden(fn($record) => $record->status === 'completed'|| $record->status === 'pending')
                        ->form([
                            Select::make('status')
                                ->label('Select Status')
                                ->options([
                                    // 'pending' => 'Pending',
                                    'confirmed' => 'Confirmed',
                                    'completed' => 'Completed',
                                ])
                                ->default('pending')
                                ->required(),
                        ])
                        ->action(function ($record, $data) {
                            // Update the status for the specific record
                            $record->update([
                                'status' => $data['status'],
                            ]);

                            Notification::make()
                                ->title('Status Updated')
                                ->success()
                                ->send();
                        }),
                ])
                    ->label('More actions')
                    ->icon('heroicon-m-information-circle')
                    ->size(ActionSize::Small)
                    // ->color('violet')
                    ->color('my-color')
                    ->dropdownPlacement('top-start')
                    ->button(),


            ])
            ->bulkActions([

                // Bulk Action for updating the status of selected records
                // BulkAction::make('updateStatusBulk')
                //     ->label('Update Status for Selected')
                //     ->form([
                //         Select::make('status')
                //             ->label('Select Status')
                //             ->options([
                //                 // 'pending' => 'Pending',
                //                 'confirmed' => 'Confirmed',
                //                 'completed' => 'Completed',
                //             ])
                //             ->required(),
                //     ])
                //     ->action(function ($records, $data) {
                //         // Update the status for selected records
                //         // foreach ($records as $record) {
                //         //     $record->update([
                //         //         'status' => $data['status'],
                //         //     ]);
                //         // }

                //         foreach ($records as $record) {
                //             if ($record->status === 'pending' && $record->payment->payment_status !== 'paid') {
                //                 Notification::make()
                //                     ->title('Payment Required')
                //                     ->body("Pending appointments can't be updated unless payment has been made.")
                //                     ->danger()
                //                     ->send();
                //             } else {
                //                 // Update the status if the payment is made or if not in 'pending' status
                //                 $record->update([
                //                     'status' => $data['status'],
                //                 ]);
                //             }
                //         }

                //         Notification::make()
                //             ->title('Status Updated')
                //             ->success()
                //             ->send();
                //     })->hidden(fn() => Auth::user()->role === 'patient'),

            ]);
    }

    public function getTabs(): array
    {
        $user = Auth::user();

        return [
            'All' => Tab::make()
            ->icon('heroicon-o-ellipsis-horizontal-circle'),

            'Pending' => Tab::make()
                ->icon('heroicon-o-clock')
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->where('status', 'pending') // Filter by 'pending' status
                )
                ->badge(
                    // Filter by status and also by the user’s role
                    Appointment::where('status', 'pending')
                        ->when($user->role === 'patient', function ($query) use ($user) {
                            $query->where('patient_id', $user->patient->id);
                        })
                        ->when($user->role === 'doctor', function ($query) use ($user) {
                            $query->where('doctor_id', $user->doctor->id);
                        })
                        ->count() // Count pending appointments for the logged-in user
                ),

            'Confirmed' => Tab::make()
                ->icon('heroicon-o-check-badge')
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->where('status', 'confirmed') // Filter by 'confirmed' status
                )
                ->badge(
                    // Filter by status and also by the user’s role
                    Appointment::where('status', 'confirmed')
                        ->when($user->role === 'patient', function ($query) use ($user) {
                            $query->where('patient_id', $user->patient->id);
                        })
                        ->when($user->role === 'doctor', function ($query) use ($user) {
                            $query->where('doctor_id', $user->doctor->id);
                        })
                        ->count() // Count confirmed appointments for the logged-in user
                ),

            'Completed' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->where('status', 'completed') // Filter by 'completed' status
                )
                ->icon('heroicon-o-shield-check')
                ->badge(
                    Appointment::where('status', 'completed')
                        ->when($user->role === 'patient', function ($query) use ($user) {
                            $query->where('patient_id', $user->patient->id);
                        })
                        ->when($user->role === 'doctor', function ($query) use ($user) {
                            $query->where('doctor_id', $user->doctor->id);
                        })
                        ->count()
                )
                ->extraAttributes(['class' => 'flex justify-end']),
        ];
    }





    // public function getTabs(): array
    // {
    //     $today = Carbon::today();

    //     return [
    //         'All' => Tab::make(),
    //         'Today' => Tab::make()
    //             ->modifyQueryUsing(fn (Builder $query) =>
    //                 $query->whereDate('appointment_date', '=', $today)
    //             )
    //             ->badge(
    //                 Appointment::whereDate('appointment_date', '=', $today)->count()
    //             ),
    //         'This Week' => Tab::make()
    //             ->modifyQueryUsing(fn (Builder $query) =>
    //                 $query->whereDate('appointment_date', '>=', $today->startOfWeek())
    //             )
    //             ->badge(
    //                 Appointment::whereDate('appointment_date', '>=', $today->startOfWeek())->count()
    //             ),
    //         'This Month' => Tab::make()
    //             ->modifyQueryUsing(fn (Builder $query) =>
    //                 $query->whereDate('appointment_date', '>=', $today->startOfMonth())
    //             )
    //             ->badge(
    //                 Appointment::whereDate('appointment_date', '>=', $today->startOfMonth())->count()
    //             ),
    //         'This Year' => Tab::make()
    //             ->modifyQueryUsing(fn (Builder $query) =>
    //                 $query->whereDate('appointment_date', '>=', $today->startOfYear())
    //             )
    //             ->badge(
    //                 Appointment::whereDate('appointment_date', '>=', $today->startOfYear())->count()
    //             ),
    //     ];
    // }



}
