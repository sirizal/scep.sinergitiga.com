<?php

namespace App\Filament\Resources\Vendors\Tables;

use App\Filament\Exports\VendorExporter;
use App\Filament\Imports\VendorImporter;
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

class VendorsTable
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
                TextColumn::make('phone_no'),
                TextColumn::make('fax_no'),
                TextColumn::make('email'),
                TextColumn::make('website'),
                TextColumn::make('contact_name'),
                TextColumn::make('paymentTerm.name')
                    ->sortable(),
                TextColumn::make('credit_limit')
                    ->sortable(),
                TextColumn::make('tax_id'),
                TextColumn::make('bussiness_license_id'),
                IconColumn::make('is_active')
                    ->boolean(),
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
                    ->importer(VendorImporter::class),
                ExportAction::make()
                    ->exporter(VendorExporter::class),
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
                        ->exporter(VendorExporter::class),
                ]),
            ]);
    }
}
