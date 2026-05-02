<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageFinder
{
    private const CACHE_PATH = 'product-images.json';

    private const AI_URL = 'https://ai.sumopod.com';

    private const AI_KEY = 'sk-CL9MdlKFe734KcKWJZgxUA';

    private const AI_MODEL = 'glm-5.1';

    private const AI_TIMEOUT = 30;

    private const IMAGE_TIMEOUT = 15;

    private const LOCAL_IMAGE_PATH = 'products';

    protected static array $memoryCache = [];

    protected TranslationService $translator;

    public function __construct()
    {
        $this->translator = new TranslationService;
    }

    public function searchImage(string $productName): ?string
    {
        $cacheKey = strtolower(trim($productName));

        if (isset(self::$memoryCache[$cacheKey])) {
            return self::$memoryCache[$cacheKey];
        }

        $cached = $this->loadCache();

        if (array_key_exists($cacheKey, $cached)) {
            return $cached[$cacheKey];
        }

        $localImage = $this->findLocalImage($productName);

        if ($localImage) {
            self::$memoryCache[$cacheKey] = $localImage;
            $this->saveToCache($cacheKey, $localImage);

            return $localImage;
        }

        $imageUrl = $this->searchImageOnWeb($productName);

        self::$memoryCache[$cacheKey] = $imageUrl;
        $this->saveToCache($cacheKey, $imageUrl);

        return $imageUrl;
    }

    protected function findLocalImage(string $productName): ?string
    {
        if (! Storage::disk('public')->exists(self::LOCAL_IMAGE_PATH)) {
            return null;
        }

        $files = Storage::disk('public')->files(self::LOCAL_IMAGE_PATH);

        $searchTerms = $this->extractSearchTerms($productName);

        foreach ($files as $file) {
            if (! str_ends_with(strtolower($file), '.webp')) {
                continue;
            }

            $fileName = basename($file, '.webp');

            foreach ($searchTerms as $term) {
                if (stripos($fileName, $term) !== false) {
                    return Storage::disk('public')->url($file);
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

    protected function searchImageOnWeb(string $productName): ?string
    {
        $searchQuery = $this->generateSearchQuery($productName);

        $providers = [
            fn () => $this->searchPexels($searchQuery),
            fn () => $this->searchPixabay($searchQuery),
            fn () => $this->searchUnsplash($searchQuery),
            fn () => $this->searchDuckDuckGo($searchQuery),
        ];

        foreach ($providers as $provider) {
            try {
                $imageUrl = $provider();

                if ($imageUrl) {
                    return $imageUrl;
                }
            } catch (\Exception $e) {
                Log::warning('Image provider failed: '.$e->getMessage());
            }
        }

        return null;
    }

    protected function searchPexels(string $query): ?string
    {
        $response = Http::timeout(self::IMAGE_TIMEOUT)
            ->withHeaders([
                'Authorization' => config('services.pexels.key', ''),
            ])
            ->get('https://api.pexels.com/v1/search', [
                'query' => $query,
                'per_page' => 5,
            ]);

        if ($response->successful()) {
            $data = $response->json();

            if (! empty($data['photos'])) {
                return $data['photos'][0]['src']['medium'] ?? null;
            }
        }

        return null;
    }

    protected function searchPixabay(string $query): ?string
    {
        $apiKey = config('services.pixabay.key', '');

        if (empty($apiKey)) {
            return null;
        }

        $response = Http::timeout(self::IMAGE_TIMEOUT)
            ->get('https://pixabay.com/api/', [
                'key' => $apiKey,
                'q' => $query,
                'image_type' => 'photo',
                'per_page' => 5,
            ]);

        if ($response->successful()) {
            $data = $response->json();

            if (! empty($data['hits'])) {
                return $data['hits'][0]['webformatURL'] ?? null;
            }
        }

        return null;
    }

    protected function searchUnsplash(string $query): ?string
    {
        $apiKey = config('services.unsplash.key', '');

        if (empty($apiKey)) {
            return null;
        }

        $response = Http::timeout(self::IMAGE_TIMEOUT)
            ->get('https://api.unsplash.com/search/photos', [
                'query' => $query,
                'per_page' => 5,
            ])
            ->withHeaders([
                'Authorization' => 'Client-ID '.$apiKey,
            ]);

        if ($response->successful()) {
            $data = $response->json();

            if (! empty($data['results'])) {
                return $data['results'][0]['urls']['regular'] ?? null;
            }
        }

        return null;
    }

    protected function searchDuckDuckGo(string $query): ?string
    {
        $response = Http::timeout(self::IMAGE_TIMEOUT)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            ])
            ->get('https://html.duckduckgo.com/html/', ['q' => $query.' site:pexels.com OR site:pixabay.com OR site:unsplash.com']);

        if (! $response->successful()) {
            return null;
        }

        $html = $response->body();

        if (preg_match('/<img[^>]+src="([^"]+\.(?:jpg|jpeg|png|webp))"/i', $html, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function generateSearchQuery(string $productName): string
    {
        $englishName = $this->translator->translate($productName);

        try {
            $response = Http::timeout(self::AI_TIMEOUT)
                ->withHeaders([
                    'Authorization' => 'Bearer '.self::AI_KEY,
                    'Content-Type' => 'application/json',
                ])
                ->post(rtrim(self::AI_URL, '/').'/chat/completions', [
                    'model' => self::AI_MODEL,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Generate a short, specific search query in English to find a product image. Return only the query, nothing else. Focus on the product type.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $englishName,
                        ],
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 50,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $query = trim($data['choices'][0]['message']['content'] ?? '');

                if (! empty($query)) {
                    return $query.' product image';
                }
            }
        } catch (\Exception $e) {
            Log::warning('AI query generation failed: '.$e->getMessage());
        }

        return $englishName.' product image';
    }

    protected function loadCache(): array
    {
        $path = storage_path('cache/'.self::CACHE_PATH);

        if (! file_exists($path)) {
            return [];
        }

        $content = file_get_contents($path);

        if ($content === false) {
            return [];
        }

        return json_decode($content, true) ?? [];
    }

    protected function saveToCache(string $key, ?string $value): void
    {
        $cache = $this->loadCache();
        $cache[$key] = $value;
        $this->saveCache($cache);
    }

    protected function saveCache(array $cache): void
    {
        $path = storage_path('cache/'.self::CACHE_PATH);
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $json = json_encode($cache, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            Log::error('Failed to encode image cache');

            return;
        }

        $fp = fopen($path, 'c');
        if (! $fp) {
            Log::error('Failed to open image cache for writing');

            return;
        }

        if (flock($fp, LOCK_EX)) {
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, $json);
            fflush($fp);
            flock($fp, LOCK_UN);
        } else {
            Log::error('Failed to lock image cache');
        }

        fclose($fp);
    }
}
