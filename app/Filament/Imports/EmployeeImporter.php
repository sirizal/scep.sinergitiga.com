<?php

namespace App\Filament\Imports;

use App\Models\Employee;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class EmployeeImporter extends Importer
{
    protected static ?string $model = Employee::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('code')
                ->label('Code')
                ->required(),
            ImportColumn::make('name')
                ->label('Name')
                ->required(),
            ImportColumn::make('pob')
                ->label('Place of Birth')
                ->required(),
            ImportColumn::make('dob')
                ->label('Date of Birth')
                ->required(),
            ImportColumn::make('departement_id')
                ->label('Departement ID')
                ->required()
                ->numeric(),
            ImportColumn::make('position_id')
                ->label('Position ID')
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
            ImportColumn::make('email')
                ->label('Email'),
            ImportColumn::make('identity_no')
                ->label('Identity No'),
            ImportColumn::make('tax_id')
                ->label('Tax ID'),
            ImportColumn::make('sallary')
                ->label('Sallary')
                ->required()
                ->numeric(),
            ImportColumn::make('is_active')
                ->label('Is Active')
                ->boolean(),
            ImportColumn::make('dependants')
                ->label('Dependants')
                ->numeric(),
        ];
    }

    public function resolveRecord(): Employee
    {
        return new Employee;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your employee import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
