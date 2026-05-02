<?php

namespace App\Filament\Imports;

use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('description')
                ->label('Description'),
            ImportColumn::make('variant_code')
                ->label('Variant Code'),
            ImportColumn::make('uom')
                ->label('UOM')
                ->relationship(resolveUsing: 'code')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('customer_product_code')
                ->label('Customer Product Code'),
            ImportColumn::make('is_active')
                ->label('Is Active')
                ->boolean(),
        ];
    }

    public function resolveRecord(): Product
    {
        return new Product;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your product import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
