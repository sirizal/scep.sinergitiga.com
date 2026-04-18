<?php

namespace App\Filament\Resources\Villages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VillageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('sub_district_id')
                    ->relationship('subDistrict', 'name')
                    ->searchable()
                    ->preload(true)
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('postal_code')
                    ->required(),
            ]);
    }
}
