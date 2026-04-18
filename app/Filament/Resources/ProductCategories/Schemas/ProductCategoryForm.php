<?php

namespace App\Filament\Resources\ProductCategories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('parent_id')
                    ->label('Parent Category')
                    ->relationship('parent', 'name')
                    ->nullable()
                    ->searchable()
                    ->placeholder('Select parent category'),
                TextInput::make('name')
                    ->required()
                    ->label('Name'),
                TextInput::make('unspsc')
                    ->label('UNSPSC'),
            ]);
    }
}
