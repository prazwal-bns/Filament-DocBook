<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class CustomProfile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.custom-profile';
}
