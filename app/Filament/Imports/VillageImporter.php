<?php

namespace App\Filament\Imports;

use App\Models\Village;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class VillageImporter extends Importer
{
    protected static ?string $model = Village::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('subDistrict')
                ->relationship(resolveUsing: 'name')
                ->label('Sub District'),
            ImportColumn::make('name')
                ->label('Name'),
            ImportColumn::make('postal_code')
                ->label('Postal Code'),
        ];
    }

    public function resolveRecord(): Village
    {
        return new Village;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your village import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
