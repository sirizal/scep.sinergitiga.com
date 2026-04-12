<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('pob')
                    ->label('Place of Birth')
                    ->required(),
                DatePicker::make('dob')
                    ->label('Date of Birth')
                    ->required(),
                Select::make('departement_id')
                    ->relationship('departement', 'name')
                    ->required(),
                Select::make('position_id')
                    ->relationship('position', 'name')
                    ->required(),
                TextInput::make('address')
                    ->columnSpanFull(),
                Select::make('country_id')
                    ->relationship('country', 'name')
                    ->required(),
                Select::make('province_id')
                    ->relationship('province', 'name')
                    ->required(),
                Select::make('district_id')
                    ->relationship('district', 'name')
                    ->required(),
                Select::make('sub_district_id')
                    ->relationship('subDistrict', 'name')
                    ->required(),
                Select::make('village_id')
                    ->relationship('village', 'name')
                    ->required(),
                TextInput::make('postal_code'),
                TextInput::make('phone_no'),
                TextInput::make('email'),
                TextInput::make('identity_no'),
                TextInput::make('tax_id'),
                TextInput::make('sallary')
                    ->numeric(),
                Select::make('is_active')
                    ->boolean(),
                TextInput::make('dependants')
                    ->numeric(),
            ]);
    }
}
