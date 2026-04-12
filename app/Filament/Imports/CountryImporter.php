<?php

namespace App\Filament\Imports;

use App\Models\Country;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class CountryImporter extends Importer
{
    protected static ?string $model = Country::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('code')
                ->label('Code'),
            ImportColumn::make('name')
                ->label('Name'),
        ];
    }

    public function resolveRecord(): Country
    {
        return new Country;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your country import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
