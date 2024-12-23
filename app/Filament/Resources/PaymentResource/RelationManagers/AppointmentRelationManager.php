<?php

namespace App\Filament\Resources\PaymentResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class AppointmentRelationManager extends RelationManager
{
    protected static string $relationship = 'appointment';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('appointment_date')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('appointment_date')
            ->columns([
                TextColumn::make('doctor.user.name')->label('Doctor Name'),
                TextColumn::make('patient.user.name')->label('Patient Name'),
                TextColumn::make('appointment_date'),
                TextColumn::make('day'),
                BadgeColumn::make('status')
                ->label('Status')
                ->colors([
                    'warning' => 'pending',
                    'secondary' => 'confirmed',
                    'success' => 'completed',
                ])
                ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
