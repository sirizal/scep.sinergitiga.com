<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Exports\ProductExporter;
use App\Filament\Imports\ProductImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ImportAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('images')
                    ->label('Image')
                    ->collection('images')
                    ->square()
                    ->limit(1),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->wrapHeader()
                    ->wrap(),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->wrapHeader()
                    ->wrap(),
                TextColumn::make('variant_code')
                    ->label('Variant Code')
                    ->searchable()
                    ->wrapHeader()
                    ->wrap(),
                TextColumn::make('productCategory.name')
                    ->label('Category')
                    ->searchable()
                    ->wrapHeader()
                    ->wrap(),
                TextColumn::make('uom.name')
                    ->label('UOM')
                    ->searchable()
                    ->wrapHeader()
                    ->wrap(),
                TextColumn::make('customer_product_code')
                    ->label('Customer Product Code')
                    ->searchable()
                    ->wrapHeader()
                    ->wrap(),
                ToggleColumn::make('is_active')
                    ->label('Active'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(ProductImporter::class),
                ExportAction::make()
                    ->exporter(ProductExporter::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ExportBulkAction::make()
                        ->exporter(ProductExporter::class),
                ]),
            ]);
    }
}
