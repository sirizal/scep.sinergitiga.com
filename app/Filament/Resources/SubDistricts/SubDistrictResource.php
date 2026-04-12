<?php

namespace App\Filament\Resources\SubDistricts;

use App\Filament\Resources\SubDistricts\Pages\CreateSubDistrict;
use App\Filament\Resources\SubDistricts\Pages\EditSubDistrict;
use App\Filament\Resources\SubDistricts\Pages\ListSubDistricts;
use App\Filament\Resources\SubDistricts\Schemas\SubDistrictForm;
use App\Filament\Resources\SubDistricts\Tables\SubDistrictsTable;
use App\Models\SubDistrict;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class SubDistrictResource extends Resource
{
    protected static ?string $model = SubDistrict::class;

    protected static string|UnitEnum|null $navigationGroup = 'Geographical Data';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::MapPin;

    public static function form(Schema $schema): Schema
    {
        return SubDistrictForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubDistrictsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubDistricts::route('/'),
            'create' => CreateSubDistrict::route('/create'),
            'edit' => EditSubDistrict::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
