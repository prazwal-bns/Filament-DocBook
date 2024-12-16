<?php

namespace App\Filament\Resources\AppointmentResource\Pages;


use App\Filament\Resources\AppointmentResource;
use App\Models\Appointment;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Actions\SelectAction;
use Filament\Forms\Components\Actions as ComponentsActions;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Actions\BulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;

class ListAppointments extends ListRecords
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                // TextColumn::make('id')
                //     ->label('Appointment ID')
                //     ->sortable(),

                TextColumn::make('patient.user.name')
                    ->label('Patient Name')
                    ->searchable(),

                TextColumn::make('doctor.user.name')
                    ->label('Doctor Name')
                    ->searchable(),

                TextColumn::make('appointment_date')
                    ->label('Date')
                    ->date(),


                BadgeColumn::make('payment.payment_status')
                    ->colors([
                        'success' => 'paid',
                        'danger' => 'unpaid',
                    ]),
                

                TextColumn::make('start_time')
                    ->label('Start Time'),

                TextColumn::make('end_time')
                    ->label('End Time'),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'secondary' => 'confirmed',
                        'success' => 'completed',
                    ])
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->successNotification(function ($record) {
                        return Notification::make()
                            ->success()
                            ->icon('heroicon-o-trash')
                            ->title('Appointment Removed!')
                            ->body("The appointment with Dr. {$record->doctor->user->name} for {$record->patient->user->name} on {$record->appointment_date} has been removed.");
                    }),
                
                ActionGroup::make([
                    Action::make('review')
                        ->label('Leave a Review')
                        ->color('teal')
                        ->icon('heroicon-o-pencil-square')
                        ->visible(fn ($record) => $record->status === 'completed' && !$record->review)
                        ->url(fn ($record) => route('filament.admin.resources.reviews.create', ['appointment_id' => $record->id])),

                        
                    Action::make('view_review')
                        ->label('View Review')
                        ->color('indigo')
                        ->icon('heroicon-o-eye')
                        ->visible(fn ($record) => $record->review)  
                        ->url(fn ($record) => route('filament.admin.resources.reviews.view', ['record' => $record->review])), 

                    Action::make('updateStatus')
                        ->label('Update Status')
                        ->color('primary')
                        ->icon('heroicon-o-pencil')
                        ->form([
                            Select::make('status')
                                ->label('Select Status')
                                ->options([
                                    'pending' => 'Pending',
                                    'confirmed' => 'Confirmed',
                                    'completed' => 'Completed',
                                ])
                                ->default('pending')
                                ->required(),
                        ])
                        ->action(function ($record, $data) {
                            // Update the status for the specific record
                            $record->update([
                                'status' => $data['status'],
                            ]);
        
                            Notification::make()
                                ->title('Status Updated')
                                ->success()
                                ->send();
                        }),
                ])
                    ->label('More actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size(ActionSize::Small)
                    ->color('secondary')
                    ->button(),
                
    
            ])
            ->bulkActions([
    
                // Bulk Action for updating the status of selected records
                BulkAction::make('updateStatusBulk')
                    ->label('Update Status for Selected')
                    ->form([
                        Select::make('status')
                            ->label('Select Status')
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'completed' => 'Completed',
                            ])
                            ->required(),
                    ])
                    ->action(function ($records, $data) {
                        // Update the status for selected records
                        foreach ($records as $record) {
                            $record->update([
                                'status' => $data['status'],
                            ]);
                        }
    
                        Notification::make()
                            ->title('Status Updated')
                            ->success()
                            ->send();
                    }),
    
            ]);
    }


  
    public function getTabs(): array
    {
        $today = Carbon::today();
    
        return [
            'All' => Tab::make(),
            'Today' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->whereDate('appointment_date', '=', $today)
                )
                ->badge(
                    Appointment::whereDate('appointment_date', '=', $today)->count()
                ),
            'This Week' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->whereDate('appointment_date', '>=', $today->startOfWeek())
                )
                ->badge(
                    Appointment::whereDate('appointment_date', '>=', $today->startOfWeek())->count()
                ),
            'This Month' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->whereDate('appointment_date', '>=', $today->startOfMonth())
                )
                ->badge(
                    Appointment::whereDate('appointment_date', '>=', $today->startOfMonth())->count()
                ),
            'This Year' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->whereDate('appointment_date', '>=', $today->startOfYear())
                )
                ->badge(
                    Appointment::whereDate('appointment_date', '>=', $today->startOfYear())->count()
                ),
        ];
    }
    


}
