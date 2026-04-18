<?php

namespace App\Filament\Resources\Journals\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class JournalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('journal_no')
                    ->required(),
                DatePicker::make('date')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('reference'),
                TextInput::make('status')
                    ->required()
                    ->default('draft'),
                TextInput::make('created_by')
                    ->numeric(),
                DateTimePicker::make('posted_at'),
            ]);
    }
}
