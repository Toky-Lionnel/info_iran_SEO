<?php

declare(strict_types=1);

namespace App\Core;

final class Helpers
{
    private const ARTICLE_MEDIA = [
        1 => [
            'image' => '/public/images/articles/article-01-rising-lion.jpg',
            'source' => 'https://loremflickr.com/1200/630/iran,airstrike?lock=1',
        ],
        2 => [
            'image' => '/public/images/articles/article-02-midnight-hammer.jpg',
            'source' => 'https://loremflickr.com/1200/630/nuclear,facility?lock=2',
        ],
        3 => [
            'image' => '/public/images/articles/article-03-war-timeline.jpg',
            'source' => 'https://loremflickr.com/1200/630/war,city,night?lock=3',
        ],
        4 => [
            'image' => '/public/images/articles/article-04-epic-fury.jpg',
            'source' => 'https://loremflickr.com/1200/630/missile,launch?lock=4',
        ],
        5 => [
            'image' => '/public/images/articles/article-05-ormuz.jpg',
            'source' => 'https://loremflickr.com/1200/630/strait,ship,tanker?lock=5',
        ],
        6 => [
            'image' => '/public/images/articles/article-06-protests.jpg',
            'source' => 'https://loremflickr.com/1200/630/protest,crowd?lock=6',
        ],
        7 => [
            'image' => '/public/images/articles/article-07-europe-diplomacy.jpg',
            'source' => 'https://loremflickr.com/1200/630/europe,parliament,diplomacy?lock=7',
        ],
        8 => [
            'image' => '/public/images/articles/article-08-natanz.jpg',
            'source' => 'https://loremflickr.com/1200/630/nuclear,industry?lock=8',
        ],
        9 => [
            'image' => '/public/images/articles/article-09-south-pars.jpg',
            'source' => 'https://loremflickr.com/1200/630/gas,industry,plant?lock=9',
        ],
        10 => [
            'image' => '/public/images/articles/article-10-humanitarian.jpg',
            'source' => 'https://loremflickr.com/1200/630/refugee,humanitarian,aid?lock=10',
        ],
        11 => [
            'image' => '/public/images/articles/article-11-economy.jpg',
            'source' => 'https://loremflickr.com/1200/630/economy,market,iran?lock=11',
        ],
        12 => [
            'image' => '/public/images/articles/article-12-proxies.jpg',
            'source' => 'https://loremflickr.com/1200/630/middleeast,map,conflict?lock=12',
        ],
        13 => [
            'image' => '/public/images/articles/article-13-cyber.jpg',
            'source' => 'https://loremflickr.com/1200/630/cyber,security,network?lock=13',
        ],
        14 => [
            'image' => '/public/images/articles/article-14-negotiation.jpg',
            'source' => 'https://loremflickr.com/1200/630/united,nations,meeting?lock=14',
        ],
    ];

    private const INTELLIGENCE_MEDIA = [
        'map' => [
            'image' => '/public/images/intelligence/intelligence-map.jpg',
            'source' => 'https://loremflickr.com/1400/700/iran,map,satellite?lock=101',
            'label' => 'Source visuelle carte',
        ],
        'timeline' => [
            'image' => '/public/images/intelligence/intelligence-timeline.jpg',
            'source' => 'https://loremflickr.com/1400/700/history,timeline,archive?lock=102',
            'label' => 'Source visuelle timeline',
        ],
        'stats' => [
            'image' => '/public/images/intelligence/intelligence-stats.jpg',
            'source' => 'https://loremflickr.com/1400/700/statistics,data,chart?lock=103',
            'label' => 'Source visuelle statistiques',
        ],
    ];

    private function __construct()
    {
    }

    public static function slugify(string $text, int $maxLength = 200): string
    {
        $slug = strtolower(trim($text));
        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug);
        if ($transliterated !== false) {
            $slug = $transliterated;
        }

        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');
        if ($slug === '') {
            $slug = 'item-' . date('YmdHis');
        }

