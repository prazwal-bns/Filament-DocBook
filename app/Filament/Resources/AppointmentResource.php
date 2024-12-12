<?php

namespace App\Filament\Resources;

use Closure;
use App\Filament\Resources\AppointmentResource\Pages;
use App\Filament\Resources\AppointmentResource\RelationManagers;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Get;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\DateFilter;
use Carbon\Carbon;
use App\Models\Schedule;
use Filament\Infolists\Infolist;
use Filament\Tables\Filters\Filter;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationGroup = 'Manage Appointments';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?int $navigationSort = 1;


    public static function form(Form $form): Form
    {
      
        return $form
            ->schema([
                Forms\Components\Select::make('patient_id')
                    ->label('Patient Name')
                    ->searchable()
                    ->preload()
                    ->options(
                        Patient::with('user')
                            ->get()->pluck('user.name','id')
                    )
                    ->required(),

                Forms\Components\Select::make('doctor_id')
                    ->label('Doctor Name')
                    ->searchable()
                    ->preload()
                    ->options(
                        Doctor::with('user')
                            ->get()->where('status','available')->pluck('user.name','id')
                    )
                    ->required()
                    ->rule(static function (Forms\Get $get, Forms\Components\Component $component): Closure {
                        return static function (string $attribute, $value, Closure $fail) use ($get, $component) {
                            $doctorId = $value;
                            $day = $get('day');
                
                            // Fetch the doctor's schedule for the selected day
                            $schedule = Schedule::where('doctor_id', $doctorId)
                                ->where('day', $day)
                                ->where('status', 'available')
                                ->first();
                
                            if (!$schedule) {
                                $fail("The selected doctor is not available on this day.");
                                return;
                            }
                        };
                    }),
                
                DatePicker::make('appointment_date')
                    ->label('Appointment Date')
                    ->reactive()
                    ->minDate(now())
                    ->afterStateUpdated(function (callable $set, $state) {
                        if ($state) {
                            $dayName = Carbon::parse($state)->format('l');
                            $set('day', $dayName); // Set the day field based on the selected date
                        }
                    })
                    ->required()
                    ->rule(static function (Forms\Get $get, Forms\Components\Component $component): Closure {
                        return static function (string $attribute, $value, Closure $fail) use ($get, $component) {
                            // Ensure the date is available in the form before validating
                            $appointmentDate = $value;
                            $startTime = $get('start_time');
                            $endTime = $get('end_time');
                            $doctorId = $get('doctor_id');
                            $day = $get('day'); // Get the day based on the selected date

                            // Fetch the doctor's schedule for the selected day
                            $schedule = Schedule::where('doctor_id', $doctorId)
                                ->where('day', $day)
                                ->where('status', 'available')
                                ->first();

                            if (!$schedule) {
                                $fail("The selected doctor is not available on this day.");
                                return;
                            }

                            // Check if the appointment start and end time are within the doctor's schedule
                            if (
                                Carbon::parse($startTime)->lt($schedule->start_time) ||
                                Carbon::parse($endTime)->gt($schedule->end_time)
                            ) {
                                $fail("The appointment must be scheduled between the doctor's available hours of {$schedule->start_time} - {$schedule->end_time}.");
                            }

                            // Check for overlapping appointments
                            $appointments = Appointment::where('doctor_id', $doctorId)
                                ->where('appointment_date', $appointmentDate)
                                ->get();

                            $overlappingAppointment = $appointments->contains(function ($appointment) use ($startTime, $endTime) {
                                return (
                                    (Carbon::parse($startTime)->between($appointment->start_time, $appointment->end_time)) ||
                                    (Carbon::parse($endTime)->between($appointment->start_time, $appointment->end_time)) ||
                                    (Carbon::parse($startTime)->lte($appointment->start_time) && Carbon::parse($endTime)->gte($appointment->end_time))
                                );
                            });

                            if ($overlappingAppointment) {
                                $schedule = $appointments->map(function ($appointment) {
                                    return Carbon::parse($appointment->start_time)->format('H:i') . ' - ' . Carbon::parse($appointment->end_time)->format('H:i');
                                })->implode(', ');

                                $fail("The selected doctor is already booked for this time slot. He's not available during: {$schedule}");
                            }
                        };
                    }),

                Forms\Components\TimePicker::make('start_time')
                    ->label('Start Time')
                    ->format('H:i')
                    ->reactive()
                    ->seconds(false)
                    ->required(),

                Forms\Components\TimePicker::make('end_time')
                    ->label('End Time')
                    ->format('H:i')
                    ->reactive()
                    ->seconds(false)
                    ->after('start_time')
                    ->required(),
                
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'completed' => 'Completed',
                    ])
                    ->label('Appointment Status')
                    ->required()
                    ->default('pending'),

                Forms\Components\TextInput::make('day')
                ->hidden()
                ->required(),

                Forms\Components\Textarea::make('appointment_reason')
                    ->rows(10)
                    ->cols(20)
                    ->columnSpanFull(),
                
            ]);
        
    }


    public static function table(Table $table): Table
    {
        
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.user.name')
                    ->sortable()
                    ->searchable()
                    ->label('Patient Name'),
                Tables\Columns\TextColumn::make('doctor.user.name')
                    ->label('Doctor Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('appointment_date')
                    ->date()
                    ->sortable(),
                    // ->defaultSort('asc'),
                Tables\Columns\TextColumn::make('start_time'),
                Tables\Columns\TextColumn::make('end_time'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('day')
                    ->searchable(),
            ])
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
                    ->placeholder('Select Doctor'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->successNotification(function ($record) {
                        return Notification::make()
                            ->success()
                            ->icon('heroicon-o-trash')
                            ->title('Appointment Removed!')
                            ->body("The appointment with Dr. {$record->doctor->user->name} for {$record->patient->user->name} on {$record->appointment_date} has been removed.");
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

        ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            // 'view' => Pages\ViewAppointment::route('/{record}'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
