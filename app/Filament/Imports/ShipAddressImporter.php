<?php

namespace App\Filament\Imports;

use App\Models\ShipAddress;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class ShipAddressImporter extends Importer
{
    protected static ?string $model = ShipAddress::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('customer_id')
                ->label('Customer ID')
                ->requiredMapping()
                ->rules(['required', 'exists:customers,id']),
            ImportColumn::make('name')
                ->label('Name')
                ->requiredMapping()
                ->rules(['required', 'max:100']),
            ImportColumn::make('address')
                ->label('Address'),
            ImportColumn::make('country_id')
                ->label('Country ID')
                ->numeric(),
            ImportColumn::make('province_id')
                ->label('Province ID')
                ->numeric(),
            ImportColumn::make('district_id')
                ->label('District ID')
                ->numeric(),
            ImportColumn::make('sub_district_id')
                ->label('Sub District ID')
                ->numeric(),
            ImportColumn::make('village_id')
                ->label('Village ID')
                ->numeric(),
            ImportColumn::make('postal_code')
                ->label('Postal Code'),
            ImportColumn::make('contact_name')
                ->label('Contact Name'),
            ImportColumn::make('phone_no')
                ->label('Phone No'),
            ImportColumn::make('email')
                ->label('Email'),
        ];
    }

    public function resolveRecord(): ShipAddress
    {
        return new ShipAddress;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your shipping address import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
