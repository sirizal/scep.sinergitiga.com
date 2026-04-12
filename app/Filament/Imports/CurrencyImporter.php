<?php

namespace App\Filament\Imports;

use App\Models\Currency;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class CurrencyImporter extends Importer
{
    protected static ?string $model = Currency::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('code')
                ->label('Code'),
            ImportColumn::make('name')
                ->label('Name'),
            ImportColumn::make('symbol')
                ->label('Symbol'),
        ];
    }

    public function resolveRecord(): Currency
    {
        return new Currency;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your currency import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
