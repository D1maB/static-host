<?php

namespace App\Filament\Resources\Sites\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('custom_domain')
                    ->label('Custom domain')
                    ->placeholder('example.com')
                    ->unique(ignoreRecord: true),
                Toggle::make('is_active')
                    ->required(),
                DateTimePicker::make('published_at'),
            ]);
    }
}
