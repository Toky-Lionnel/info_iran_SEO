<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class SecurityLogModel
{
    private PDO $db;
    private const ALLOWED_STATUS = ['success', 'failed', 'blocked'];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function log(string $ip, string $action, string $status): bool
    {
        $status = mb_strtolower(trim($status));
        if (!in_array($status, self::ALLOWED_STATUS, true)) {
            $status = 'failed';
        }

        $stmt = $this->db->prepare(
            'INSERT INTO security_logs (ip, action, status)
             VALUES (:ip, :action, :status)'
        );
        return $stmt->execute([
            ':ip' => mb_substr(trim($ip), 0, 45),
            ':action' => mb_substr(trim($action), 0, 120),
            ':status' => $status,
        ]);
    }

    public function registerFailedAndMaybeBlock(string $ip, string $action, int $threshold = 6, int $windowMinutes = 15): bool
    {
        $this->log($ip, $action, 'failed');
        $failed = $this->countRecentFailedAttempts($ip, $action, $windowMinutes);
        if ($failed >= max(1, $threshold)) {
            $this->log($ip, $action, 'blocked');
            return true;
        }
        return false;
    }

    public function countRecentFailedAttempts(string $ip, string $action, int $windowMinutes = 15): int
    {
        $minutes = max(1, $windowMinutes);
        $stmt = $this->db->prepare(
            "SELECT COUNT(*)
             FROM security_logs
             WHERE ip = :ip
               AND action = :action
               AND status = :status
               AND created_at >= DATE_SUB(NOW(), INTERVAL {$minutes} MINUTE)"
        );
        $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
        $stmt->bindValue(':action', $action, PDO::PARAM_STR);
        $stmt->bindValue(':status', 'failed', PDO::PARAM_STR);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function isBlocked(string $ip, string $action, int $windowMinutes = 60): bool
    {
        $minutes = max(1, $windowMinutes);
        $stmt = $this->db->prepare(
            "SELECT 1
             FROM security_logs
             WHERE ip = :ip
               AND action = :action
               AND status = :status
               AND created_at >= DATE_SUB(NOW(), INTERVAL {$minutes} MINUTE)
             LIMIT 1"
        );
        $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
        $stmt->bindValue(':action', $action, PDO::PARAM_STR);
        $stmt->bindValue(':status', 'blocked', PDO::PARAM_STR);
        $stmt->execute();
        return (bool) $stmt->fetchColumn();
    }

    public function getRecentLogs(int $limit = 150): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, ip, action, status, created_at
             FROM security_logs
             ORDER BY created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getSuspiciousIps(int $windowMinutes = 60, int $limit = 30): array
    {
        $minutes = max(1, $windowMinutes);
        $stmt = $this->db->prepare(
            "SELECT ip,
                    COUNT(*) AS failed_count,
                    MAX(created_at) AS last_seen
             FROM security_logs
             WHERE status = 'failed'
               AND created_at >= DATE_SUB(NOW(), INTERVAL {$minutes} MINUTE)
             GROUP BY ip
             HAVING failed_count >= 3
             ORDER BY failed_count DESC, last_seen DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function resolveClientIp(): string
    {
        $serverIp = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        $forwarded = trim((string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''));
        if ($forwarded !== '') {
            $first = trim(explode(',', $forwarded)[0]);
            if (filter_var($first, FILTER_VALIDATE_IP) !== false) {
                return $first;
            }
        }
        return filter_var($serverIp, FILTER_VALIDATE_IP) !== false ? $serverIp : '0.0.0.0';
    }
}
