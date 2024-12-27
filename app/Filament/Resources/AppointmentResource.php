<?php

namespace App\Filament\Resources;

use Closure;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Doctor;
use App\Models\Patient;
use Filament\Forms\Get;
use App\Models\Schedule;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Appointment;
use Filament\Actions\Action;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Split;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextArea;
use Illuminate\Database\Eloquent\Model;
use App\Rules\AppointmentValidationRule;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AppointmentResource\Pages;
use Filament\Forms\Components\Select as ComponentsSelect;
use Filament\Resources\Concerns\GloballySearchable;
use App\Filament\Resources\AppointmentResource\RelationManagers;
use App\Filament\Resources\AppointmentResource\RelationManagers\ReviewRelationManager;
use App\Filament\Resources\AppointmentResource\RelationManagers\PaymentRelationManager;

class AppointmentResource extends Resource
{
    // use GloballySearchable;
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationGroup = 'Manage Appointments';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $slug = 'appointments';

    protected static ?int $navigationSort = 1;

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->appointment_date;
    }

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
                            ->rules('exists:patients,id')
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
                            ->rules('exists:doctors,id')
                            ->columnSpanFull()
                            ->label('Doctor Name')
                            ->native(false)
                            ->reactive()
                            ->preload()
                            ->hidden(fn () => Auth::user()->role === 'doctor')
                            ->disabled(fn ($livewire) => Auth::user()->role === 'patient' && $livewire instanceof \Filament\Resources\Pages\EditRecord)
                            ->options(
                        Doctor::where('status', 'available')
                                    ->whereHas('schedules', function ($query) {
                                        $query->whereNotNull('id');
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
                            ->native(false)
                            ->columnSpanFull()
                            ->reactive()
                            ->default($appointment->id ?? null)
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
                                    'id' => $get('id'),
                                ];
                                return new AppointmentValidationRule($formState);
                            }),


                        TextInput::make('id')
                            ->hidden()
                            ->required(),


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
                            ->required()
                            ->rule(static function (Forms\Get $get, Forms\Components\Component $component): Closure {
                                return static function (string $attribute, $value, Closure $fail) use ($get) {
                                    $startTime = strtotime($get('start_time'));
                                    $endTime = strtotime($value);

                                    if ($startTime && $endTime) {
                                        $durationInMinutes = ($endTime - $startTime) / 60;

                                        if ($durationInMinutes < 30) {
                                            $fail("The duration between Start Time and End Time must be at least 30 minutes.");
                                        }
                                    }
                                };
                            }),

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
            PaymentRelationManager::class,
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
