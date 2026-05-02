<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    private const DICTIONARY_PATH = 'translation-dictionary.json';

    private const AI_URL = 'https://ai.sumopod.com';

    private const AI_KEY = 'sk-CL9MdlKFe734KcKWJZgxUA';

    private const AI_MODEL = 'glm-5.1';

    private const AI_TIMEOUT = 30;

    private const CACHE_TTL = 3600;

    protected static array $memoryCache = [];

    public function translate(string $text): string
    {
        $text = trim($text);

        if (empty($text)) {
            return '';
        }

        $cacheKey = 'translate_'.md5(strtolower($text));

        if (isset(self::$memoryCache[$text])) {
            return self::$memoryCache[$text];
        }

        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            self::$memoryCache[$text] = $cached;

            return $cached;
        }

        $translation = $this->translateWithDictionary($text);

        if ($translation !== null) {
            self::$memoryCache[$text] = $translation;
            Cache::put($cacheKey, $translation, self::CACHE_TTL);

            return $translation;
        }

        $translation = $this->translateWithAI($text);

        if ($translation !== null && strtolower($translation) !== strtolower($text)) {
            $this->saveToDictionary($text, $translation);
            self::$memoryCache[$text] = $translation;
            Cache::put($cacheKey, $translation, self::CACHE_TTL);

            return $translation;
        }

        return $text;
    }

    private function translateWithDictionary(string $text): ?string
    {
        $dictionary = $this->loadDictionary();
        $lowerText = strtolower(trim($text));

        if (isset($dictionary[$lowerText])) {
            return $dictionary[$lowerText];
        }

        $words = preg_split('/\s+/', $lowerText, -1, PREG_SPLIT_NO_EMPTY);

        if (count($words) <= 1) {
            return null;
        }

        $translatedWords = [];
        $unknownSegments = [];
        $hasUnknown = false;

        foreach ($words as $word) {
            if (isset($dictionary[$word])) {
                if (! empty($unknownSegments)) {
                    $unknownPhrase = implode(' ', $unknownSegments);
                    $aiTranslation = $this->translateWithAI($unknownPhrase);
                    if ($aiTranslation !== null) {
                        $translatedWords[] = $aiTranslation;
                    } else {
                        $translatedWords[] = $unknownPhrase;
                    }
                    $unknownSegments = [];
                    $hasUnknown = false;
                }

                $translatedWords[] = $dictionary[$word];
            } else {
                $unknownSegments[] = $word;
                $hasUnknown = true;
            }
        }

        if (! empty($unknownSegments)) {
            $unknownPhrase = implode(' ', $unknownSegments);
            $aiTranslation = $this->translateWithAI($unknownPhrase);
            if ($aiTranslation !== null) {
                $translatedWords[] = $aiTranslation;
            } else {
                $translatedWords[] = $unknownPhrase;
            }
        }

        return implode(' ', $translatedWords);
    }

    private function translateWithAI(string $text): ?string
    {
        $lowerText = strtolower(trim($text));

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
                            'content' => 'Translate this product name to English. Return ONLY the translated text, no explanations.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $text,
                        ],
                    ],
                    'temperature' => 0.1,
                    'max_tokens' => 100,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $translation = trim($data['choices'][0]['message']['content'] ?? '');
                $translation = trim($translation, '"\'');

                if (! empty($translation)) {
                    return $translation;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('AI translation failed: '.$e->getMessage());

            return null;
        }
    }

    private function saveToDictionary(string $original, string $translation): void
    {
        $dictionary = $this->loadDictionary();
        $dictionary[strtolower(trim($original))] = $translation;
        $this->saveDictionary($dictionary);
    }

    private function loadDictionary(): array
    {
        $path = storage_path('app/'.self::DICTIONARY_PATH);

        if (! file_exists($path)) {
            return [];
        }

        $content = file_get_contents($path);

        if ($content === false) {
            return [];
        }

        return json_decode($content, true) ?? [];
    }

    private function saveDictionary(array $dictionary): void
    {
        $path = storage_path('app/'.self::DICTIONARY_PATH);
        $json = json_encode($dictionary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            Log::error('Failed to encode translation dictionary');

            return;
        }

        $fp = fopen($path, 'c');
        if (! $fp) {
            Log::error('Failed to open translation dictionary for writing');

            return;
        }

        if (flock($fp, LOCK_EX)) {
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, $json);
            fflush($fp);
            flock($fp, LOCK_UN);
        } else {
            Log::error('Failed to lock translation dictionary');
        }

        fclose($fp);
    }
}
