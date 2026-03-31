<?php

declare(strict_types=1);

namespace App\Core;

use App\Controllers\Api\ApiController;
use App\Controllers\Back\ArticleController as BackArticleController;
use App\Controllers\Back\AuthController;
use App\Controllers\Back\CategoryController as BackCategoryController;
use App\Controllers\Back\CommunityController as BackCommunityController;
use App\Controllers\Back\DashboardController;
use App\Controllers\Back\EventController as BackEventController;
use App\Controllers\Back\ReportController as BackReportController;
use App\Controllers\Back\SecurityController as BackSecurityController;
use App\Controllers\Back\StatController as BackStatController;
use App\Controllers\Back\SubscriberController as BackSubscriberController;
use App\Controllers\Back\TimelineController as BackTimelineController;
use App\Controllers\Back\UserController;
use App\Controllers\Front\AccountController;
use App\Controllers\Front\ArticleController as FrontArticleController;
use App\Controllers\Front\CategoryController as FrontCategoryController;
use App\Controllers\Front\ErrorController;
use App\Controllers\Front\HomeController;
use App\Controllers\Front\IntelligenceController;
use App\Controllers\Front\PortalController;

final class Router
{
    public function dispatch(string $url): void
    {
        $route = $this->normalizeRoute($url);
        if ($route === null) {
            (new ErrorController())->forbidden('URL invalide ou potentiellement dangereuse.');
            return;
        }

        $this->applyCacheHeaders($route);

        if ($route === '') {
            $this->dispatchFront('');
            return;
        }

        $segments = explode('/', $route);
        if (($segments[0] ?? '') === 'admin') {
            $this->dispatchBack($segments);
            return;
        }

        if (($segments[0] ?? '') === 'api') {
            $this->dispatchApi($segments);
            return;
        }

        $this->dispatchFront($route);
    }

    private function dispatchFront(string $route): void
    {
        if ($route === '') {
            (new HomeController())->index();
            return;
        }

        if ($route === 'articles') {
            $page = $this->sanitizePage($_GET['page'] ?? 1);
            (new FrontArticleController())->list($page);
            return;
        }

        if ($route === 'nouveautes') {
            (new PortalController())->nouveautes();
            return;
        }

        if ($route === 'debats') {
            (new PortalController())->debates();
            return;
        }

        if (preg_match('/^debat\/([a-z0-9-]+)$/i', $route, $matches) === 1) {
            (new PortalController())->debateDetail((string) $matches[1]);
            return;
        }

        if ($route === 'journaux') {
            (new PortalController())->journals();
            return;
        }

        if ($route === 'archives') {
            (new PortalController())->archives();
            return;
        }

        if ($route === 'contact') {
            (new PortalController())->contact();
            return;
        }

        if ($route === 'abonnes') {
            (new PortalController())->premium();
            return;
        }

        if ($route === 'carte') {
            (new IntelligenceController())->map();
            return;
        }

        if ($route === 'timeline') {
            (new IntelligenceController())->timeline();
            return;
        }

        if ($route === 'statistiques') {
            (new IntelligenceController())->stats();
            return;
        }

        if ($route === 'avis/create') {
            (new PortalController())->createReview();
            return;
        }

        if ($route === 'share-log') {
            (new PortalController())->logShare();
            return;
        }

        if ($route === 'compte/login') {
            (new AccountController())->login();
            return;
        }

        if ($route === 'compte/register') {
            (new AccountController())->register();
            return;
        }

        if ($route === 'compte/profil') {
            (new AccountController())->profile();
            return;
        }

        if ($route === 'compte/logout') {
            (new AccountController())->logout();
            return;
        }

        if ($route === 'sitemap.xml') {
            require ROOT . '/sitemap.php';
            return;
        }

        if (preg_match('/^article-(\d+)-(\d+)\.html$/', $route, $matches) === 1) {
            (new FrontArticleController())->detail((int) $matches[1], (int) $matches[2]);
            return;
        }

        if (preg_match('/^categorie-(\d+)-(\d+)\.html$/', $route, $matches) === 1) {
            (new FrontCategoryController())->showById((int) $matches[1], (int) $matches[2]);
            return;
        }

        if (preg_match('/^categorie\/([a-z0-9-]+)(?:\/(\d+))?$/i', $route, $matches) === 1) {
            $page = $this->sanitizePage($matches[2] ?? 1);
            (new FrontCategoryController())->show($matches[1], $page);
            return;
        }

        if ($route === '403') {
            (new ErrorController())->forbidden();
            return;
        }

        if ($route === '404') {
            (new ErrorController())->notFound();
            return;
        }

        (new ErrorController())->notFound();
    }

