<?php

namespace App\Filament\Resources\Warehouses\Schemas;

use App\Models\District;
use App\Models\Province;
use App\Models\SubDistrict;
use App\Models\Village;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class WarehouseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->readOnly()
                    ->visible(fn (?string $operation): bool => $operation === 'edit')
                    ->helperText('Auto-generated on create'),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (TextInput $component, Get $get, ?string $operation) => $operation !== 'edit'
                        ? $component
                            ->getContainer()
                            ->getComponent('slugField')
                            ?->state(fn () => Str::slug($get('name')))
                        : null),
                TextInput::make('slug')
                    ->readOnly()
                    ->visible(fn (?string $operation): bool => $operation === 'edit')
                    ->helperText('Auto-generated from name on create'),
                TextInput::make('type')
                    ->required()
                    ->maxLength(50)
                    ->placeholder('MAIN, TRANSIT, DISTRIBUTION'),
                Textarea::make('address')
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
                    ->live()
                    ->searchable(),
                Select::make('district_id')
                    ->options(fn (Get $get): array => $get('province_id')
                        ? District::where('province_id', $get('province_id'))->pluck('name', 'id')->toArray()
                        : [])
                    ->live()
                    ->searchable(),
                Select::make('sub_district_id')
                    ->options(fn (Get $get): array => $get('district_id')
                        ? SubDistrict::where('district_id', $get('district_id'))->pluck('name', 'id')->toArray()
                        : [])
                    ->live()
                    ->searchable(),
                Select::make('village_id')
                    ->options(fn (Get $get): array => $get('sub_district_id')
                        ? Village::where('sub_district_id', $get('sub_district_id'))->pluck('name', 'id')->toArray()
                        : [])
                    ->live()
                    ->searchable()
                    ->afterStateUpdated(fn (Select $component, Get $get) => $component
                        ->getContainer()
                        ->getComponent('postalCodeField')
                        ?->state($get('village_id') ? Village::find($get('village_id'))?->postal_code : null)),
                TextInput::make('postal_code')
                    ->maxLength(10)
                    ->key('postalCodeField'),
                TextInput::make('latitude')
                    ->numeric()
                    ->step(0.0000001)
                    ->placeholder('-6.2000000')
                    ->formatStateUsing(fn ($state): ?string => $state !== null ? sprintf('%.7f', (float) $state) : null),
                TextInput::make('longitude')
                    ->numeric()
                    ->step(0.0000001)
                    ->placeholder('106.8160000')
                    ->formatStateUsing(fn ($state): ?string => $state !== null ? sprintf('%.7f', (float) $state) : null),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
