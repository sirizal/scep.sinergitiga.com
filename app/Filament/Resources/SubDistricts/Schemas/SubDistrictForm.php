<?php

namespace App\Filament\Resources\SubDistricts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubDistrictForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('district_id')
                    ->relationship('district', 'name')
                    ->searchable()
                    ->required(),
                TextInput::make('name')
                    ->required(),
            ]);
    }
}
