<?php

namespace App\Filament\Resources\Vendors\Schemas;

use App\Models\District;
use App\Models\Province;
use App\Models\SubDistrict;
use App\Models\Village;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class VendorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->readOnly()
                    ->visible(fn (?string $operation): bool => $operation === 'edit'),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('address')
                    ->columnSpanFull(),
                Select::make('country_id')
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload(true)
                    ->live(),
                Select::make('province_id')
                    ->options(fn (Get $get): array => $get('country_id')
                        ? Province::where('country_id', $get('country_id'))->pluck('name', 'id')->toArray()
                        : [])
                    ->live(),
                Select::make('district_id')
                    ->options(fn (Get $get): array => $get('province_id')
                        ? District::where('province_id', $get('province_id'))->pluck('name', 'id')->toArray()
                        : []),
                Select::make('sub_district_id')
                    ->options(fn (Get $get): array => $get('district_id')
                        ? SubDistrict::where('district_id', $get('district_id'))->pluck('name', 'id')->toArray()
                        : []),
                Select::make('village_id')
                    ->options(fn (Get $get): array => $get('sub_district_id')
                        ? Village::where('sub_district_id', $get('sub_district_id'))->pluck('name', 'id')->toArray()
                        : [])
                    ->afterStateUpdated(fn (TextInput $component, Get $get) => $component
                        ->getContainer()
                        ->getComponent('postalCodeField')
                        ->fill($get('village_id') ? Village::find($get('village_id'))?->postal_code : null)),
                TextInput::make('postal_code')
                    ->maxLength(10)
                    ->key('postalCodeField'),
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
                    ->relationship('paymentTerm', 'name')
                    ->searchable()
                    ->preload(true),
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
