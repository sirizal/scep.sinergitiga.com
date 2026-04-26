<?php

namespace App\Filament\Actions;

use App\Models\Location;
use Filament\Actions\Action;

class PrintLabelAction
{
    public static function make(): Action
    {
        return Action::make('printLabel')
            ->label('Print Label')
            ->icon('heroicon-o-printer')
            ->color('info')
            ->url(fn (Location $record): string => route('locations.print-label', $record))
            ->openUrlInNewTab();
    }
}