        return substr($slug, 0, max(10, $maxLength));
    }

    public static function paginate(int $totalItems, int $perPage, int $currentPage): array
    {
        $perPage = max(1, $perPage);
        $currentPage = max(1, $currentPage);
        $totalPages = max(1, (int) ceil($totalItems / $perPage));
        $currentPage = min($currentPage, $totalPages);

        return [
            'total_items' => $totalItems,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'offset' => ($currentPage - 1) * $perPage,
            'has_prev' => $currentPage > 1,
            'has_next' => $currentPage < $totalPages,
            'prev_page' => max(1, $currentPage - 1),
            'next_page' => min($totalPages, $currentPage + 1),
        ];
    }

    public static function sanitizeHtml(string $html): string
    {
        $allowedTags = '<p><h2><h3><h4><h5><h6><ul><ol><li><strong><em><blockquote><a><img><figure><figcaption><br><table><thead><tbody><tr><th><td><code><pre>';
        $clean = strip_tags($html, $allowedTags);

        // Neutralize dangerous URI schemes inside href/src.
        $clean = preg_replace_callback(
            '/\s(href|src)\s*=\s*([\'"])(.*?)\2/i',
            static function (array $matches): string {
                $attr = strtolower($matches[1]);
                $quote = $matches[2];
                $value = trim($matches[3]);

                if (preg_match('/^\s*(javascript:|data:|vbscript:)/i', $value) === 1) {
                    $value = '#';
                }

                return ' ' . $attr . '=' . $quote . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . $quote;
            },
            $clean
        ) ?? $clean;

        return $clean;
    }

    public static function truncate(string $text, int $maxLength = 160): string
    {
        $text = trim($text);
        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, max(3, $maxLength - 1))) . '...';
    }

    public static function resolveAssetUrl(?string $path, string $fallback = '/public/images/placeholder.webp'): string
    {
        $value = trim((string) $path);
        if ($value === '') {
            return BASE_URL . $fallback;
        }

        if (preg_match('#^https?://#i', $value) === 1) {
            return $value;
        }

        $legacyPrefixes = [
            '/iran/',
            '/' . APP_DIR_NAME . '/',
        ];
        foreach ($legacyPrefixes as $prefix) {
            if (str_starts_with($value, $prefix)) {
                $value = '/' . ltrim(substr($value, strlen($prefix)), '/');
                break;
            }
        }

        if (str_starts_with($value, '/')) {
            return BASE_URL . $value;
        }

        if (str_starts_with($value, 'public/')) {
            return BASE_URL . '/' . $value;
        }

        return $value;
    }

    public static function resolveArticleCover(array $article): string
    {
        $rawCover = trim((string) ($article['cover_image'] ?? ''));
        $articleId = (int) ($article['id'] ?? 0);

        $isPlaceholder = $rawCover === ''
            || str_contains($rawCover, '/public/images/placeholder.webp')
            || str_contains($rawCover, '/iran/public/images/placeholder.webp');

        if ($isPlaceholder && isset(self::ARTICLE_MEDIA[$articleId]['image'])) {
            return self::resolveAssetUrl((string) self::ARTICLE_MEDIA[$articleId]['image']);
        }

        return self::resolveAssetUrl($rawCover);
    }

    public static function getArticleImageSourceUrl(int $articleId): ?string
    {
        return self::ARTICLE_MEDIA[$articleId]['source'] ?? null;
    }

    public static function getIntelligenceVisual(string $key): ?array
    {
        if (!isset(self::INTELLIGENCE_MEDIA[$key])) {
            return null;
        }

        $item = self::INTELLIGENCE_MEDIA[$key];
        return [
            'image' => self::resolveAssetUrl((string) $item['image']),
            'source' => (string) $item['source'],
            'label' => (string) $item['label'],
        ];
    }
}
