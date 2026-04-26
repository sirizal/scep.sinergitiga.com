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
            ->url(fn (Collection $records): string => route('locations.print-labels', [
                'ids' => implode(',', $records->pluck('id')->all()),
            ]))
            ->openUrlInNewTab()
            ->modalHeading('Print Location Labels')
            ->modalDescription(fn (Collection $records): string => 
                "You are about to print labels for {$records->count()} location(s).")
            ->modalSubmitActionLabel('Print Labels');
    }
}