<?php

namespace App\Console\Commands;

use App\Models\ProductCategory;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:import-product-categories')]
#[Description('Import product categories from CSV')]
class ImportProductCategories extends Command
{
    public function handle(): int
    {
        $filePath = database_path('seeders/product_categories.csv');

        if (! file_exists($filePath)) {
            $this->error("File not found: {$filePath}");

            return self::FAILURE;
        }

        ProductCategory::truncate();

        $handle = fopen($filePath, 'r');

        $headers = fgetcsv($handle, 0, ';');
        $headers = array_map(function ($h) {
            $h = trim($h);
            if (substr($h, 0, 3) === "\xef\xbb\xbf") {
                $h = substr($h, 3);
            }

            return $h;
        }, $headers);

        $inserted = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $data = array_combine($headers, $row);

            $parentName = trim($data['parent'] ?? '');
            $unspsc = trim($data['unspsc'] ?? '');
            $name = trim($data['name'] ?? '');
            $idName = trim($data['id_name'] ?? '');

            if (empty($unspsc) || empty($name)) {
                $skipped++;

                continue;
            }

            $parentId = ProductCategory::where('name', $parentName)->value('id');

            ProductCategory::create([
                'name' => $name,
                'id_name' => $idName,
                'unspsc' => $unspsc,
                'parent_id' => $parentId,
            ]);

            $inserted++;
        }

        fclose($handle);

        $this->info("Inserted: {$inserted}, Skipped: {$skipped}");

        return self::SUCCESS;
    }
}
