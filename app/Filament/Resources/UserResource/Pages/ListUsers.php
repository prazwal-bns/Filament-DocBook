<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Appointment;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $user = Auth::user();

        return [
            'All' => Tab::make()
            ->icon('heroicon-o-ellipsis-horizontal-circle')
            ->badge(
                User::count() 
            ),
            
            'Doctors' => Tab::make()
                ->icon('heroicon-o-clock')
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->where('role', 'doctor') 
                )
                ->badge(
                    User::where('role', 'doctor')
                        ->count() 
                ),
            
            'Patients' => Tab::make()
                ->icon('heroicon-o-clock')
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->where('role', 'patient') 
                )
                ->badge(
                    User::where('role', 'patient')
                        ->count() 
                )
                ->extraAttributes(['class' => 'flex justify-end']),
        ];
    }
}
