<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('sku')
                ->label('SKU'),
            ExportColumn::make('slug')
                ->label('Slug'),
            ExportColumn::make('name')
                ->label('Name'),
            ExportColumn::make('description')
                ->label('Description'),
            ExportColumn::make('variant_code')
                ->label('Variant Code'),
            ExportColumn::make('product_category_id')
                ->label('Product Category ID'),
            ExportColumn::make('uom_id')
                ->label('UOM ID'),
            ExportColumn::make('customer_product_code')
                ->label('Customer Product Code'),
            ExportColumn::make('is_active')
                ->label('Is Active'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your product export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
