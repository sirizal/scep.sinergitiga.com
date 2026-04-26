<?php

namespace App\Filament\Resources\ProductCategories\Tables;

use App\Filament\Exports\ProductCategoryExporter;
use App\Filament\Imports\ProductCategoryImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ImportAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('parent.name')
                    ->label('Parent Category')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('id_name')
                    ->label('ID Name')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('unspsc')
                    ->label('UNSPSC')
                    ->searchable()
                    ->wrap(),
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
                    ->importer(ProductCategoryImporter::class),
                ExportAction::make()
                    ->exporter(ProductCategoryExporter::class),
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
                        ->exporter(ProductCategoryExporter::class),
                ]),
            ]);
    }
}
