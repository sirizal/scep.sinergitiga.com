<?php

namespace App\Services;

use App\Models\ProductCategory;
use App\Models\ProductCategoryLevel;

class ProductCategoryFinder
{
    private const MIN_SCORE = 30;

    private const STOP_WORDS = [
        'a', 'an', 'the', 'for', 'of', 'and', 'or', 'in', 'on', 'at',
        'to', 'with', 'by', 'from', 'up', 'down', 'out', 'over', 'under',
        'is', 'are', 'was', 'were', 'be', 'been', 'being',
        'has', 'have', 'had', 'do', 'does', 'did', 'will', 'would',
        'could', 'should', 'may', 'might', 'must',
        'kg', 'pack', 'box', 'piece', 'pc', 'set', 'unit', 'liter', 'litre', 'ml', 'gram', 'g', 'meter',
        'large', 'small', 'medium', 'mini', 'mega', 'ultra', 'super',
        'premium', 'standard', 'basic', 'pro', 'plus',
        'new', 'old', 'best', 'top', 'free', 'organic', 'natural',
        'fresh', 'hot', 'cold', 'warm', 'ready', 'used', 'sale',
        'dan', 'atau', 'di', 'ke', 'dari', 'untuk', 'dengan', 'pada', 'oleh',
        'ini', 'itu', 'yang', 'bisa', 'dapat', 'satu', 'dua', 'tiga',
    ];

    protected static ?array $cachedLevels = null;

    protected TranslationService $translator;

    public function __construct()
    {
        $this->translator = new TranslationService;
    }

    public function findForProduct(string $productName): ?int
    {
        if (empty(trim($productName))) {
            return null;
        }

        $englishName = $this->translator->translate($productName);

        $keywords = $this->extractKeywords($englishName);

        if (empty($keywords)) {
            return null;
        }

        $levels = $this->getCachedLevels();

        foreach ($keywords as $keyword) {
            $bestMatch = null;
            $bestScore = 0;

            foreach ($levels as $level) {
                $score = $this->scoreSingleKeyword($keyword, $level['category_6']);

                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = $level;
                }
            }

            if ($bestMatch && $bestScore >= self::MIN_SCORE && $bestMatch['unspsc']) {
                $category = ProductCategory::where('unspsc', $bestMatch['unspsc'])->first();

                return $category?->id;
            }
        }

        return null;
    }

    private function getCachedLevels(): array
    {
        if (self::$cachedLevels === null) {
            self::$cachedLevels = ProductCategoryLevel::select('category_6', 'unspsc')
                ->get()
                ->map(fn ($l) => [
                    'category_6' => $l->category_6,
                    'unspsc' => $l->unspsc,
                ])
                ->toArray();
        }

        return self::$cachedLevels;
    }

    private function extractKeywords(string $name): array
    {
        $name = strtolower($name);
        $name = preg_replace('/[^a-z0-9\s]/', ' ', $name);
        $words = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY);

        $keywords = array_filter($words, fn ($word) => strlen($word) > 1);
        $keywords = array_diff($keywords, self::STOP_WORDS);

        return array_values($keywords);
    }

    private function scoreSingleKeyword(string $keyword, ?string $text): int
    {
        if (! $text) {
            return 0;
        }

        $text = strtolower($text);
        $textWords = array_filter(preg_split('/[\s,\-]+/', $text, -1, PREG_SPLIT_NO_EMPTY), fn ($w) => strlen($w) >= 2);

        foreach ($textWords as $textWord) {
            if ($keyword === $textWord) {
                return 200;
            }
        }

        foreach ($textWords as $textWord) {
            if (str_starts_with($textWord, $keyword) || str_starts_with($keyword, $textWord)) {
                return 100;
            }
        }

        foreach ($textWords as $textWord) {
            if (str_contains($textWord, $keyword) || str_contains($keyword, $textWord)) {
                return 50;
            }
        }

        return 0;
    }
}
