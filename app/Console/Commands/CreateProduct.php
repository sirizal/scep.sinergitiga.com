<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Uom;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Symfony\Component\DomCrawler\Crawler;

#[Signature('app:create-product {--truncate : Truncate products table before importing}')]
#[Description('Create product from product_masters.csv for first time')]
class CreateProduct extends Command
{
    protected string $csvPath = 'database/seeders/product_masters.csv';

    protected string $imageDisk = 'public';

    protected string $imagePath = 'products';

    protected int $imageDownloadTimeout = 10;

    public function handle(): int
    {
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
        $imageUrl = $this->searchImageOnWeb($product->name);

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

    protected function searchImageOnWeb(string $productName): ?string
    {
        $searchQuery = urlencode($productName.' product');
        $searchUrl = "https://www.google.com/search?q={$searchQuery}&tbm=isch";

        try {
            $response = Http::timeout($this->imageDownloadTimeout)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.5',
                ])
                ->get($searchUrl);

            if (! $response->successful()) {
                return null;
            }

            return $this->extractImageUrl($response->body());
        } catch (\Exception $e) {
            $this->warn(sprintf('Image search failed for %s: %s', $productName, $e->getMessage()));

            return null;
        }
    }

    protected function extractImageUrl(string $html): ?string
    {
        try {
            $crawler = new Crawler($html);

            $image = $crawler->filter('img')->first();

            if ($image->count() > 0) {
                $src = $image->attr('src') ?? $image->attr('data-src');

                if ($src && str_starts_with($src, 'http')) {
                    return $src;
                }
            }

            if (preg_match('/"ou":"(https?:\/\/[^"]+\.(?:jpg|jpeg|png|webp|gif))"/i', $html, $matches)) {
                return $matches[1];
            }

            if (preg_match('/https?:\/\/[^"\'<>\s]+\.(?:jpg|jpeg|png|webp|gif)/i', $html, $matches)) {
                return $matches[0];
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    protected function downloadAndAttachImage(Product $product, string $imageUrl): void
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'product_img_').'.webp';

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

        if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
            unlink($tempPath);

            return;
        }

        if ($mimeType !== 'image/webp') {
            $this->convertToWebp($tempPath);
        }

        $product->addMedia($tempPath)
            ->toMediaCollection('images');

        unlink($tempPath);
    }

    protected function convertToWebp(string $imagePath): void
    {
        if (! extension_loaded('gd')) {
            return;
        }

        $mimeType = mime_content_type($imagePath);
        $webpPath = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $imagePath);

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
            rename($webpPath, $imagePath);
        }
    }

    protected function findLocalImageByName(string $name): ?string
    {
        if (! Storage::disk($this->imageDisk)->exists($this->imagePath)) {
            return null;
        }

        $files = Storage::disk($this->imageDisk)->files($this->imagePath);

        $searchTerms = $this->extractSearchTerms($name);

        foreach ($files as $file) {
            if (! str_ends_with(strtolower($file), '.webp')) {
                continue;
            }

            $fileName = basename($file, '.webp');

            foreach ($searchTerms as $term) {
                if (stripos($fileName, $term) !== false) {
                    return basename($file);
                }
            }
        }

        return null;
    }

    protected function extractSearchTerms(string $name): array
    {
        $terms = [];

        $terms[] = strtolower($name);

        $words = explode(' ', strtolower($name));
        foreach ($words as $word) {
            if (strlen($word) > 3) {
                $terms[] = $word;
            }
        }

        return array_unique($terms);
    }
}
