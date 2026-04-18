<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sku')
                    ->label('SKU')
                    ->readonly()
                    ->visible(fn (string $operation): bool => $operation === 'edit'),
                TextInput::make('name')
                    ->required()
                    ->label('Name'),
                Textarea::make('description')
                    ->label('Description')
                    ->rows(3),
                SpatieMediaLibraryFileUpload::make('images')
                    ->label('Images')
                    ->collection('images')
                    ->image()
                    ->multiple()
                    ->maxFiles(10)
                    ->reorderable(),
                TextInput::make('variant_code')
                    ->label('Variant Code'),
                Select::make('product_category_id')
                    ->label('Product Category')
                    ->relationship('productCategory', 'name')
                    ->nullable()
                    ->searchable()
                    ->preload()
                    ->placeholder('Select category'),
                Select::make('uom_id')
                    ->label('UOM')
                    ->relationship('uom', 'name')
                    ->required()
                    ->searchable()
                    ->placeholder('Select UOM'),
                TextInput::make('customer_product_code')
                    ->label('Customer Product Code'),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }
}
