<?php

namespace App\Filament\Actions;

use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;

class PrintLabelsBulkAction
{
    public static function make(): BulkAction
    {
        return BulkAction::make('printLabels')
            ->label('Print Labels')
            ->icon('heroicon-o-printer')
            ->color('info')
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records) {
                $ids = implode(',', $records->pluck('id')->all());
                
                return redirect()->to(route('locations.print-labels', ['ids' => $ids]));
            })
            ->requiresConfirmation();
    }
}