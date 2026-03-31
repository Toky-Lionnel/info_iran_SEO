<?php

declare(strict_types=1);

if (!defined('ROOT')) {
    define('ROOT', __DIR__);
}

require_once ROOT . '/config/config.php';
require_once ROOT . '/core/Database.php';

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'App\\Core\\' => ROOT . '/core/',
        'App\\Models\\' => ROOT . '/models/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }
        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
});

use App\Models\ArticleModel;
use App\Models\CommunityModel;

header('Content-Type: application/xml; charset=utf-8');

$articleModel = new ArticleModel();
$articles = $articleModel->getPublished(500, 0);
$communityModel = new CommunityModel();
$debates = $communityModel->getPublishedDebates(200);

$xmlEscape = static fn(string $value): string => htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc><?= $xmlEscape(BASE_URL . '/') ?></loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?= $xmlEscape(BASE_URL . '/articles') ?></loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc><?= $xmlEscape(BASE_URL . '/nouveautes') ?></loc>
        <changefreq>daily</changefreq>
        <priority>0.85</priority>
    </url>
    <url>
        <loc><?= $xmlEscape(BASE_URL . '/debats') ?></loc>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?= $xmlEscape(BASE_URL . '/journaux') ?></loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?= $xmlEscape(BASE_URL . '/archives') ?></loc>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    <url>
        <loc><?= $xmlEscape(BASE_URL . '/carte') ?></loc>
        <changefreq>daily</changefreq>
        <priority>0.85</priority>
    </url>
    <url>
        <loc><?= $xmlEscape(BASE_URL . '/timeline') ?></loc>
        <changefreq>daily</changefreq>
        <priority>0.85</priority>
    </url>
    <url>
        <loc><?= $xmlEscape(BASE_URL . '/statistiques') ?></loc>
        <changefreq>daily</changefreq>
        <priority>0.85</priority>
    </url>
    <url>
        <loc><?= $xmlEscape(BASE_URL . '/contact') ?></loc>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    <?php foreach ($articles as $article): ?>
        <url>
            <loc><?= $xmlEscape(BASE_URL . '/article-' . (int) $article['id'] . '-' . (int) $article['category_id'] . '.html') ?></loc>
            <lastmod><?= date('Y-m-d', strtotime((string) $article['updated_at'])) ?></lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
    <?php endforeach; ?>
    <?php foreach ($debates as $debate): ?>
        <url>
            <loc><?= $xmlEscape(BASE_URL . '/debat/' . (string) $debate['slug']) ?></loc>
            <changefreq>daily</changefreq>
            <priority>0.7</priority>
        </url>
    <?php endforeach; ?>
</urlset>
