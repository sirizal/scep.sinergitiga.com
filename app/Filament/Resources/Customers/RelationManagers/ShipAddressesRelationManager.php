<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ImportAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShipAddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'shipAddresses';

    public function table(Table $table): Table
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
                ImportAction::make(),
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
