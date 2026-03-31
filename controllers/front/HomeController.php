<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Models\ArticleModel;
use App\Models\CategoryModel;
use App\Models\CommunityModel;

final class HomeController
{
    public function index(): void
    {
        $articleModel = new ArticleModel();
        $categoryModel = new CategoryModel();
        $communityModel = new CommunityModel();

        $featuredArticles = $articleModel->getPublished(6, 0);
        $latestArticles = $articleModel->getPublished(6, 6);
        $categories = $categoryModel->getAll();
        $featuredDebates = $communityModel->getPublishedDebates(3);
        $pinnedJournals = $communityModel->getPublishedJournals(3);
        $approvedReviews = $communityModel->getApprovedReviews(6);

        $seo = [
            'title' => 'Guerre en Iran 2024-2026 : Chronologie et Analyses',
            'description' => APP_DESC,
            'canonical' => BASE_URL . '/',
            'og_type' => 'website',
            'og_image' => BASE_URL . '/public/images/iran-flag.webp',
        ];

        $schemaOrg = '<script type="application/ld+json">' . json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => APP_NAME,
            'url' => BASE_URL,
            'description' => APP_DESC,
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => BASE_URL . '/articles?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';

        $currentPage = 'home';
        $extraCss = ['home.css', 'responsive.css'];
        $extraJs = ['search.js'];
        $navCategories = $categories;

        include ROOT . '/views/front/layouts/header.php';
        include ROOT . '/views/front/home/index.php';
        include ROOT . '/views/front/layouts/footer.php';
    }
}
