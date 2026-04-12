<?php

namespace App\Filament\Resources\Companies\Tables;

use App\Filament\Exports\CompanyExporter;
use App\Filament\Imports\CompanyImporter;
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

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('companyType.name')
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
                TextColumn::make('fax_no'),
                TextColumn::make('email'),
                TextColumn::make('website'),
                TextColumn::make('contact_name'),
                TextColumn::make('tax_id'),
                TextColumn::make('bussiness_license_id'),
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
                    ->importer(CompanyImporter::class),
                ExportAction::make()
                    ->exporter(CompanyExporter::class),
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
                        ->exporter(CompanyExporter::class),
                ]),
            ]);
    }
}
