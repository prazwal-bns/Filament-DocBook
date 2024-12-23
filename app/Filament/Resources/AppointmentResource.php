<?php

namespace App\Filament\Resources;

use Closure;
use App\Filament\Resources\AppointmentResource\Pages;
use App\Filament\Resources\AppointmentResource\RelationManagers;
use App\Filament\Resources\AppointmentResource\RelationManagers\ReviewRelationManager;
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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TextArea;
use App\Rules\AppointmentValidationRule;
use Carbon\Carbon;
use App\Models\Schedule;
use Filament\Actions\Action;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select as ComponentsSelect;
use Filament\Forms\Components\Split;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationGroup = 'Manage Appointments';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();

        if ($user->role === 'patient') {
            return static::getModel()::where('patient_id', $user->patient->id)->count();
        } elseif ($user->role === 'doctor') {
            return static::getModel()::where('doctor_id', $user->doctor->id)->count();
        }
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() > 10 ? 'info' : 'success';
    }



    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = Auth::user();

        if ($user->role === 'admin') {
            return $query; // Admins can see all appointments
        }

        if ($user->role === 'patient') {
            return $query->where('patient_id', Auth::user()->patient->id);
        }

        if ($user->role === 'doctor') {
            return $query->where('doctor_id', Auth::user()->doctor->id);
        }

        return $query->where('id', null); // If not admin, patient, or doctor, show no results
    }




    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\Select::make('patient_id')
            Split::make([
                Section::make('Appointment')
                    ->schema([
                        Select::make('patient_id')
                            ->label('Patient Name')
                            // ->searchable()
                            ->native(false)
                            ->preload()
                            ->options(
                                Patient::with('user')
                                    ->get()->pluck('user.name','id')
                            )
                            ->hidden(fn () => Auth::user()->role === 'patient')
                            ->columnSpanFull()
                            ->required(fn () => Auth::user()->role !== 'patient'),

                        Forms\Components\Hidden::make('status_only_update')
                            ->default(false),

                        Select::make('doctor_id')
                            ->columnSpanFull()
                            ->label('Doctor Name')
                            ->native(false)
                            ->reactive()
                            ->preload()
                            ->hidden(fn () => Auth::user()->role === 'doctor')
                            // ->disabled(fn ($livewire) => Auth::user()->role === 'patient' && $livewire instanceof \Filament\Resources\Pages\EditRecord)
                            ->options(
                        Doctor::where('status', 'available')
                                    ->whereHas('schedules', function ($query) {
                                        $query->whereNotNull('id'); // Ensure the doctor has at least one schedule
                                    })
                                    ->with('user')
                                    ->get()
                                    ->pluck('user.name', 'id')
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
                            ->columnSpanFull()
                            ->reactive()
                            ->minDate(now()->toDateString())
                            ->afterStateUpdated(function (callable $set, $state) {
                                if ($state) {
                                    $dayName = Carbon::parse($state)->format('l');
                                    $set('day', $dayName); // Set the day field based on the selected date
                                }
                            })
                            ->required()
                            ->rule(static function (Forms\Get $get) {
                                $formState = [
                                    'start_time' => $get('start_time'),
                                    'end_time' => $get('end_time'),
                                    'doctor_id' => $get('doctor_id'),
                                    'day' => $get('day'),
                                ];
                                return new AppointmentValidationRule($formState);
                            }),


                        TimePicker::make('start_time')
                            ->label('Start Time')
                            ->format('H:i')
                            ->reactive()
                            ->seconds(false)
                            ->required(),

                        TimePicker::make('end_time')
                            ->label('End Time')
                            ->format('H:i')
                            ->reactive()
                            ->seconds(false)
                            ->after('start_time')
                            ->required(),

                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'completed' => 'Completed',
                            ])
                            ->label('Appointment Status')
                            ->hidden()
                            ->default('pending'),

                        TextInput::make('day')
                            ->hidden()
                            ->required(),

                        Textarea::make('appointment_reason')
                            ->rows(10)
                            ->columnSpanFull(),
                ])->columns(2),

                ]),


            Split::make([
                    Section::make('Doctor\'s Schedule and Booked Appointments')
                    // ->hidden(fn ($get) => !$get('doctor_id'))
                    ->schema([
                        Card::make()
                            ->schema([
                                Forms\Components\Placeholder::make('schedules')
                                ->label('Schedules')
                                ->content(function ($get) {
                                    $doctorId = $get('doctor_id');
                                    if (!$doctorId) {
                                        return 'No doctor selected.';
                                    }

                                    $schedules = Schedule::where('doctor_id', $doctorId)->get();

                                    if ($schedules->isEmpty()) {
                                        return 'No schedules available for this doctor.';
                                    }

                                    $schedulesData = $schedules->map(function ($schedule) {
                                        return [
                                            'day' => $schedule->day,
                                            'time' => "{$schedule->start_time} - {$schedule->end_time}",
                                            'status' => $schedule->status,
                                        ];
                                    })->values()->toArray();

                                    return view('filament.forms.components.list', [
                                        'columns' => ['day', 'time', 'status'],
                                        'rows' => $schedulesData,
                                    ]);
                                })
                                ->columnSpanFull(),
                        ]),

                        Card::make()
                        ->schema([
                            Forms\Components\Placeholder::make('appointments')
                                ->label('Booked Appointments')
                                ->content(function ($get) {
                                    $doctorId = $get('doctor_id');
                                    if (!$doctorId) {
                                        return 'No doctor selected.';
                                    }

                                    $appointments = Appointment::where('doctor_id', $doctorId)
                                        ->with(['patient.user'])
                                        ->get()
                                        ->where('status', '!=', 'completed');

                                    if ($appointments->isEmpty()) {
                                        return 'No appointments booked for this doctor.';
                                    }

                                    $appointmentsData = $appointments->map(function ($appointment) {
                                        $patientName = $appointment->patient->user->name ?? 'Unknown Patient';
                                        return [
                                            // 'patient' => $patientName,
                                            'date' => $appointment->appointment_date,
                                            'time' => "{$appointment->start_time} - {$appointment->end_time}",
                                            'status' => $appointment->status,
                                        ];
                                    })->values()->toArray();

                                    return view('filament.forms.components.list', [
                                        // 'columns' => ['patient', 'date', 'time', 'status'],
                                        'columns' => ['date', 'time'],
                                        'rows' => $appointmentsData,
                                    ]);
                                })
                                ->columnSpanFull(),
                        ]),
                ]),

            ])->extraAttributes(['class' => 'w-[30%]'])

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
            ReviewRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'view' => Pages\ViewAppointment::route('/{record}'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }

    public static function canEdit(Model $record): bool
    {
        return $record->status == 'pending';
    }
}
