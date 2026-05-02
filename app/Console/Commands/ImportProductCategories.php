<?php

namespace App\Console\Commands;

use App\Models\ProductCategory;
use App\Models\ProductCategoryLevel;
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
        ProductCategoryLevel::truncate();

        $handle = fopen($filePath, 'r');

        $headers = fgetcsv($handle, 0, ';');
        $headers = array_map(function ($h) {
            $h = trim($h);
            if (substr($h, 0, 3) === "\xef\xbb\xbf") {
                $h = substr($h, 3);
            }

            return $h;
        }, $headers);

        rewind($handle);
        fgetcsv($handle, 0, ';');

        $rows = [];
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $rows[] = $row;
        }

        fclose($handle);

        $this->info('Pass 1: Creating categories...');

        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        $inserted = 0;
        $skipped = 0;
        $parentMappings = [];
        $unspscIndex = array_search('unspsc', $headers);
        $parentIndex = array_search('parent', $headers);

        foreach ($rows as $row) {
            $data = array_combine($headers, $row);

            $parentUnspsc = trim($data['parent'] ?? '');
            $unspsc = trim($data['unspsc'] ?? '');
            $name = trim($data['name'] ?? '');
            $idName = trim($data['id_name'] ?? '');

            if (empty($unspsc) || empty($name)) {
                $skipped++;
                $bar->advance();

                continue;
            }

            $category = ProductCategory::create([
                'name' => $name,
                'id_name' => $idName,
                'unspsc' => $unspsc,
            ]);

            $inserted++;

            if (! empty($parentUnspsc)) {
                $parentMappings[$category->id] = $parentUnspsc;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info('Pass 2: Linking parents...');

        if (! empty($parentMappings)) {
            $bar = $this->output->createProgressBar(count($parentMappings));
            $bar->start();

            foreach ($parentMappings as $categoryId => $parentUnspsc) {
                $parent = ProductCategory::where('unspsc', $parentUnspsc)->first();
                if ($parent) {
                    ProductCategory::where('id', $categoryId)->update(['parent_id' => $parent->id]);
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        $this->info("Inserted: {$inserted}, Skipped: {$skipped}");

        $this->info('Pass 3: Building category levels...');

        $rootCategories = ProductCategory::whereNull('parent_id')->with('children')->get();
        $levelEntries = [];

        foreach ($rootCategories as $root) {
            $this->traverseCategory($root, [], $levelEntries);
        }

        $bar = $this->output->createProgressBar(count($levelEntries));
        $bar->start();

        foreach ($levelEntries as $entry) {
            ProductCategoryLevel::create($entry);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Created '.count($levelEntries).' category level entries.');

        return self::SUCCESS;
    }

    private function traverseCategory(ProductCategory $category, array $path, array &$levelEntries): void
    {
        $formatted = $category->name.'-'.$category->id_name;
        $newPath = array_merge($path, [$formatted]);

        $children = $category->children()->get();

        if ($children->isEmpty()) {
            while (count($newPath) < 7) {
                $newPath[] = end($newPath);
            }

            $levelEntries[] = [
                'category_0' => $newPath[0],
                'category_1' => $newPath[1],
                'category_2' => $newPath[2],
                'category_3' => $newPath[3],
                'category_4' => $newPath[4],
                'category_5' => $newPath[5],
                'category_6' => $newPath[6],
                'unspsc' => $category->unspsc,
            ];

            return;
        }

        foreach ($children as $child) {
            $this->traverseCategory($child, $newPath, $levelEntries);
        }
    }
}