    private function dispatchBack(array $segments): void
    {
        $resource = $segments[1] ?? '';

        switch ($resource) {
            case '':
                if (Session::isAdmin()) {
                    (new DashboardController())->index();
                } else {
                    header('Location: ' . ADMIN_PATH . '/login');
                    exit;
                }
                return;

            case 'login':
                (new AuthController())->login();
                return;

            case 'logout':
                (new AuthController())->logout();
                return;

            case 'articles':
                $this->dispatchBackArticles($segments);
                return;

            case 'categories':
                $this->dispatchBackCategories($segments);
                return;

            case 'users':
                $this->dispatchBackUsers($segments);
                return;

            case 'subscribers':
                $this->dispatchBackSubscribers($segments);
                return;

            case 'community':
                $this->dispatchBackCommunity($segments);
                return;

            case 'reports':
                $this->dispatchBackReports($segments);
                return;

            case 'events':
                $this->dispatchBackEvents($segments);
                return;

            case 'timeline':
                $this->dispatchBackTimeline($segments);
                return;

            case 'stats':
                $this->dispatchBackStats($segments);
                return;

            case 'security':
                $this->dispatchBackSecurity($segments);
                return;

            default:
                (new ErrorController())->notFound('La route admin demandee est inconnue.');
        }
    }

    private function dispatchBackArticles(array $segments): void
    {
        $controller = new BackArticleController();
        $action = $segments[2] ?? '';
        $id = isset($segments[3]) ? (int) $segments[3] : 0;

        if ($action === '') {
            $controller->list();
            return;
        }

        if ($action === 'create') {
            $controller->create();
            return;
        }

        if ($action === 'edit' && $id > 0) {
            $controller->edit($id);
            return;
        }

        if ($action === 'delete' && $id > 0) {
            $controller->delete($id);
            return;
        }

        if ($action === 'toggle' && $id > 0) {
            $controller->toggleStatus($id);
            return;
        }

        (new ErrorController())->notFound('La route article demandee est inconnue.');
    }

    private function dispatchBackCategories(array $segments): void
    {
        $controller = new BackCategoryController();
        $action = $segments[2] ?? '';
        $id = isset($segments[3]) ? (int) $segments[3] : 0;

        if ($action === '') {
            $controller->list();
            return;
        }

        if ($action === 'create') {
            $controller->create();
            return;
        }

        if ($action === 'edit' && $id > 0) {
            $controller->edit($id);
            return;
        }

        if ($action === 'delete' && $id > 0) {
            $controller->delete($id);
            return;
        }

        (new ErrorController())->notFound('La route categorie demandee est inconnue.');
    }

    private function dispatchBackUsers(array $segments): void
    {
        $controller = new UserController();
        $action = $segments[2] ?? '';
        $id = isset($segments[3]) ? (int) $segments[3] : 0;

        if ($action === '') {
            $controller->list();
            return;
        }

        if ($action === 'create') {
            $controller->create();
            return;
        }

        if ($action === 'delete' && $id > 0) {
            $controller->delete($id);
            return;
        }

        if ($action === 'change-password') {
            $controller->changePassword();
            return;
        }

        (new ErrorController())->notFound('La route utilisateur demandee est inconnue.');
    }

