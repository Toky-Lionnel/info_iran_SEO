<?php

declare(strict_types=1);

namespace App\Controllers\Back;

use App\Core\Session;
use App\Models\AnalyticsModel;
use App\Models\ArticleModel;
use App\Models\CategoryModel;
use App\Models\CommunityModel;
use App\Models\ReportModel;
use App\Models\SeoAnalysisModel;
use App\Models\SubscriberModel;

final class DashboardController
{
    public function index(): void
    {
        Session::requireAdmin();

        $articleModel = new ArticleModel();
        $categoryModel = new CategoryModel();
        $communityModel = new CommunityModel();
        $subscriberModel = new SubscriberModel();
        $reportModel = new ReportModel();
        $analyticsModel = new AnalyticsModel();
        $seoAnalysisModel = new SeoAnalysisModel();

        $dashboardFilters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'status' => trim((string) ($_GET['status'] ?? '')),
            'category_id' => (int) ($_GET['category_id'] ?? 0),
            'date_from' => trim((string) ($_GET['date_from'] ?? '')),
            'date_to' => trim((string) ($_GET['date_to'] ?? '')),
            'sort' => trim((string) ($_GET['sort'] ?? 'latest')),
        ];

        $articleStats = $articleModel->getStats();
        $communityStats = $communityModel->getAdminCounts();
        $stats = [
            'total_articles' => $articleStats['published_articles'],
            'total_views' => $articleStats['total_views'],
            'total_categories' => $categoryModel->countAll(),
            'drafts' => $articleStats['draft_articles'],
            'pending_comments' => $communityStats['pending_comments'],
            'new_contacts' => $communityStats['new_contacts'],
            'premium_subscribers' => $subscriberModel->countPremium(),
            'active_subscribers' => $subscriberModel->countActive(),
            'avg_reading_duration' => $analyticsModel->getAverageReadingDuration(),
            'conversion_rate' => $analyticsModel->getSubscriberConversionRate(),
        ];

        $recentArticles = $articleModel->getDashboardRows($dashboardFilters, 15);
        $categories = $categoryModel->getAll();
        $recentExchangeLogs = $reportModel->getRecentLogs(10);
        $topPages = $analyticsModel->getTopPages(10);
        $mostReadArticles = $analyticsModel->getMostReadArticles(8);
        $dailyTraffic = $analyticsModel->getDailyTraffic(30);
        $seoWeakArticles = $seoAnalysisModel->getWorstScores(8);
        $seo = ['title' => 'Dashboard'];
        $adminPage = 'dashboard';

        include ROOT . '/views/back/layouts/header.php';
        include ROOT . '/views/back/dashboard/index.php';
        include ROOT . '/views/back/layouts/footer.php';
    }
}
