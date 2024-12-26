<?php

namespace App\Filament\Resources\ScheduleResource\Pages;

use App\Filament\Resources\ScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Schedule;
use Filament\Tables;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Components\Tab;

class ListSchedules extends ListRecords
{
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }


    // public function getTabs(): array
    // {
    //     $user = Auth::user();

    //     if ($user->role !== 'admin') {
    //         return [];
    //     }

    //     $doctors = Doctor::with('user')->get();

    //     $tabs = [];

    //     foreach ($doctors as $doctor) {
    //         $doctorName = $doctor->user->name;

    //         $tabs["{$doctorName}"] = Tab::make()
    //             ->icon('heroicon-o-user')
    //             ->label("{$doctorName}")
    //             ->modifyQueryUsing(fn (Builder $query) =>
    //                 $query->where('doctor_id', $doctor->id)
    //             )
    //             ->badge(
    //                 Schedule::where('doctor_id', $doctor->id)
    //                     ->count()
    //             );
    //     }

    //     return $tabs;
    // }




}
