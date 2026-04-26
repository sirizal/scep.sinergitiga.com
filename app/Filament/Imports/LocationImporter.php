<?php

namespace App\Filament\Imports;

use App\Models\Location;
use App\Models\Warehouse;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class LocationImporter extends Importer
{
    protected static ?string $model = Location::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Name')
                ->nullable(),
            ImportColumn::make('type')
                ->label('Type')
                ->nullable(),
            ImportColumn::make('warehouse')
                ->label('Warehouse')
                ->nullable()
                ->example('WH01'),
            ImportColumn::make('zone')
                ->label('Zone')
                ->nullable(),
            ImportColumn::make('aisle')
                ->label('Aisle')
                ->nullable(),
            ImportColumn::make('rack')
                ->label('Rack')
                ->nullable(),
            ImportColumn::make('level')
                ->label('Level')
                ->nullable(),
            ImportColumn::make('bin')
                ->label('Bin')
                ->nullable(),
            ImportColumn::make('max_weight')
                ->label('Max Weight')
                ->nullable()
                ->numeric(),
            ImportColumn::make('max_volume')
                ->label('Max Volume')
                ->nullable()
                ->numeric(),
            ImportColumn::make('is_active')
                ->label('Is Active')
                ->boolean()
                ->default(true),
        ];
    }

    public function resolveRecord(): Location
    {
        $location = new Location;

        $location->zone = $this->data['zone'] ?? null;
        $location->aisle = $this->data['aisle'] ?? null;
        $location->rack = $this->data['rack'] ?? null;
        $location->level = $this->data['level'] ?? null;
        $location->bin = $this->data['bin'] ?? null;
        $location->name = $this->data['name'] ?? null;
        $location->type = $this->data['type'] ?? null;
        $location->max_weight = $this->data['max_weight'] ?? 0;
        $location->max_volume = $this->data['max_volume'] ?? 0;
        $location->is_active = $this->data['is_active'] ?? true;

        if (! empty($this->data['warehouse'])) {
            $warehouse = Warehouse::where('code', $this->data['warehouse'])->first();
            $location->warehouse_id = $warehouse?->id;
        }

        $location->code = Location::generateCode($location);

        return $location;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your location import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
