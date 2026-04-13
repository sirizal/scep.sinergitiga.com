<?php

namespace App\Filament\Resources\Vendors\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VendorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required()
                    ->maxLength(10),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('address')
                    ->columnSpanFull(),
                Select::make('country_id')
                    ->relationship('country', 'name'),
                Select::make('province_id')
                    ->relationship('province', 'name'),
                Select::make('district_id')
                    ->relationship('district', 'name'),
                Select::make('sub_district_id')
                    ->relationship('subDistrict', 'name'),
                Select::make('village_id')
                    ->relationship('village', 'name'),
                TextInput::make('postal_code')
                    ->maxLength(10),
                TextInput::make('phone_no')
                    ->maxLength(20),
                TextInput::make('fax_no')
                    ->maxLength(20),
                TextInput::make('email')
                    ->maxLength(255),
                TextInput::make('website')
                    ->maxLength(255),
                TextInput::make('contact_name')
                    ->maxLength(50),
                Select::make('payment_term_id')
                    ->relationship('paymentTerm', 'name'),
                TextInput::make('credit_limit')
                    ->numeric()
                    ->default(0),
                TextInput::make('tax_id')
                    ->maxLength(50),
                TextInput::make('bussiness_license_id')
                    ->maxLength(50),
            ]);
    }
}
