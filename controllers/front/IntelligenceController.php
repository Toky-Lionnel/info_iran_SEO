<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Models\CategoryModel;
use App\Models\EventModel;
use App\Models\StatModel;
use App\Models\TimelineEventModel;

final class IntelligenceController
{
    public function map(): void
    {
        $categoryModel = new CategoryModel();
        $eventModel = new EventModel();

        $mapFilters = [
            'type' => trim((string) ($_GET['type'] ?? '')),
            'date_from' => trim((string) ($_GET['date_from'] ?? '')),
            'date_to' => trim((string) ($_GET['date_to'] ?? '')),
        ];
        $latestEvents = $eventModel->getAll($mapFilters, 30);

        $seo = [
            'title' => 'Carte interactive des evenements | ' . APP_NAME,
            'description' => 'Carte dynamique des evenements en Iran avec filtres type/date et visualisation geographique.',
            'canonical' => BASE_URL . '/carte',
            'og_type' => 'website',
        ];
        $currentPage = 'map';
        $extraCss = ['portal.css', 'intelligence.css', 'responsive.css'];
        $extraJs = ['map.js'];
        $navCategories = $categoryModel->getAll();
        $allowedEventTypes = $eventModel->getAllowedTypes();

        include ROOT . '/views/front/layouts/header.php';
        include ROOT . '/views/front/intelligence/map.php';
        include ROOT . '/views/front/layouts/footer.php';
    }

    public function timeline(): void
    {
        $categoryModel = new CategoryModel();
        $timelineModel = new TimelineEventModel();

        $timelineFilters = [
            'category' => trim((string) ($_GET['category'] ?? '')),
            'date_from' => trim((string) ($_GET['date_from'] ?? '')),
            'date_to' => trim((string) ($_GET['date_to'] ?? '')),
        ];
        $timelineEvents = $timelineModel->getAll($timelineFilters, 80);

        $seo = [
            'title' => 'Timeline interactive 2024-2026 | ' . APP_NAME,
            'description' => 'Chronologie horizontale interactive des evenements militaires, politiques et diplomatiques.',
            'canonical' => BASE_URL . '/timeline',
            'og_type' => 'website',
        ];
        $currentPage = 'timeline';
        $extraCss = ['portal.css', 'intelligence.css', 'responsive.css'];
        $extraJs = ['timeline.js'];
        $navCategories = $categoryModel->getAll();
        $allowedTimelineCategories = $timelineModel->getAllowedCategories();

        include ROOT . '/views/front/layouts/header.php';
        include ROOT . '/views/front/intelligence/timeline.php';
        include ROOT . '/views/front/layouts/footer.php';
    }

    public function stats(): void
    {
        $categoryModel = new CategoryModel();
        $statModel = new StatModel();

        $statsFilters = [
            'type' => trim((string) ($_GET['type'] ?? '')),
            'date_from' => trim((string) ($_GET['date_from'] ?? '')),
            'date_to' => trim((string) ($_GET['date_to'] ?? '')),
        ];
        $statRows = $statModel->getAll($statsFilters, 120);
        $statsSeries = $statModel->getSeriesForChart($statsFilters);

        $seo = [
            'title' => 'Statistiques dynamiques | ' . APP_NAME,
            'description' => 'Graphiques evolutifs sur les pertes, deplacements et sanctions economiques.',
            'canonical' => BASE_URL . '/statistiques',
            'og_type' => 'website',
        ];
        $currentPage = 'stats';
        $extraCss = ['portal.css', 'intelligence.css', 'responsive.css'];
        $extraJs = ['stats.js'];
        $navCategories = $categoryModel->getAll();
        $allowedStatTypes = $statModel->getAllowedTypes();

        include ROOT . '/views/front/layouts/header.php';
        include ROOT . '/views/front/intelligence/stats.php';
        include ROOT . '/views/front/layouts/footer.php';
    }
}
