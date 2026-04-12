<?php

namespace App\Filament\Exports;

use App\Models\Village;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class VillageExporter extends Exporter
{
    protected static ?string $model = Village::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('subDistrict.name')
                ->label('Sub District'),
            ExportColumn::make('name')
                ->label('Name'),
            ExportColumn::make('postal_code')
                ->label('Postal Code'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your village export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
