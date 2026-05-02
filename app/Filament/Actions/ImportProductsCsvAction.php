<?php

namespace App\Filament\Actions;

use App\Models\Product;
use App\Models\ProductUom;
use App\Models\Uom;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ImportProductsCsvAction extends Action
{
    protected const MAX_ROWS = 50;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Import CSV')
            ->icon('heroicon-o-arrow-up-tray')
            ->schema([
                Section::make()
                    ->schema([
                        FileUpload::make('csv_file')
                            ->label('CSV File')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv'])
                            ->directory('csv-imports')
                            ->required(),
                        Toggle::make('truncate')
                            ->label('Truncate existing products before import')
                            ->default(false),
                    ])
                    ->columns(1),
            ])
            ->action(function (array $data) {
                $csvPath = Storage::disk('local')->path($data['csv_file']);

                if (! file_exists($csvPath)) {
                    Notification::make()
                        ->title('CSV file not found')
                        ->danger()
                        ->send();

                    return;
                }

                if ($data['truncate']) {
                    $this->deleteProductMedia();
                    ProductUom::query()->truncate();
                    Product::query()->truncate();
                }

                $rows = $this->readCsv($csvPath);

                if (empty($rows)) {
                    Notification::make()
                        ->title('No data found in CSV file')
                        ->warning()
                        ->send();

                    return;
                }

                if (count($rows) > self::MAX_ROWS) {
                    Storage::disk('local')->delete($data['csv_file']);

                    Notification::make()
                        ->title('Too many rows')
                        ->body(sprintf('CSV contains %d rows. Maximum allowed is %d.', count($rows), self::MAX_ROWS))
                        ->danger()
                        ->duration(5000)
                        ->send();

                    return;
                }

                $created = 0;
                $skipped = 0;

                foreach ($rows as $row) {
                    $uom = Uom::where('code', $row['uom'] ?? '')->first();

                    if (! $uom) {
                        $skipped++;

                        continue;
                    }

                    $product = Product::create([
                        'name' => $row['name'] ?? '',
                        'description' => $row['description'] ?? '',
                        'variant_code' => $row['variant_code'] ?? '',
                        'uom_id' => $uom->id,
                        'customer_product_code' => $row['customer_product_code'] ?? '',
                        'is_active' => true,
                    ]);

                    $created++;
                }

                Storage::disk('local')->delete($data['csv_file']);

                $notification = Notification::make()
                    ->title('Import completed')
                    ->body(sprintf('%d created, %d skipped.', $created, $skipped))
                    ->success()
                    ->duration(5000);

                if ($skipped > 0) {
                    $notification->warning();
                }

                $notification->send();
            });
    }

    protected function deleteProductMedia(): void
    {
        $products = Product::withTrashed()->get();

        foreach ($products as $product) {
            $product->clearMediaCollection('images');
        }

        Media::query()->truncate();
    }

    protected function readCsv(string $file): array
    {
        $rows = [];
        $header = null;

        if (($handle = fopen($file, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                if (! $header) {
                    $header = array_map('trim', $data);
                    $header = array_map(fn ($h) => ltrim($h, "\xEF\xBB\xBF"), $header);

                    continue;
                }

                $row = array_combine($header, array_map('trim', $data));

                if (! isset($row['name'])) {
                    continue;
                }

                $rows[] = $row;
            }
            fclose($handle);
        }

        return $rows;
    }
}
