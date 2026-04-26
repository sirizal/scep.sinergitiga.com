<?php

namespace App\Services;

use App\Models\ProductCategory;
use Illuminate\Support\Collection;

class ProductCategoryFinder
{
    private const TOP_CATEGORY_LIMIT = 3;

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
        // Indonesian
        'dan', 'atau', 'di', 'ke', 'dari', 'untuk', 'dengan', 'pada', 'oleh',
        'ini', 'itu', 'yang', 'bisa', 'dapat', 'satu', 'dua', 'tiga',
    ];

    private const ROOT_CATEGORY_IDS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

    public function findForProduct(string $productName): ?int
    {
        if (empty(trim($productName))) {
            return null;
        }

        $keywords = $this->extractKeywords($productName);

        if (empty($keywords)) {
            return null;
        }

        $topCategories = $this->getRootCategories();
        $matchedTopCategories = $this->matchTopCategories($keywords, $topCategories);

        if ($matchedTopCategories->isEmpty()) {
            return $this->fallbackSearchAll($keywords);
        }

        $categoriesToScore = $this->getCategoriesFromTopMatches($matchedTopCategories);

        if ($categoriesToScore->isEmpty()) {
            return null;
        }

        $bestMatch = $this->findBestMatch($keywords, $categoriesToScore);

        if ($bestMatch && $bestMatch['score'] >= self::MIN_SCORE) {
            return $bestMatch['category']->id;
        }

        return $this->fallbackSearchAll($keywords);
    }

    private function fallbackSearchAll(array $keywords): ?int
    {
        $allCategories = ProductCategory::all();

        $bestMatch = null;
        $bestScore = 0;
        $bestDepth = 0;

        foreach ($allCategories as $category) {
            $score = $this->scoreCategory($keywords, $category);
            $depth = $this->getCategoryDepth($category);

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestDepth = $depth;
                $bestMatch = [
                    'category' => $category,
                    'score' => $score,
                ];
            } elseif ($score === $bestScore && $depth > $bestDepth) {
                $bestDepth = $depth;
                $bestMatch = [
                    'category' => $category,
                    'score' => $score,
                ];
            }
        }

        if ($bestMatch && $bestMatch['score'] >= self::MIN_SCORE) {
            return $bestMatch['category']->id;
        }

        return null;
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

    private function getRootCategories(): Collection
    {
        return ProductCategory::whereIn('id', self::ROOT_CATEGORY_IDS)
            ->get();
    }

    private function matchTopCategories(array $keywords, Collection $topCategories): Collection
    {
        $scores = [];

        foreach ($topCategories as $category) {
            $score = $this->calculateKeywordScore($keywords, $category->name, $category->id_name);
            if ($score > 0) {
                $scores[$category->id] = [
                    'category' => $category,
                    'score' => $score,
                ];
            }
        }

        uasort($scores, fn ($a, $b) => $b['score'] <=> $a['score']);

        $matched = array_slice($scores, 0, self::TOP_CATEGORY_LIMIT, true);

        if (empty($matched)) {
            return collect();
        }

        return collect($matched)->pluck('category');
    }

    private function calculateKeywordScore(array $keywords, string $name, ?string $idName = null): int
    {
        $score = 0;

        $name = strtolower($name);
        $nameWords = preg_split('/[\s,]+/', $name, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($keywords as $keyword) {
            foreach ($nameWords as $nameWord) {
                if ($keyword === $nameWord) {
                    $score += 100;

                    continue;
                }

                if (str_starts_with($nameWord, $keyword) || str_starts_with($keyword, $nameWord)) {
                    $score += 60;
                } elseif (str_contains($nameWord, $keyword) || str_contains($keyword, $nameWord)) {
                    $score += 30;
                }
            }
        }

        if ($idName) {
            $idName = strtolower($idName);
            $idNameWords = preg_split('/[\s,]+/', $idName, -1, PREG_SPLIT_NO_EMPTY);

            foreach ($keywords as $keyword) {
                foreach ($idNameWords as $idNameWord) {
                    if ($keyword === $idNameWord) {
                        $score += 100;

                        continue;
                    }

                    if (str_starts_with($idNameWord, $keyword) || str_starts_with($keyword, $idNameWord)) {
                        $score += 60;
                    } elseif (str_contains($idNameWord, $keyword) || str_contains($keyword, $idNameWord)) {
                        $score += 30;
                    }
                }
            }
        }

        return $score;
    }

    private function getCategoriesFromTopMatches(Collection $matchedTopCategories): Collection
    {
        $categoryIds = [];

        foreach ($matchedTopCategories as $topCategory) {
            $descendants = $this->getAllDescendantIds($topCategory);
            $categoryIds = array_merge($categoryIds, $descendants);
        }

        if (empty($categoryIds)) {
            return collect();
        }

        return ProductCategory::whereIn('id', $categoryIds)
            ->get();
    }

    private function getAllDescendantIds(ProductCategory $category): array
    {
        $ids = [$category->id];

        $children = ProductCategory::where('parent_id', $category->id)->get();

        foreach ($children as $child) {
            $ids = array_merge($ids, $this->getAllDescendantIds($child));
        }

        return $ids;
    }

    private function findBestMatch(array $keywords, Collection $categories): ?array
    {
        $bestMatch = null;
        $bestScore = 0;
        $bestDepth = 0;

        foreach ($categories as $category) {
            $score = $this->scoreCategory($keywords, $category);
            $depth = $this->getCategoryDepth($category);

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestDepth = $depth;
                $bestMatch = [
                    'category' => $category,
                    'score' => $score,
                ];
            } elseif ($score === $bestScore && $depth > $bestDepth) {
                $bestDepth = $depth;
                $bestMatch = [
                    'category' => $category,
                    'score' => $score,
                ];
            }
        }

        return $bestMatch;
    }

    private function getCategoryDepth(ProductCategory $category): int
    {
        $depth = 0;
        $current = $category;

        while ($current->parent_id) {
            $depth++;
            $current = ProductCategory::find($current->parent_id);
            if (! $current) {
                break;
            }
        }

        return $depth;
    }

    private function scoreCategory(array $keywords, ProductCategory $category): int
    {
        $score = 0;
        $categoryName = strtolower($category->name);
        $categoryWords = preg_split('/[\s,]+/', $categoryName, -1, PREG_SPLIT_NO_EMPTY);

        $idName = $category->id_name ? strtolower($category->id_name) : null;
        $idNameWords = $idName ? preg_split('/[\s,]+/', $idName, -1, PREG_SPLIT_NO_EMPTY) : [];

        $keywordCount = count($keywords);
        $matchedKeywords = 0;
        $exactMatches = 0;

        foreach ($keywords as $keyword) {
            foreach ($categoryWords as $categoryWord) {
                if ($keyword === $categoryWord) {
                    $exactMatches++;
                    $matchedKeywords++;
                    $score += 150;

                    continue;
                }

                if (str_starts_with($categoryWord, $keyword) || str_starts_with($keyword, $categoryWord)) {
                    $matchedKeywords++;
                    $score += 80;
                } elseif (str_contains($categoryWord, $keyword) || str_contains($keyword, $categoryWord)) {
                    $matchedKeywords++;
                    $score += 40;
                }
            }

            if (! empty($idNameWords)) {
                foreach ($idNameWords as $idNameWord) {
                    if ($keyword === $idNameWord) {
                        $exactMatches++;
                        $matchedKeywords++;
                        $score += 150;

                        continue;
                    }

                    if (str_starts_with($idNameWord, $keyword) || str_starts_with($keyword, $idNameWord)) {
                        $matchedKeywords++;
                        $score += 80;
                    } elseif (str_contains($idNameWord, $keyword) || str_contains($keyword, $idNameWord)) {
                        $matchedKeywords++;
                        $score += 40;
                    }
                }
            }
        }

        if ($keywordCount > 0 && $matchedKeywords > 0) {
            $matchRatio = $matchedKeywords / $keywordCount;
            $score += (int) ($matchRatio * 50);
        }

        $score += ($exactMatches * 30);

        return $score;
    }
}