    private function dispatchBackSubscribers(array $segments): void
    {
        $controller = new BackSubscriberController();
        $action = $segments[2] ?? '';
        $id = isset($segments[3]) ? (int) $segments[3] : 0;

        if ($action === '') {
            $controller->list();
            return;
        }

        if ($action === 'edit' && $id > 0) {
            $controller->edit($id);
            return;
        }

        if ($action === 'delete' && $id > 0) {
            $controller->delete($id);
            return;
        }

        (new ErrorController())->notFound('La route abonne demandee est inconnue.');
    }

    private function dispatchBackCommunity(array $segments): void
    {
        $controller = new BackCommunityController();
        $resource = $segments[2] ?? '';
        $action = $segments[3] ?? '';
        $id = isset($segments[4]) ? (int) $segments[4] : 0;

        if ($resource === '') {
            $controller->index();
            return;
        }

        if ($resource === 'comments' && in_array($action, ['approve', 'reject', 'delete'], true) && $id > 0) {
            $controller->moderateComment($action, $id);
            return;
        }

        if ($resource === 'debate-comments' && in_array($action, ['approve', 'reject', 'delete'], true) && $id > 0) {
            $controller->moderateDebateComment($action, $id);
            return;
        }

        if ($resource === 'reviews' && in_array($action, ['approve', 'reject', 'delete'], true) && $id > 0) {
            $controller->moderateReview($action, $id);
            return;
        }

        if ($resource === 'subscribers' && $action === 'toggle' && $id > 0) {
            $controller->toggleSubscriber($id);
            return;
        }

        if ($resource === 'subscribers' && $action === 'active' && $id > 0) {
            $controller->toggleSubscriberActive($id);
            return;
        }

        if ($resource === 'contacts' && in_array($action, ['read', 'close', 'delete'], true) && $id > 0) {
            $controller->contactAction($action, $id);
            return;
        }

        if ($resource === 'journals' && $action === 'create') {
            $controller->createJournal();
            return;
        }

        if ($resource === 'journals' && $action === 'delete' && $id > 0) {
            $controller->deleteJournal($id);
            return;
        }

        if ($resource === 'debates' && $action === 'create') {
            $controller->createDebate();
            return;
        }

        if ($resource === 'debates' && $action === 'delete' && $id > 0) {
            $controller->deleteDebate($id);
            return;
        }

        (new ErrorController())->notFound('La route communaute demandee est inconnue.');
    }

    private function dispatchBackReports(array $segments): void
    {
        $controller = new BackReportController();
        $resource = $segments[2] ?? '';
        $target = $segments[3] ?? '';

        if ($resource === '') {
            $controller->index();
            return;
        }

        if ($resource === 'export' && $target !== '') {
            $controller->export($target);
            return;
        }

        if ($resource === 'import' && $target === 'subscribers') {
            $controller->importSubscribers();
            return;
        }

        (new ErrorController())->notFound('La route report demandee est inconnue.');
    }

    private function dispatchBackEvents(array $segments): void
    {
        $controller = new BackEventController();
        $action = $segments[2] ?? '';
        $id = isset($segments[3]) ? (int) $segments[3] : 0;

        if ($action === '') {
            $controller->list();
            return;
        }

        if ($action === 'create') {
            $controller->create();
            return;
        }

        if ($action === 'edit' && $id > 0) {
            $controller->edit($id);
            return;
        }

        if ($action === 'delete' && $id > 0) {
            $controller->delete($id);
            return;
        }

        (new ErrorController())->notFound('La route evenements demandee est inconnue.');
    }

