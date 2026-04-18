<?php

namespace App\Filament\Resources\ShipAddresses\Tables;

use App\Filament\Imports\ShipAddressImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ImportAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShipAddressesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('address')
                    ->limit(50),
                TextColumn::make('country.name')
                    ->sortable(),
                TextColumn::make('province.name')
                    ->sortable(),
                TextColumn::make('district.name')
                    ->sortable(),
                TextColumn::make('subDistrict.name')
                    ->sortable(),
                TextColumn::make('village.name')
                    ->sortable(),
                TextColumn::make('postal_code'),
                TextColumn::make('contact_name'),
                TextColumn::make('phone_no'),
                TextColumn::make('email'),
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(ShipAddressImporter::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
