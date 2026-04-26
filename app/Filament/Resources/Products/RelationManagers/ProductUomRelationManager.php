<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Models\ProductUom;
use App\Models\Uom;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProductUomRelationManager extends RelationManager
{
    protected static string $relationship = 'productUoms';

    protected static bool $shouldSkipAuthorization = true;

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('uom_id')
                    ->label('UOM')
                    ->options(function () {
                        $ownerRecord = $this->getOwnerRecord();
                        $usedUomIds = $ownerRecord->productUoms()->pluck('uom_id')->toArray();

                        return Uom::query()
                            ->whereNotIn('id', $usedUomIds)
                            ->orderBy('name')
                            ->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->placeholder('Select UOM'),
                Select::make('convert_uom_id')
                    ->label('Convert To UOM')
                    ->options(function () {
                        $ownerRecord = $this->getOwnerRecord();

                        return Uom::query()
                            ->where('id', $ownerRecord->uom_id)
                            ->orderBy('name')
                            ->pluck('name', 'id');
                    })
                    ->default($this->getOwnerRecord()?->uom_id)
                    ->required(),
                TextInput::make('conversion_qty')
                    ->label('Conversion Qty')
                    ->numeric()
                    ->default(1)
                    ->required(),
            ]);
    }

    protected function mutateFormDataForCreate(array $data): array
    {
        $ownerRecord = $this->getOwnerRecord();
        $data['product_id'] = $ownerRecord->id;

        return $data;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('uom.name')
            ->columns([
                TextColumn::make('uom.name')
                    ->label('UOM')
                    ->searchable(),
                TextColumn::make('convertUom.name')
                    ->label('Convert To UOM')
                    ->searchable(),
                TextColumn::make('conversion_qty')
                    ->label('Conversion Qty')
                    ->numeric(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->modal()
                    ->label('Add')
                    ->using(function (array $data): Model {
                        $ownerRecord = $this->getOwnerRecord();
                        $data['product_id'] = $ownerRecord->id;

                        return $ownerRecord->productUoms()->create($data);
                    }),
            ])
            ->recordActions([
                /* EditAction::make()
                    ->modal()
                    ->hidden(fn (ProductUom $record) => $record->id === $record->product->productUoms()->oldest()->first()?->id), */
                DeleteAction::make()
                    ->hidden(fn (ProductUom $record) => $record->id === $record->product->productUoms()->oldest()->first()?->id),
            ])
            ->modifyQueryUsing(fn ($query) => $query->orderBy('id'));
    }
}
