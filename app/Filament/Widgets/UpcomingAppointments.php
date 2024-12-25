<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingAppointments extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';


    public function table(Table $table): Table
    {
        // Get the current date
        $today = now()->toDateString();

        return $table
            ->query(
                Appointment::query()
                    ->where('appointment_date', '>=', $today)
                    ->orderBy('appointment_date', 'asc')
                    ->with(['doctor', 'patient'])
            )
            ->columns([
                // Display Appointment ID
                // TextColumn::make('id')
                //     ->label('Appointment ID')
                //     ->sortable(),

                // Display the Doctor's Name
                TextColumn::make('doctor.user.name')
                    ->label('Doctor')
                    ->sortable(),

                // Display the Patient's Name
                TextColumn::make('patient.user.name')
                    ->label('Patient')
                    ->sortable(),

                // Display Appointment Date
                TextColumn::make('appointment_date')
                    ->label('Appointment Date')
                    ->sortable(),

                // Display Start Time
                TextColumn::make('start_time')
                    ->label('Start Time')
                    ->sortable(),

                // Display End Time
                TextColumn::make('end_time')
                    ->label('End Time')
                    ->sortable(),

                // Display Appointment Status
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'secondary' => 'confirmed',
                        'success' => 'completed',
                    ])
                    ->sortable(),

            ])->searchable();
            // ->pagination(10); // Optional pagination, adjust as necessary
    }
}