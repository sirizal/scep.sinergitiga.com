<?php

namespace App\Services;

use App\Models\ProductCategory;
use App\Models\ProductCategoryLevel;

class ProductCategoryFinder
{
    private const MIN_SCORE = 1;

    private const STOP_WORDS = [
        'a', 'an', 'the', 'for', 'of', 'and', 'or', 'in', 'on', 'at',
        'to', 'with', 'by', 'from', 'up', 'down', 'out', 'over', 'under',
        'is', 'are', 'was', 'were', 'be', 'been', 'being',
        'has', 'have', 'had', 'do', 'does', 'did', 'will', 'would',
        'could', 'should', 'may', 'might', 'must', 'as',
        'kg', 'pack', 'box', 'piece', 'pc', 'set', 'unit', 'liter', 'litre', 'ml', 'gram', 'g', 'meter',
        'large', 'small', 'medium', 'mini', 'mega', 'ultra', 'super',
        'premium', 'standard', 'basic', 'pro', 'plus',
        'new', 'old', 'best', 'top', 'free', 'organic', 'natural',
        'fresh', 'hot', 'cold', 'warm', 'ready', 'used', 'sale',
        'dan', 'atau', 'di', 'ke', 'dari', 'untuk', 'dengan', 'pada', 'oleh',
        'ini', 'itu', 'yang', 'bisa', 'dapat', 'satu', 'dua', 'tiga',
        'hitam', 'putih', 'merah', 'biru', 'kuning', 'hijau', 'coklat', 'abu',
        'x', 'cm', 'mm', 'm', 'inch',
    ];

    private const KEYWORD_PATTERN = '/^[a-z]+$/';

    protected static ?array $cachedLevels = null;

    protected static ?array $keywordFrequencies = null;

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

        $keywordWeights = $this->computeKeywordWeights($keywords);
        $levels = $this->getCachedLevels();
        $scores = [];

        foreach ($levels as $level) {
            if (empty($level['unspsc'])) {
                continue;
            }

            $unspsc = $level['unspsc'];
            $totalScore = 0;
            $matchedKeywordCount = 0;

            foreach ($keywords as $index => $keyword) {
                $keywordScore = $this->scoreSingleKeyword($keyword, $level['category_6']);
                if ($keywordScore > 0) {
                    $positionWeight = count($keywords) - $index;
                    $rarityWeight = $keywordWeights[$keyword] ?? 1;
                    $totalScore += $keywordScore * $positionWeight * $rarityWeight;
                    $matchedKeywordCount++;
                }
            }

            if ($matchedKeywordCount > 0) {
                $scores[$unspsc] = [
                    'level' => $level,
                    'score' => $totalScore,
                    'matchedCount' => $matchedKeywordCount,
                ];
            }
        }

        if (empty($scores)) {
            return null;
        }

        $topMatchedCount = max(array_map(fn ($s) => $s['matchedCount'], $scores));

        $topCandidates = array_filter($scores, fn ($s) => $s['matchedCount'] === $topMatchedCount);

        uasort($topCandidates, fn ($a, $b) => $b['score'] <=> $a['score']);

        $best = reset($topCandidates);

        if ($best['score'] >= self::MIN_SCORE) {
            $category = ProductCategory::where('unspsc', $best['level']['unspsc'])->first();

            return $category?->id;
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

    private function computeKeywordWeights(array $keywords): array
    {
        $frequencies = $this->getKeywordFrequencies();
        $weights = [];
        $maxFreq = max(array_values($frequencies)) ?: 1;

        foreach ($keywords as $keyword) {
            $freq = $frequencies[$keyword] ?? 0;
            $weights[$keyword] = max(1, (int) log($maxFreq / ($freq + 1) + 1) + 1);
        }

        return $weights;
    }

    private function getKeywordFrequencies(): array
    {
        if (self::$keywordFrequencies !== null) {
            return self::$keywordFrequencies;
        }

        $levels = $this->getCachedLevels();
        $frequencies = [];

        foreach ($levels as $level) {
            $text = strtolower(trim(str_replace("\xEF\xBB\xBF", '', $level['category_6'] ?? '')));
            $words = array_unique(array_filter(
                preg_split('/[\s,\-]+/', $text, -1, PREG_SPLIT_NO_EMPTY),
                fn ($w) => strlen($w) >= 2 && ! in_array($w, self::STOP_WORDS)
            ));

            foreach ($words as $word) {
                $frequencies[$word] = ($frequencies[$word] ?? 0) + 1;
            }
        }

        self::$keywordFrequencies = $frequencies;

        return self::$keywordFrequencies;
    }

    private function extractKeywords(string $name): array
    {
        $name = strtolower($name);
        $name = preg_replace('/[^a-z0-9\s]/', ' ', $name);
        $words = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY);

        $keywords = array_filter($words, fn ($word) => strlen($word) > 1);
        $keywords = array_diff($keywords, self::STOP_WORDS);
        $keywords = array_filter($keywords, fn ($word) => preg_match(self::KEYWORD_PATTERN, $word));

        return array_values($keywords);
    }

    private function scoreSingleKeyword(string $keyword, ?string $text): int
    {
        if (! $text) {
            return 0;
        }

        $text = strtolower($text);
        $textWords = array_filter(preg_split('/[\s,\-]+/', $text, -1, PREG_SPLIT_NO_EMPTY), fn ($w) => strlen($w) >= 2);
        $textWords = array_diff($textWords, self::STOP_WORDS);

        foreach ($textWords as $textWord) {
            if ($keyword === $textWord) {
                return 5;
            }
        }

        foreach ($textWords as $textWord) {
            if (str_starts_with($textWord, $keyword) || str_starts_with($keyword, $textWord)) {
                return 3;
            }
        }

        foreach ($textWords as $textWord) {
            if (str_contains($textWord, $keyword) || str_contains($keyword, $textWord)) {
                return 1;
            }
        }

        return 0;
    }
}
