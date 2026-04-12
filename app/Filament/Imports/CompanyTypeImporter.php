<?php

namespace App\Filament\Imports;

use App\Models\CompanyType;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class CompanyTypeImporter extends Importer
{
    protected static ?string $model = CompanyType::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Name'),
        ];
    }

    public function resolveRecord(): CompanyType
    {
        return new CompanyType;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your company type import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
