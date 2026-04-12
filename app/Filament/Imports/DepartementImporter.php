<?php

namespace App\Filament\Imports;

use App\Models\Departement;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class DepartementImporter extends Importer
{
    protected static ?string $model = Departement::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Name')
                ->required(),
            ImportColumn::make('company_id')
                ->label('Company ID')
                ->required()
                ->numeric(),
        ];
    }

    public function resolveRecord(): Departement
    {
        return new Departement;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your departement import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
