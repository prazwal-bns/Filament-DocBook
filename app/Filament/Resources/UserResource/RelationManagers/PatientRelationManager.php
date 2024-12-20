<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PatientRelationManager extends RelationManager
{
    protected static string $relationship = 'patient';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('gender')
                ->options([
                    'male' => 'Male',
                    'female' => 'Female'
                ])
                ->default('male')
                ->required(),
                Forms\Components\DatePicker::make('dob'),
            ]);
    }


    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('gender')
            ->columns([
                Tables\Columns\TextColumn::make('gender'),
                Tables\Columns\TextColumn::make('dob'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
