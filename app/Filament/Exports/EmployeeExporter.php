<?php

namespace App\Filament\Exports;

use App\Models\Employee;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class EmployeeExporter extends Exporter
{
    protected static ?string $model = Employee::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('code')
                ->label('Code'),
            ExportColumn::make('name')
                ->label('Name'),
            ExportColumn::make('pob')
                ->label('Place of Birth'),
            ExportColumn::make('dob')
                ->label('Date of Birth'),
            ExportColumn::make('departement_id')
                ->label('Departement ID'),
            ExportColumn::make('position_id')
                ->label('Position ID'),
            ExportColumn::make('address')
                ->label('Address'),
            ExportColumn::make('country_id')
                ->label('Country ID'),
            ExportColumn::make('province_id')
                ->label('Province ID'),
            ExportColumn::make('district_id')
                ->label('District ID'),
            ExportColumn::make('sub_district_id')
                ->label('Sub District ID'),
            ExportColumn::make('village_id')
                ->label('Village ID'),
            ExportColumn::make('postal_code')
                ->label('Postal Code'),
            ExportColumn::make('phone_no')
                ->label('Phone No'),
            ExportColumn::make('email')
                ->label('Email'),
            ExportColumn::make('identity_no')
                ->label('Identity No'),
            ExportColumn::make('tax_id')
                ->label('Tax ID'),
            ExportColumn::make('sallary')
                ->label('Sallary'),
            ExportColumn::make('is_active')
                ->label('Is Active'),
            ExportColumn::make('dependants')
                ->label('Dependants'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your employee export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
