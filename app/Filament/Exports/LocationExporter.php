<?php

namespace App\Filament\Exports;

use App\Models\Location;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class LocationExporter extends Exporter
{
    protected static ?string $model = Location::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('code')
                ->label('Code'),
            ExportColumn::make('name')
                ->label('Name'),
            ExportColumn::make('type')
                ->label('Type'),
            ExportColumn::make('warehouse.code')
                ->label('Warehouse'),
            ExportColumn::make('zone')
                ->label('Zone'),
            ExportColumn::make('aisle')
                ->label('Aisle'),
            ExportColumn::make('rack')
                ->label('Rack'),
            ExportColumn::make('level')
                ->label('Level'),
            ExportColumn::make('bin')
                ->label('Bin'),
            ExportColumn::make('max_weight')
                ->label('Max Weight'),
            ExportColumn::make('max_volume')
                ->label('Max Volume'),
            ExportColumn::make('is_active')
                ->label('Is Active'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your location export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
