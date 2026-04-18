<?php

namespace App\Filament\Exports;

use App\Models\ProductCategory;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class ProductCategoryExporter extends Exporter
{
    protected static ?string $model = ProductCategory::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('parent_id')
                ->label('Parent ID'),
            ExportColumn::make('name')
                ->label('Name'),
            ExportColumn::make('unspsc')
                ->label('UNSPSC'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your product category export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
