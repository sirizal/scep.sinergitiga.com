<?php

namespace App\Filament\Imports;

use App\Models\Country;
use App\Models\District;
use App\Models\Province;
use App\Models\SubDistrict;
use App\Models\Village;
use App\Models\Warehouse;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class WarehouseImporter extends Importer
{
    protected static ?string $model = Warehouse::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Name')
                ->required(),
            ImportColumn::make('slug')
                ->label('Slug')
                ->nullable(),
            ImportColumn::make('type')
                ->label('Type')
                ->required()
                ->rules(['in:MAIN,TRANSIT,DISTRIBUTION']),
            ImportColumn::make('address')
                ->label('Address')
                ->nullable(),
            ImportColumn::make('country')
                ->label('Country')
                ->nullable()
                ->example('Indonesia'),
            ImportColumn::make('province')
                ->label('Province')
                ->nullable()
                ->example('DKI Jakarta'),
            ImportColumn::make('district')
                ->label('District')
                ->nullable()
                ->example('Jakarta Selatan'),
            ImportColumn::make('sub_district')
                ->label('Sub District')
                ->nullable(),
            ImportColumn::make('village')
                ->label('Village')
                ->nullable(),
            ImportColumn::make('postal_code')
                ->label('Postal Code')
                ->nullable(),
            ImportColumn::make('latitude')
                ->label('Latitude')
                ->nullable()
                ->numeric(),
            ImportColumn::make('longitude')
                ->label('Longitude')
                ->nullable()
                ->numeric(),
            ImportColumn::make('is_active')
                ->label('Is Active')
                ->boolean()
                ->default(true),
        ];
    }

    public function resolveRecord(): Warehouse
    {
        $warehouse = new Warehouse;

        $warehouse->code = Warehouse::generateCode();

        if ($this->data['slug'] ?? false) {
            $warehouse->slug = $this->data['slug'];
        } else {
            $warehouse->slug = Str::slug($this->data['name']);
        }

        $warehouse->country_id = $this->resolveLocationId('country', $this->data['country'] ?? null, Country::class);
        $warehouse->province_id = $this->resolveLocationId('province', $this->data['province'] ?? null, Province::class, $warehouse->country_id);
        $warehouse->district_id = $this->resolveLocationId('district', $this->data['district'] ?? null, District::class, $warehouse->province_id);
        $warehouse->sub_district_id = $this->resolveLocationId('sub_district', $this->data['sub_district'] ?? null, SubDistrict::class, $warehouse->district_id);
        $warehouse->village_id = $this->resolveLocationId('village', $this->data['village'] ?? null, Village::class, $warehouse->sub_district_id);

        return $warehouse;
    }

    protected function resolveLocationId(string $type, ?string $value, string $modelClass, ?int $parentId = null): ?int
    {
        if (empty($value)) {
            return null;
        }

        $query = $modelClass::where('name', $value);

        if ($parentId) {
            $query->where(function ($q) use ($parentId, $type) {
                $column = $type === 'country' ? 'country_id' : "{$type}_id";
                $q->where($column, $parentId);
            });
        }

        $record = $query->first();

        return $record?->id;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your warehouse import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
