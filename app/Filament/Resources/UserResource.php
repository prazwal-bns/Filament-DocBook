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
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'Mangage Users';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        $user = User::with('doctor.specialization')->find(request()->route('record'));
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required(),

                Forms\Components\Select::make('role')
                ->options([
                    // 'admin' => 'Admin',
                    'patient' => 'Patient',
                    'doctor' => 'Doctor',
                ])
                ->required()
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
                    ->required(),
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
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // public static function getRelations(): array
    // {   
    //     return [
    //         PatientRelationManager::class,
    //         DoctorRelationManager::class,
    //     ];
    // }

    public static function getRelations(): array
    {
        $recordId = request()->route('record'); 

        if (!$recordId) {
            return [];
        }

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
            // 'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
