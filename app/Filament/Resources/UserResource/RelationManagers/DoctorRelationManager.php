<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\Specialization;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DoctorRelationManager extends RelationManager
{
    protected static string $relationship = 'doctor';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->options([
                        'available' => 'Available',
                        'not_available' => 'Unavailable',
                    ])
                    ->required(),
                Forms\Components\Select::make('specialization_id')
                    ->label('Specialization')
                    ->options(Specialization::pluck('name', 'id')),
                Forms\Components\Textarea::make('bio')
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status')
            ->columns([
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('specialization.name'),
                Tables\Columns\TextColumn::make('bio'),
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
