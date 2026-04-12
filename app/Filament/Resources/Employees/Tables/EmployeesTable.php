<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Filament\Exports\EmployeeExporter;
use App\Filament\Imports\EmployeeImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ImportAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('pob')
                    ->label('Place of Birth'),
                TextColumn::make('dob')
                    ->date(),
                TextColumn::make('departement.name')
                    ->sortable(),
                TextColumn::make('position.name')
                    ->sortable(),
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
                TextColumn::make('phone_no'),
                TextColumn::make('email'),
                TextColumn::make('identity_no'),
                TextColumn::make('tax_id'),
                TextColumn::make('sallary')
                    ->numeric(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('dependants'),
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
                    ->importer(EmployeeImporter::class),
                ExportAction::make()
                    ->exporter(EmployeeExporter::class),
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
                        ->exporter(EmployeeExporter::class),
                ]),
            ]);
    }
}
