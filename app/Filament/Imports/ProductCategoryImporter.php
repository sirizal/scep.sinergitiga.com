<?php

namespace App\Filament\Imports;

use App\Models\ProductCategory;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class ProductCategoryImporter extends Importer
{
    protected static ?string $model = ProductCategory::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('parent_id')
                ->label('Parent ID'),
            ImportColumn::make('name')
                ->label('Name'),
            ImportColumn::make('unspsc')
                ->label('UNSPSC'),
        ];
    }

    public function resolveRecord(): ProductCategory
    {
        return new ProductCategory;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your product category import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
