<?php

namespace App\Filament\Imports;

use App\Models\District;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class DistrictImporter extends Importer
{
    protected static ?string $model = District::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('province')
                ->relationship(resolveUsing: 'name')
                ->label('Province'),
            ImportColumn::make('name')
                ->label('Name'),
        ];
    }

    public function resolveRecord(): District
    {
        return new District;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your district import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
