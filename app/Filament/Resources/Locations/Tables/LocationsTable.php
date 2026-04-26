<?php

namespace App\Filament\Resources\Locations\Tables;

use App\Filament\Actions\PrintLabelAction;
use App\Filament\Actions\PrintLabelsBulkAction;
use App\Filament\Exports\LocationExporter;
use App\Filament\Imports\LocationImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ImportAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class LocationsTable
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
                TrashedFilter::make(),
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
                SelectFilter::make('type')
                    ->label('Type'),
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(LocationImporter::class),
                ExportAction::make()
                    ->exporter(LocationExporter::class),
            ])
            ->recordActions([
                EditAction::make(),
                PrintLabelAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ExportBulkAction::make()
                        ->exporter(LocationExporter::class),
                    PrintLabelsBulkAction::make(),
                ]),
            ]);
    }
}