    private function dispatchBackTimeline(array $segments): void
    {
        $controller = new BackTimelineController();
        $action = $segments[2] ?? '';
        $id = isset($segments[3]) ? (int) $segments[3] : 0;

        if ($action === '') {
            $controller->list();
            return;
        }

        if ($action === 'create') {
            $controller->create();
            return;
        }

        if ($action === 'edit' && $id > 0) {
            $controller->edit($id);
            return;
        }

        if ($action === 'delete' && $id > 0) {
            $controller->delete($id);
            return;
        }

        (new ErrorController())->notFound('La route timeline demandee est inconnue.');
    }

    private function dispatchBackStats(array $segments): void
    {
        $controller = new BackStatController();
        $action = $segments[2] ?? '';
        $id = isset($segments[3]) ? (int) $segments[3] : 0;

        if ($action === '') {
            $controller->list();
            return;
        }

        if ($action === 'create') {
            $controller->create();
            return;
        }

        if ($action === 'edit' && $id > 0) {
            $controller->edit($id);
            return;
        }

        if ($action === 'delete' && $id > 0) {
            $controller->delete($id);
            return;
        }

        (new ErrorController())->notFound('La route stats demandee est inconnue.');
    }

    private function dispatchBackSecurity(array $segments): void
    {
        $controller = new BackSecurityController();
        $action = $segments[2] ?? '';

        if ($action === '') {
            $controller->index();
            return;
        }

        if ($action === 'clear-cache') {
            $controller->clearExpiredCache();
            return;
        }

        (new ErrorController())->notFound('La route securite demandee est inconnue.');
    }

    private function dispatchApi(array $segments): void
    {
        $controller = new ApiController();
        $resource = $segments[1] ?? '';
        $action = $segments[2] ?? '';

        if ($resource === 'events') {
            $controller->events();
            return;
        }

        if ($resource === 'timeline') {
            $controller->timeline();
            return;
        }

        if ($resource === 'stats') {
            $controller->stats();
            return;
        }

        if ($resource === 'articles') {
            $controller->articles();
            return;
        }

        if ($resource === 'favorite') {
            $controller->favorite();
            return;
        }

        if ($resource === 'notifications' && $action === '') {
            $controller->notifications();
            return;
        }

        if ($resource === 'notifications' && $action === 'read') {
            $controller->readNotification();
            return;
        }

        if ($resource === 'analytics') {
            $controller->analytics();
            return;
        }

        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'API route not found']);
    }

    private function sanitizePage(mixed $value): int
    {
        $page = (int) $value;
        return max(1, $page);
    }

    private function normalizeRoute(string $raw): ?string
    {
        $decoded = rawurldecode($raw);
        $route = trim($decoded, '/');
        $route = preg_replace('#/+#', '/', $route) ?? '';

        if ($this->isSuspiciousRoute($route)) {
            return null;
        }

        return $route;
    }

    private function isSuspiciousRoute(string $route): bool
    {
        if (strlen($route) > 2048) {
            return true;
        }

        if (preg_match('/[\x00-\x1F\x7F]/', $route) === 1) {
            return true;
        }

        if (str_contains($route, '..') || str_contains($route, '\\')) {
            return true;
        }

        return preg_match('#(?:^|/)\.#', $route) === 1;
    }

    private function applyCacheHeaders(string $route): void
    {
        if (headers_sent()) {
            return;
        }

        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $firstSegment = explode('/', $route)[0] ?? '';
        $isApiRoute = $firstSegment === 'api';
        $isAdminRoute = $firstSegment === 'admin';
        $isAccountRoute = str_starts_with($route, 'compte/');

        if ($isApiRoute) {
            return;
        }

        if ($isAdminRoute || $isAccountRoute || !in_array($method, ['GET', 'HEAD'], true)) {
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0', true);
            header('Pragma: no-cache', true);
            header('Expires: 0', true);
            return;
        }

        header_remove('Pragma');
        header_remove('Expires');
        header('Cache-Control: private, max-age=300, stale-while-revalidate=30', true);
        header('Vary: Accept-Encoding, Cookie', true);
    }
}
