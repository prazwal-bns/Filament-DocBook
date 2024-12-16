<?php

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Resources\ReviewResource;
use App\Models\Review;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Filament\Resources\Pages\CreateRecord;

class CreateReview extends CreateRecord
{
    protected static string $resource = ReviewResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Check if a review already exists for the selected appointment
        $existingReview = Review::where('appointment_id', $data['appointment_id'])->first();

        if ($existingReview) {
            // Send error notification
            Notification::make()
                ->title('Error')
                ->body('A review already exists for this appointment.')
                ->danger()
                ->send();

            // Throw a validation exception to stop the form submission
            throw ValidationException::withMessages([
                'appointment_id' => 'A review already exists for this appointment.',
            ]);
        }

        return $data;
    }
}
