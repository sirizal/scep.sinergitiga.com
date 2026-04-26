<?php

namespace App\Filament\Resources\Locations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->readOnly()
                    ->visible(fn (?string $operation): bool => $operation === 'edit')
                    ->helperText('Auto-generated on create'),
                Select::make('warehouse_id')
                    ->relationship('warehouse', 'code')
                    ->searchable()
                    ->preload()
                    ->live(),
                TextInput::make('zone')
                    ->maxLength(50),
                TextInput::make('aisle')
                    ->maxLength(50),
                TextInput::make('rack')
                    ->maxLength(50),
                TextInput::make('level')
                    ->maxLength(50),
                TextInput::make('bin')
                    ->maxLength(50),
                TextInput::make('name')
                    ->maxLength(255),
                TextInput::make('type')
                    ->maxLength(50),
                TextInput::make('max_weight')
                    ->numeric()
                    ->step(0.01)
                    ->default(0),
                TextInput::make('max_volume')
                    ->numeric()
                    ->step(0.01)
                    ->default(0),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
