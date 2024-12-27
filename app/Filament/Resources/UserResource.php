<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Filament\Resources\UserResource\RelationManagers\DoctorRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\PatientRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\PatientsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\UserRelationManager;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Specialization;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'Users';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() > 10 ? 'info' : 'success';
    }


    public static function form(Form $form): Form
    {
        $user = User::with('doctor.specialization')->find(request()->route('record'));
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    // ->unique('users', 'email', null, 'ignoreCase')
                    ->required(),
                    // ->rules(function () {
                    //     return [
                    //         function ($attribute, $value, $fail) {
                    //             if (\App\Models\User::whereRaw('LOWER(email) = ?', [strtolower($value)])->exists()) {
                    //                 $fail('This email has already been taken.');
                    //             }
                    //         },
                    //     ];
                    // }),

                Forms\Components\Select::make('role')
                ->options([
                    // 'admin' => 'Admin',
                    'patient' => 'Patient',
                    'doctor' => 'Doctor',
                ])
                ->required()
                ->hidden(fn(callable $get) => $get('role') === 'admin')
                ->reactive()
                ->afterStateUpdated(function (callable $set, $state) {
                    // Clear specialization when the role is not 'doctor'
                    if ($state !== 'doctor') {
                        $set('specialization_id', null);
                    }
                }),

                Forms\Components\Select::make('gender')
                    ->label('Gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                    ])
                    ->required(fn (callable $get) => $get('role') === 'patient')
                    ->hidden(fn (callable $get) => $get('role') !== 'patient'),


                Forms\Components\Select::make('specialization_id')
                        ->label('Specialization Name')
                        ->relationship('doctor.specialization', 'name')
                        ->required(fn (callable $get) => $get('role') === 'doctor')
                        ->hidden(fn (callable $get) => $get('role') !== 'doctor')
                        ->default(fn ($record) => $record->doctor->specialization_id ?? null),

                // Forms\Components\Select::make('specialization_id')
                //         ->label('Specialization')
                //         ->options(fn() => Specialization::pluck('name', 'id')->toArray())
                //         // ->disabled(fn(callable $get) => $get('id') !== null)
                //         ->required()
                //         ->default(fn($record) => $record->specialization_id)
                //         ->visible(fn($get) => $get('role') == 'doctor'),

                    Forms\Components\TextInput::make('address'),
                    Forms\Components\TextInput::make('phone')
                        ->label('Phone Number')
                        ->numeric()
                        ->minLength(10)
                        ->maxLength(10)
                        ->placeholder('Enter a valid phone number')
                        ->rule('regex:/^(98|97|96|01|061|062|063|064|065|066|067|068|069)\d{6,8}$/'),
                Forms\Components\DateTimePicker::make('email_verified_at')->hidden(),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required(fn(callable $get)=> $get('id') == null)
                    ->placeholder(function(callable $get){
                        if($get('id') !== null){
                            return "Enter new password only if you want to reset password.";
                        }
                    }),
            ]);
    }




    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
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
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                ->before(function (DeleteAction $action, $record) {
                    if ($record->role === 'doctor') {
                        $hasUpcomingAppointments = $record->doctor?->appointments()
                            ->where('date', '>=', now())
                            ->where('status', '!=', 'completed')
                            ->exists();

                        if ($hasUpcomingAppointments) {
                            Notification::make()
                                ->title('Deletion Failed')
                                ->body('This user cannot be deleted because this doctor currenlty has upcoming appointments.')
                                ->danger()
                                ->send();

                            // Halt the deletion process
                            $action->cancel();
                        }
                    }
                    if($record->role === 'admin'){
                        Notification::make()
                            ->title('Deletion Failed')
                            ->body('Admin User Can\'t be deleted.')
                            ->danger()
                            ->send();

                        // Halt the deletion process
                        $action->cancel();
                    }
                }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        $recordId = request()->route('record');

        // if (!$recordId) {
        //     return [];
        // }

        $user = User::find($recordId);

        if (!$user) {
            return [];
        }


        // Check the role of the user and return the appropriate relations.
        if ($user->role === 'patient') {
            return [
                PatientRelationManager::class,
            ];
        } elseif ($user->role === 'doctor') {
            return [
                DoctorRelationManager::class,
            ];
        }

        // Default: No relations for other roles
        return [];
    }



    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    // protected static function getPages(): array
    // {
    //     return [
    //         'create' => Register::class,  // Register the 'Register' page
    //     ];
    // }
}
