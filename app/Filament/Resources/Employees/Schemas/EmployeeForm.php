<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Models\District;
use App\Models\Province;
use App\Models\SubDistrict;
use App\Models\Village;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
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
                    ->searchable()
                    ->preload(true)
                    ->required(),
                Select::make('position_id')
                    ->relationship('position', 'name')
                    ->searchable()
                    ->preload(true)
                    ->required(),
                TextInput::make('address')
                    ->columnSpanFull(),
                Select::make('country_id')
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload(true)
                    ->required()
                    ->live(),
                Select::make('province_id')
                    ->options(fn (Get $get): array => $get('country_id')
                        ? Province::where('country_id', $get('country_id'))->pluck('name', 'id')->toArray()
                        : [])
                    ->required()
                    ->live(),
                Select::make('district_id')
                    ->options(fn (Get $get): array => $get('province_id')
                        ? District::where('province_id', $get('province_id'))->pluck('name', 'id')->toArray()
                        : [])
                    ->required(),
                Select::make('sub_district_id')
                    ->options(fn (Get $get): array => $get('district_id')
                        ? SubDistrict::where('district_id', $get('district_id'))->pluck('name', 'id')->toArray()
                        : [])
                    ->required(),
                Select::make('village_id')
                    ->options(fn (Get $get): array => $get('sub_district_id')
                        ? Village::where('sub_district_id', $get('sub_district_id'))->pluck('name', 'id')->toArray()
                        : [])
                    ->required()
                    ->afterStateUpdated(fn (TextInput $component, Get $get) => $component
                        ->getContainer()
                        ->getComponent('postalCodeField')
                        ->fill($get('village_id') ? Village::find($get('village_id'))?->postal_code : null)),
                TextInput::make('postal_code')
                    ->key('postalCodeField'),
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
