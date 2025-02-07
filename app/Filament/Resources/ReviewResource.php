<?php

namespace App\Filament\Resources;

use Illuminate\Support\Str;
use App\Filament\Resources\ReviewResource\Pages;
use App\Filament\Resources\ReviewResource\RelationManagers;
use App\Models\Appointment;
use App\Models\Review;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';

    protected static ?string $navigationLabel = 'Reviews';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup = 'Manage Appointments';

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();

        if ($user->role === 'patient') {
            return static::getModel()::whereHas('appointment', function ($query) use ($user) {
                $query->where('patient_id', $user->patient->id);
            })->count();
        } elseif ($user->role === 'doctor') {
            return static::getModel()::whereHas('appointment', function ($query) use ($user) {
                $query->where('doctor_id', $user->doctor->id);
            })->count();
        }

        return static::getModel()::count();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = Auth::user();

        if ($user->role === 'admin') {
            return $query;
        }

        if ($user->role === 'patient') {
            return $query->whereHas('appointment', function ($appointmentQuery) use ($user) {
                $appointmentQuery->where('patient_id', $user->patient->id);
            });
        }

        if ($user->role === 'doctor') {
            return $query->whereHas('appointment', function ($appointmentQuery) use ($user) {
                $appointmentQuery->where('doctor_id', $user->doctor->id);
            });
        }

        return $query->where('id', null);
    }



    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'lime';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Hidden::make('appointment_id')
                    ->default(fn () => request()->query('appointment_id'))
                    ->disabled(fn ($livewire) =>
                        (Auth::user()->role === 'patient' && $livewire instanceof \Filament\Resources\Pages\EditRecord) ||
                        in_array(Auth::user()->role, ['doctor', 'admin'])
                    )
                    ->required(),

                // Forms\Components\Select::make('appointment_id')
                //     ->label('Select Completed Appointment')
                //     ->options(
                //         Appointment::query()
                //             ->where('status', 'completed')
                //             ->with(['patient', 'doctor'])
                //             ->get()
                //             ->mapWithKeys(function ($appointment) {
                //                 return [
                //                     $appointment->id => "{$appointment->patient->user->name} with Dr. {$appointment->doctor->user->name} on {$appointment->appointment_date}",
                //                 ];
                //             })
                //     )
                //     ->searchable()
                //     ->placeholder('Select a completed appointment')
                //     ->visible(fn () => !request()->query('appointment_id')) // Only show if no `appointment_id` in the query string
                //     ->required(fn () => !request()->query('appointment_id')),

                // Forms\Components\Select::make('appointment_id')
                //     ->label('Select Completed Appointment')
                //     ->options(
                //         Appointment::query()
                //             ->where('status', 'completed')
                //             ->when(Auth::user()->role === 'doctor', function ($query) {
                //                 $query->where('doctor_id', Auth::user()->doctor->id);
                //             })
                //             ->with(['patient', 'doctor'])
                //             ->get()
                //             ->mapWithKeys(function ($appointment) {
                //                 return [
                //                     $appointment->id => "{$appointment->patient->user->name} with {$appointment->doctor->user->name} on {$appointment->appointment_date}",
                //                 ];
                //             })
                //     )
                //     ->searchable()
                //     ->placeholder('Select a completed appointment')
                //     ->visible(fn () => !request()->query('appointment_id'))
                //     ->disabled(fn ($livewire) =>
                //         ($livewire instanceof \Filament\Resources\Pages\EditRecord && in_array(Auth::user()->role, ['doctor', 'admin'])) ||
                //         (Auth::user()->role === 'patient' && $livewire instanceof \Filament\Resources\Pages\EditRecord)
                //     )
                //     ->required(fn () => !request()->query('appointment_id')),

                Forms\Components\Select::make('appointment_id')
                    ->label('Select Completed Appointment')
                    ->options(
                        Appointment::query()
                            ->where('status', 'completed')
                            ->when(Auth::user()->role === 'doctor', function ($query) {
                                $query->where('doctor_id', Auth::user()->doctor->id);
                            })
                            ->doesntHave('review') // Exclude appointments with reviews
                            ->with(['patient', 'doctor'])
                            ->get()
                            ->mapWithKeys(function ($appointment) {
                                return [
                                    $appointment->id => "{$appointment->patient->user->name} with {$appointment->doctor->user->name} on {$appointment->appointment_date}",
                                ];
                            })
                    )
                    ->searchable()
                    ->placeholder('Select a completed appointment')
                    ->visible(fn () => !request()->query('appointment_id'))
                    ->disabled(fn ($livewire) =>
                        ($livewire instanceof \Filament\Resources\Pages\EditRecord && in_array(Auth::user()->role, ['doctor', 'admin'])) ||
                        (Auth::user()->role === 'patient' && $livewire instanceof \Filament\Resources\Pages\EditRecord)
                    )
                    ->required(fn () => !request()->query('appointment_id')),



                Forms\Components\Textarea::make('review_msg')
                    ->label('Review Message')
                    ->required()
                    ->columnSpanFull(),

                FileUpload::make('review_pdf')
                    ->label('Review PDF')
                    ->directory('reviews')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(2048)
                    ->nullable()
                    // ->getUploadedFileNameForStorageUsing(function ($file, callable $get) {
                    //     $patientName = $get('appointment_id')->patient->name;
                    //     $timestamp = now()->format('Y_m_d_His');
                    //     return "{$patientName}_review_{$timestamp}.{$file->getClientOriginalExtension()}";
                    // }),
                    ->getUploadedFileNameForStorageUsing(function ($file, callable $get) {
                        $appointmentId = $get('appointment_id');
                        $appointment = \App\Models\Appointment::with('patient')->find($appointmentId);

                        if ($appointment && $appointment->patient) {
                            $patientName = Str::slug($appointment->patient->user->name, '_');
                            $timestamp = now()->format('Y_m_d_His');
                            return "{$patientName}_review_{$timestamp}.{$file->getClientOriginalExtension()}";
                        }

                        $timestamp = now()->format('Y_m_d_His');
                        return "review_{$timestamp}.{$file->getClientOriginalExtension()}";
                    }),



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

                Tables\Columns\TextColumn::make('appointment_id')
                    ->label('Appointment Details')
                    ->getStateUsing(fn ($record) =>
                        // $record->appointment_id . ' - ' .
                        $record->appointment->patient->user->name . ' with ' .
                        $record->appointment->doctor->user->name . ' on ' .
                        $record->appointment->appointment_date)
                    ->sortable()
                    ->limit(30),


                Tables\Columns\TextColumn::make('appointment.doctor.user.name')
                    ->label('Doctor Name'),

                Tables\Columns\TextColumn::make('appointment.patient.user.name')
                    ->label('Patient Name'),

                Tables\Columns\TextColumn::make('appointment.appointment_date')
                    ->label('Appointment Date'),

                Tables\Columns\TextColumn::make('review_pdf')
                    ->label('Review PDF')
                    ->url(fn ($record) => $record->review_pdf ? asset('storage/' . $record->review_pdf) : null, true)
                    ->default('No PDF Uploaded')
                    ->limit(30)
                    ->openUrlInNewTab()
                    ->extraAttributes(['style' => 'white-space: normal; word-wrap: break-word;'])
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('review_msg')
                    ->label('Review Message')
                    ->limit(10),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->defaultPaginationPageOption(5)
            ->filters([
                // Add filters here if needed
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
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

    public static function canCreate(): bool
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'doctor') {
            return true;
        }

        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            // Section::make('')
            //     ->schema([
            //         // TextEntry::make('appointment_id')->label('Appointment Details'),
            //         TextEntry::make('review_msg')->label('Review Message'),
            //     ])
            //     ->columns(2),
        ]);
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'doctor' && $record->appointment->doctor_id === $user->doctor->id) {
            return true;
        }

        return false;
    }

}
