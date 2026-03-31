<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Session;
use App\Models\AnalyticsModel;
use App\Models\ArticleModel;
use App\Models\CacheModel;
use App\Models\EventModel;
use App\Models\FavoriteModel;
use App\Models\NotificationModel;
use App\Models\StatModel;
use App\Models\TimelineEventModel;
use Throwable;

final class ApiController
{
    public function events(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->respond(['ok' => false, 'error' => 'Method not allowed'], 405);
            return;
        }

        $filters = [
            'type' => trim((string) ($_GET['type'] ?? '')),
            'date_from' => trim((string) ($_GET['date_from'] ?? '')),
            'date_to' => trim((string) ($_GET['date_to'] ?? '')),
        ];

        $cache = new CacheModel();
        $cacheKey = 'api.events.' . md5(json_encode($filters));
        $cached = $cache->get($cacheKey);
        if ($cached !== null) {
            $this->respondPublicJson($cached, 120);
            return;
        }

        $eventModel = new EventModel();
        $rows = $eventModel->getAll($filters, 500);
        $payload = [
            'ok' => true,
            'count' => count($rows),
            'data' => $rows,
        ];
        $json = $this->encode($payload);
        $cache->set($cacheKey, $json, 180);
        $this->respondPublicJson($json, 120);
    }

    public function timeline(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->respond(['ok' => false, 'error' => 'Method not allowed'], 405);
            return;
        }

        $filters = [
            'category' => trim((string) ($_GET['category'] ?? '')),
            'date_from' => trim((string) ($_GET['date_from'] ?? '')),
            'date_to' => trim((string) ($_GET['date_to'] ?? '')),
        ];

        $cache = new CacheModel();
        $cacheKey = 'api.timeline.' . md5(json_encode($filters));
        $cached = $cache->get($cacheKey);
        if ($cached !== null) {
            $this->respondPublicJson($cached, 120);
            return;
        }

        $timelineModel = new TimelineEventModel();
        $rows = $timelineModel->getAll($filters, 500);
        $payload = [
            'ok' => true,
            'count' => count($rows),
            'data' => $rows,
        ];
        $json = $this->encode($payload);
        $cache->set($cacheKey, $json, 180);
        $this->respondPublicJson($json, 120);
    }

    public function stats(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->respond(['ok' => false, 'error' => 'Method not allowed'], 405);
            return;
        }

        $filters = [
            'type' => trim((string) ($_GET['type'] ?? '')),
            'date_from' => trim((string) ($_GET['date_from'] ?? '')),
            'date_to' => trim((string) ($_GET['date_to'] ?? '')),
        ];

        $cache = new CacheModel();
        $cacheKey = 'api.stats.' . md5(json_encode($filters));
        $cached = $cache->get($cacheKey);
        if ($cached !== null) {
            $this->respondPublicJson($cached, 120);
            return;
        }

        $statModel = new StatModel();
        $rows = $statModel->getAll($filters, 700);
        $series = $statModel->getSeriesForChart($filters);
        $payload = [
            'ok' => true,
            'count' => count($rows),
            'data' => $rows,
            'series' => $series,
        ];
        $json = $this->encode($payload);
        $cache->set($cacheKey, $json, 180);
        $this->respondPublicJson($json, 120);
    }

    public function articles(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->respond(['ok' => false, 'error' => 'Method not allowed'], 405);
            return;
        }

        $limit = max(1, min(50, (int) ($_GET['limit'] ?? 12)));
        $query = trim((string) ($_GET['q'] ?? ''));
        $cache = new CacheModel();
        $cacheKey = 'api.articles.' . md5($limit . '|' . $query);
        $cached = $cache->get($cacheKey);
        if ($cached !== null) {
            $this->respondPublicJson($cached, 90);
            return;
        }

        $articleModel = new ArticleModel();
        $rows = $articleModel->getPublished($limit, 0, $query);
        $payload = [
            'ok' => true,
            'count' => count($rows),
            'data' => array_map(
                static fn(array $row): array => [
                    'id' => (int) ($row['id'] ?? 0),
                    'category_id' => (int) ($row['category_id'] ?? 0),
                    'title' => (string) ($row['title'] ?? ''),
                    'excerpt' => (string) ($row['excerpt'] ?? ''),
                    'slug' => (string) ($row['slug'] ?? ''),
                    'views' => (int) ($row['views'] ?? 0),
                    'reading_time' => (int) ($row['reading_time'] ?? 1),
                    'published_at' => (string) ($row['published_at'] ?? ''),
                ],
                $rows
            ),
        ];
        $json = $this->encode($payload);
        $cache->set($cacheKey, $json, 120);
        $this->respondPublicJson($json, 90);
    }

    public function favorite(): void
    {
        $this->setNoStoreHeaders();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respond(['ok' => false, 'error' => 'Method not allowed'], 405);
            return;
        }

        if (!Session::isSubscriber()) {
            $this->respond(['ok' => false, 'error' => 'Authentication required'], 401);
            return;
        }

        if (!$this->verifyCsrf()) {
            $this->respond(['ok' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }

        $articleId = (int) ($_POST['article_id'] ?? 0);
        if ($articleId <= 0) {
            $this->respond(['ok' => false, 'error' => 'Invalid article'], 422);
            return;
        }

        $articleModel = new ArticleModel();
        $article = $articleModel->getById($articleId);
        if ($article === null || (string) ($article['status'] ?? '') !== 'published') {
            $this->respond(['ok' => false, 'error' => 'Unknown article'], 422);
            return;
        }

        $favoriteModel = new FavoriteModel();
        $userId = (int) Session::get('subscriber_id');
        $isFavorite = $favoriteModel->toggle($userId, $articleId);
        $favoritesCount = $favoriteModel->countForArticle($articleId);

        if ($isFavorite) {
            $notificationModel = new NotificationModel();
            $notificationModel->create(
                $userId,
                'article',
                'Article ajoute a votre liste lire plus tard: ' . (string) ($article['title'] ?? '')
            );
        }

        $this->respond([
            'ok' => true,
            'favorite' => $isFavorite,
            'count' => $favoritesCount,
        ]);
    }

    public function notifications(): void
    {
        $this->setNoStoreHeaders();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->respond(['ok' => false, 'error' => 'Method not allowed'], 405);
            return;
        }

        if (!Session::isSubscriber()) {
            $this->respond(['ok' => false, 'error' => 'Authentication required'], 401);
            return;
        }

        $userId = (int) Session::get('subscriber_id');
        $model = new NotificationModel();
        $rows = $model->getByUser($userId, 20);

        $this->respond([
            'ok' => true,
            'unread_count' => $model->unreadCount($userId),
            'data' => $rows,
        ]);
    }

    public function readNotification(): void
    {
        $this->setNoStoreHeaders();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respond(['ok' => false, 'error' => 'Method not allowed'], 405);
            return;
        }

        if (!Session::isSubscriber()) {
            $this->respond(['ok' => false, 'error' => 'Authentication required'], 401);
            return;
        }

        if (!$this->verifyCsrf()) {
            $this->respond(['ok' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }

        $userId = (int) Session::get('subscriber_id');
        $id = (int) ($_POST['id'] ?? 0);
        $model = new NotificationModel();

        if ($id > 0) {
            $model->markAsRead($userId, $id);
        } else {
            $model->markAllAsRead($userId);
        }

        $this->respond([
            'ok' => true,
            'unread_count' => $model->unreadCount($userId),
        ]);
    }

    public function analytics(): void
    {
        $this->setNoStoreHeaders();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respond(['ok' => false, 'error' => 'Method not allowed'], 405);
            return;
        }

        $page = trim((string) ($_POST['page'] ?? ''));
        $duration = (int) ($_POST['duration'] ?? 0);
        if ($page === '') {
            $this->respond(['ok' => false, 'error' => 'Invalid payload'], 422);
            return;
        }

        try {
            $analyticsModel = new AnalyticsModel();
            $analyticsModel->logPage(
                mb_substr($page, 0, 190),
                Session::isSubscriber() ? (int) Session::get('subscriber_id') : null,
                max(0, $duration)
            );
            $this->respond(['ok' => true]);
            return;
        } catch (Throwable $exception) {
            $this->respond(['ok' => false, 'error' => 'Logging failed'], 500);
            return;
        }
    }

    private function verifyCsrf(): bool
    {
        $sessionToken = (string) ($_SESSION['csrf_token'] ?? '');
        $headerToken = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        $postedToken = (string) ($_POST['csrf_token'] ?? '');
        $token = $headerToken !== '' ? $headerToken : $postedToken;
        return $sessionToken !== '' && $token !== '' && hash_equals($sessionToken, $token);
    }

    private function respond(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo $this->encode($payload);
    }

    private function respondRaw(string $json, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo $json;
    }

    private function respondPublicJson(string $json, int $maxAge = 120): void
    {
        $safeMaxAge = max(0, $maxAge);
        $etag = '"' . sha1($json) . '"';
        $ifNoneMatch = trim((string) ($_SERVER['HTTP_IF_NONE_MATCH'] ?? ''));

        http_response_code(200);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: public, max-age=' . $safeMaxAge . ', stale-while-revalidate=30');
        header('ETag: ' . $etag);
        header('Vary: Accept-Encoding');

        if ($ifNoneMatch !== '' && $this->isEtagMatch($etag, $ifNoneMatch)) {
            http_response_code(304);
            return;
        }

        echo $json;
    }

    private function isEtagMatch(string $etag, string $ifNoneMatch): bool
    {
        $candidates = array_map('trim', explode(',', $ifNoneMatch));
        foreach ($candidates as $candidate) {
            if ($candidate === '*') {
                return true;
            }

            $normalized = str_starts_with($candidate, 'W/')
                ? trim(substr($candidate, 2))
                : $candidate;

            if ($normalized === $etag) {
                return true;
            }
        }

        return false;
    }

    private function setNoStoreHeaders(): void
    {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
    }

    private function encode(array $payload): string
    {
        $json = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
        );

        if ($json === false) {
            return '{"ok":false,"error":"JSON encoding failed"}';
        }

        return $json;
    }
}
