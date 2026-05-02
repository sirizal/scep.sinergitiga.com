<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Uom;
use App\Services\ImageFinder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

#[Signature('app:create-product {--truncate : Truncate products table before importing}')]
#[Description('Create product from product_masters.csv for first time')]
class CreateProduct extends Command
{
    protected string $csvPath = 'database/seeders/product_masters.csv';

    protected int $imageDownloadTimeout = 10;

    protected ImageFinder $imageFinder;

    public function handle(): int
    {
        $this->imageFinder = new ImageFinder;

        if (! file_exists(base_path($this->csvPath))) {
            $this->error("CSV file not found at: {$this->csvPath}");

            return self::FAILURE;
        }

        if ($this->option('truncate')) {
            $this->warn('Truncating products table...');
            Product::query()->truncate();
        }

        $this->info('Reading product_masters.csv...');

        $products = $this->readCsv();

        if (empty($products)) {
            $this->warn('No data found in CSV file.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Found %d products to import.', count($products)));

        $bar = $this->output->createProgressBar(count($products));
        $bar->start();

        $created = 0;
        $skipped = 0;

        foreach ($products as $row) {
            $uom = Uom::where('code', $row['uom'])->first();

            if (! $uom) {
                $this->newLine();
                $this->warn(sprintf('UOM code "%s" not found for product: %s', $row['uom'], $row['name']));
                $skipped++;
                $bar->advance();

                continue;
            }

            $product = Product::create([
                'name' => $row['name'],
                'description' => $row['description'],
                'variant_code' => $row['variant_code'],
                'uom_id' => $uom->id,
                'customer_product_code' => $row['customer_product_code'],
                'is_active' => true,
            ]);

            $this->attachImage($product);

            $created++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info(sprintf('Import completed: %d created, %d skipped.', $created, $skipped));

        return self::SUCCESS;
    }

    protected function readCsv(): array
    {
        $file = base_path($this->csvPath);
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

    protected function attachImage(Product $product): void
    {
        $imageUrl = $this->imageFinder->searchImage($product->name);

        if (! $imageUrl) {
            $this->warn(sprintf('No image found for: %s', $product->name));

            return;
        }

        try {
            $this->downloadAndAttachImage($product, $imageUrl);
        } catch (FileDoesNotExist|FileIsTooBig $e) {
            $this->warn(sprintf('Failed to attach image for %s: %s', $product->name, $e->getMessage()));
        }
    }

    protected function downloadAndAttachImage(Product $product, string $imageUrl): void
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'product_img_');

        $response = Http::timeout($this->imageDownloadTimeout)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            ])
            ->sink($tempPath)
            ->get($imageUrl);

        if (! $response->successful() || ! file_exists($tempPath)) {
            return;
        }

        $mimeType = mime_content_type($tempPath);
        $extensionMap = [
            'image/jpeg' => '.jpg',
            'image/png' => '.png',
            'image/gif' => '.gif',
            'image/webp' => '.webp',
        ];

        if (! isset($extensionMap[$mimeType])) {
            unlink($tempPath);

            return;
        }

        $newPath = $tempPath.$extensionMap[$mimeType];
        rename($tempPath, $newPath);
        $tempPath = $newPath;

        if ($mimeType !== 'image/webp') {
            $webpPath = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $tempPath);
            $this->convertToWebp($tempPath, $webpPath);
            $tempPath = $webpPath;
        }

        $product->addMedia($tempPath)
            ->toMediaCollection('images');

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }

    protected function convertToWebp(string $imagePath, string $webpPath): void
    {
        if (! extension_loaded('gd')) {
            return;
        }

        $mimeType = mime_content_type($imagePath);

        if ($mimeType === 'image/jpeg') {
            $source = imagecreatefromjpeg($imagePath);
        } elseif ($mimeType === 'image/png') {
            $source = imagecreatefrompng($imagePath);
        } elseif ($mimeType === 'image/gif') {
            $source = imagecreatefromgif($imagePath);
        } else {
            return;
        }

        if ($source) {
            imagewebp($source, $webpPath, 80);
            imagedestroy($source);
            unlink($imagePath);
        }
    }
}
