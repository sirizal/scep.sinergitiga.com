<?php

namespace App\Filament\Exports;

use App\Models\Customer;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class CustomerExporter extends Exporter
{
    protected static ?string $model = Customer::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('code')
                ->label('Code'),
            ExportColumn::make('name')
                ->label('Name'),
            ExportColumn::make('address')
                ->label('Address'),
            ExportColumn::make('country.name')
                ->label('Country'),
            ExportColumn::make('province.name')
                ->label('Province'),
            ExportColumn::make('district.name')
                ->label('District'),
            ExportColumn::make('subDistrict.name')
                ->label('Sub District'),
            ExportColumn::make('village.name')
                ->label('Village'),
            ExportColumn::make('postal_code')
                ->label('Postal Code'),
            ExportColumn::make('phone_no')
                ->label('Phone No'),
            ExportColumn::make('fax_no')
                ->label('Fax No'),
            ExportColumn::make('email')
                ->label('Email'),
            ExportColumn::make('website')
                ->label('Website'),
            ExportColumn::make('contact_name')
                ->label('Contact Name'),
            ExportColumn::make('paymentTerm.name')
                ->label('Payment Term'),
            ExportColumn::make('credit_limit')
                ->label('Credit Limit'),
            ExportColumn::make('tax_id')
                ->label('Tax ID'),
            ExportColumn::make('bussiness_license_id')
                ->label('Business License ID'),
            ExportColumn::make('is_active')
                ->label('Is Active'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your customer export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
