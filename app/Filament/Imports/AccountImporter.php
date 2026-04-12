<?php

namespace App\Filament\Imports;

use App\Models\Account;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class AccountImporter extends Importer
{
    protected static ?string $model = Account::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('code')
                ->label('Code'),
            ImportColumn::make('name')
                ->label('Name'),
            ImportColumn::make('parent')
                ->relationship(resolveUsing: 'code')
                ->label('Parent Code'),
            ImportColumn::make('account_type')
                ->relationship(resolveUsing: 'name')
                ->label('Account Type'),
            ImportColumn::make('normal_balance')
                ->label('Normal Balance'),
            ImportColumn::make('is_postable')
                ->label('Is Postable'),
        ];
    }

    public function resolveRecord(): Account
    {
        return new Account;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your account import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
