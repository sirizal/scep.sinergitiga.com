<?php

namespace App\Filament\Imports;

use App\Models\Province;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class ProvinceImporter extends Importer
{
    protected static ?string $model = Province::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('country')
                ->relationship(resolveUsing: 'name')
                ->label('Country'),
            ImportColumn::make('name')
                ->label('Name'),
        ];
    }

    public function resolveRecord(): Province
    {
        return new Province;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your province import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
