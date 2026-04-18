<?php

namespace App\Filament\Resources\Accounts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Select::make('parent_id')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload(true),
                Select::make('account_type_id')
                    ->relationship('accountType', 'name')
                    ->searchable()
                    ->preload(true),
                TextInput::make('normal_balance')
                    ->required(),
                Toggle::make('is_postable')
                    ->required(),
            ]);
    }
}
