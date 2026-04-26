<?php

namespace App\Filament\Resources\Warehouses\Tables;

use App\Filament\Exports\WarehouseExporter;
use App\Filament\Imports\WarehouseImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ImportAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WarehousesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'MAIN' => 'primary',
                        'TRANSIT' => 'warning',
                        'DISTRIBUTION' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('is_active')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->label('Status')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Not Active')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'MAIN' => 'Main',
                        'TRANSIT' => 'Transit',
                        'DISTRIBUTION' => 'Distribution',
                    ]),
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(WarehouseImporter::class),
                ExportAction::make()
                    ->exporter(WarehouseExporter::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->exporter(WarehouseExporter::class),
                ]),
            ]);
    }
}
