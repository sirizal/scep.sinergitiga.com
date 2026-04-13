<?php

namespace App\Filament\Imports;

use App\Models\Vendor;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class VendorImporter extends Importer
{
    protected static ?string $model = Vendor::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('code')
                ->label('Code')
                ->requiredMapping()
                ->rules(['required', 'max:10']),
            ImportColumn::make('name')
                ->label('Name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
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
            ImportColumn::make('phone_no')
                ->label('Phone No'),
            ImportColumn::make('fax_no')
                ->label('Fax No'),
            ImportColumn::make('email')
                ->label('Email'),
            ImportColumn::make('website')
                ->label('Website'),
            ImportColumn::make('contact_name')
                ->label('Contact Name'),
            ImportColumn::make('payment_term_id')
                ->label('Payment Term ID')
                ->numeric(),
            ImportColumn::make('credit_limit')
                ->label('Credit Limit')
                ->numeric(),
            ImportColumn::make('tax_id')
                ->label('Tax ID'),
            ImportColumn::make('bussiness_license_id')
                ->label('Business License ID'),
            ImportColumn::make('is_active')
                ->label('Is Active')
                ->boolean(),
        ];
    }

    public function resolveRecord(): Vendor
    {
        return new Vendor;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your vendor import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
