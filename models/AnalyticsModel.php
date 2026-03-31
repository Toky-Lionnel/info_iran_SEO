<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class AnalyticsModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function logPage(string $page, ?int $userId, int $duration = 0): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO analytics (page, user_id, duration)
             VALUES (:page, :user_id, :duration)'
        );
        return $stmt->execute([
            ':page' => mb_substr(trim($page), 0, 190),
            ':user_id' => $userId,
            ':duration' => max(0, $duration),
        ]);
    }

    public function getTopPages(int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT page,
                    COUNT(*) AS visits,
                    ROUND(AVG(duration), 1) AS avg_duration
             FROM analytics
             GROUP BY page
             ORDER BY visits DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getDailyTraffic(int $days = 30): array
    {
        $days = max(1, $days);
        $stmt = $this->db->prepare(
            "SELECT DATE(created_at) AS day_label, COUNT(*) AS visits
             FROM analytics
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL {$days} DAY)
             GROUP BY DATE(created_at)
             ORDER BY day_label ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAverageReadingDuration(): float
    {
        $avg = $this->db->query(
            "SELECT COALESCE(AVG(duration), 0)
             FROM analytics
             WHERE page LIKE '/article-%'"
        )->fetchColumn();

        return (float) $avg;
    }

    public function getSubscriberConversionRate(): float
    {
        $active = (int) $this->db->query('SELECT COUNT(*) FROM subscribers WHERE is_active = 1')->fetchColumn();
        if ($active === 0) {
            return 0.0;
        }

        $premium = (int) $this->db->query(
            "SELECT COUNT(*)
             FROM subscribers
             WHERE is_active = 1
               AND plan = 'premium'
               AND is_subscribed = 1"
        )->fetchColumn();

        return round(($premium / $active) * 100, 2);
    }

    public function getMostReadArticles(int $limit = 8): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, category_id, title, views, reading_time, published_at
             FROM articles
             WHERE status = 'published'
             ORDER BY views DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
