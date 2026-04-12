<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('company_type_id')
                    ->relationship('companyType', 'name')
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
                TextInput::make('fax_no'),
                TextInput::make('email'),
                TextInput::make('website'),
                TextInput::make('contact_name'),
                TextInput::make('tax_id'),
                TextInput::make('bussiness_license_id'),
            ]);
    }
}
