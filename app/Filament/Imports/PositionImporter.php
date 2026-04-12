<?php

namespace App\Filament\Imports;

use App\Models\Position;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class PositionImporter extends Importer
{
    protected static ?string $model = Position::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Name')
                ->required(),
        ];
    }

    public function resolveRecord(): Position
    {
        return new Position;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your position import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
