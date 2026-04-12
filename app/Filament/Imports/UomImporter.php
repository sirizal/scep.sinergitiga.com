<?php

namespace App\Filament\Imports;

use App\Models\Uom;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class UomImporter extends Importer
{
    protected static ?string $model = Uom::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('code')
                ->label('Code'),
            ImportColumn::make('name')
                ->label('Name'),
        ];
    }

    public function resolveRecord(): Uom
    {
        return new Uom;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your uom import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
