<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Filament\Resources\ReviewResource\RelationManagers;
use App\Models\Appointment;
use App\Models\Review;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\ValidationException;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';

    protected static ?string $navigationLabel = 'Manage Reviews';
    protected static ?string $navigationGroup = 'Manage Appointments';


    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('appointment_id')
                    ->label('Select Completed Appointment')
                    ->options(
                        Appointment::query()
                            ->where('status', 'completed')
                            ->with(['patient', 'doctor'])
                            ->get()
                            ->mapWithKeys(function ($appointment) {
                                return [
                                    $appointment->id => "{$appointment->patient->user->name} with Dr. {$appointment->doctor->user->name} on {$appointment->appointment_date}",
                                ];
                            })
                    )
                    ->searchable()
                    ->required()
                    ->placeholder('Select a completed appointment')
                    ->visible(fn () => !request()->query('appointment_id')),

                Forms\Components\Hidden::make('appointment_id')
                    ->default(fn () => request()->query('appointment_id'))
                    ->visible(fn () => request()->query('appointment_id')),

                Forms\Components\Textarea::make('review_msg')
                    ->label('Review Message')
                    ->required() 
                    ->columnSpanFull(),
                
            ]);
    }

    public static function getActions(): array
    {
        return [
            Action::make('create')
                ->label('Add New Review')
                ->hidden(fn () => !request()->query('appointment_id'))
                ->url(fn () => route('filament.admin.resources.reviews.create')),
        ];
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Tables\Columns\TextColumn::make('appointment_id')
                //     ->label('Appointment ID')
                //     ->numeric()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('appointment_id')
                    ->label('Appointment Details')
                    ->getStateUsing(fn ($record) => 
                        $record->appointment_id . ' - ' . 
                        $record->appointment->patient->user->name . ' with ' . 
                        $record->appointment->doctor->user->name . ' on ' . 
                        $record->appointment->appointment_date)
                    ->sortable(),
                Tables\Columns\TextColumn::make('review_msg')
                    ->label('Review Message'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // Add filters here if needed
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'view' => Pages\ViewReview::route('/{record}'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }

  
}
