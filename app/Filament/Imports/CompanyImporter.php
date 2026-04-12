<?php

namespace App\Filament\Imports;

use App\Models\Company;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class CompanyImporter extends Importer
{
    protected static ?string $model = Company::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Name')
                ->required(),
            ImportColumn::make('company_type_id')
                ->label('Company Type ID')
                ->required()
                ->numeric(),
            ImportColumn::make('address')
                ->label('Address'),
            ImportColumn::make('country_id')
                ->label('Country ID')
                ->required()
                ->numeric(),
            ImportColumn::make('province_id')
                ->label('Province ID')
                ->required()
                ->numeric(),
            ImportColumn::make('district_id')
                ->label('District ID')
                ->required()
                ->numeric(),
            ImportColumn::make('sub_district_id')
                ->label('Sub District ID')
                ->required()
                ->numeric(),
            ImportColumn::make('village_id')
                ->label('Village ID')
                ->required()
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
            ImportColumn::make('tax_id')
                ->label('Tax ID'),
            ImportColumn::make('bussiness_license_id')
                ->label('Business License ID'),
        ];
    }

    public function resolveRecord(): Company
    {
        return new Company;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your company import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
