<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Filament\Resources\ScheduleResource\RelationManagers;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Schedule;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Closure;


class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationGroup = 'Mangage Doctor Schedules';

    protected static ?int $navigationSort = 8;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';


    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = Auth::user();

        if ($user->role === 'admin') {
            return $query;
        }

        if ($user->role === 'doctor') {
            return $query->where('doctor_id', Auth::user()->doctor->id);
        }

        return $query->where('id', null);
    }

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();

        if ($user->role === 'doctor') {
            return Schedule::where('doctor_id', $user->doctor->id)->count();
        }
        return Schedule::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'lime';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('doctor_id')
                    ->options(
                        Doctor::join('users', 'doctors.user_id', '=', 'users.id')
                            ->pluck('users.name', 'doctors.id')
                    )
                    ->required(),


                Forms\Components\Select::make('day')
                    ->options([
                        "Sunday" => "Sunday",
                        "Monday" => "Monday",
                        "Tuesday" => "Tuesday",
                        "Wednesday" => "Wednesday",
                        "Thursday" => "Thursday",
                        "Friday" => "Friday",
                        "Saturday" => "Saturday"
                    ])
                    ->required()
                    ->reactive()
                    ->disabled(),

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
                        'available' => 'Available',
                        'unavailable' => 'Unavailable',
                    ])
                    ->rule(static function (Forms\Get $get, Forms\Components\Component $component): Closure {
                        return static function (string $attribute, $value, Closure $fail) use ($get, $component) {
                            $doctorId = $get('doctor_id');
                            $day = $get('day');

                            if ($doctorId && $day) {
                                $appointmentsExist = Appointment::where('doctor_id', $doctorId)
                                    ->where('day', $day)
                                    ->where('status', '!=', 'completed')
                                    ->exists();

                                if ($appointmentsExist) {
                                    $fail("Sorry !! You can't change your status as you still have appointments on this day.");

                                    Notification::make()
                                    ->title('Error')
                                    ->body('Sorry !! You can\'t change your status as you still have appointments on this day.')
                                    ->danger()
                                    ->send();
                                }
                            }
                        };
                    })
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('doctor.user.name')
                    ->label('Doctor Name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('day')
                    ->searchable(),
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
            ])
            ->paginated([7, 10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(7)
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchedules::route('/'),
            // 'create' => Pages\CreateSchedule::route('/create'),
            'view' => Pages\ViewSchedule::route('/{record}'),
            'edit' => Pages\EditSchedule::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
