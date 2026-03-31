<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class NotificationModel
{
    private PDO $db;
    private const ALLOWED_TYPES = ['article', 'commentaire', 'debat'];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(int $userId, string $type, string $message): bool
    {
        $type = $this->sanitizeType($type);
        $stmt = $this->db->prepare(
            'INSERT INTO notifications (user_id, type, message, is_read)
             VALUES (:user_id, :type, :message, 0)'
        );
        return $stmt->execute([
            ':user_id' => $userId,
            ':type' => $type,
            ':message' => mb_substr(trim($message), 0, 255),
        ]);
    }

    public function getByUser(int $userId, int $limit = 30): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, type, message, is_read, created_at
             FROM notifications
             WHERE user_id = :user_id
             ORDER BY created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function markAsRead(int $userId, int $notificationId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE notifications
             SET is_read = 1
             WHERE id = :id
               AND user_id = :user_id'
        );
        return $stmt->execute([
            ':id' => $notificationId,
            ':user_id' => $userId,
        ]);
    }

    public function markAllAsRead(int $userId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE notifications
             SET is_read = 1
             WHERE user_id = :user_id
               AND is_read = 0'
        );
        return $stmt->execute([':user_id' => $userId]);
    }

    public function unreadCount(int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
             FROM notifications
             WHERE user_id = :user_id
               AND is_read = 0'
        );
        $stmt->execute([':user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function broadcast(string $type, string $message, bool $premiumOnly = false): int
    {
        $type = $this->sanitizeType($type);
        $message = mb_substr(trim($message), 0, 255);

        $sql = 'INSERT INTO notifications (user_id, type, message, is_read)
                SELECT s.id, :type, :message, 0
                FROM subscribers s
                WHERE s.is_active = 1';

        if ($premiumOnly) {
            $sql .= " AND s.plan = 'premium' AND s.is_subscribed = 1";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':type' => $type,
            ':message' => $message,
        ]);
        return $stmt->rowCount();
    }

    private function sanitizeType(string $type): string
    {
        $type = mb_strtolower(trim($type));
        return in_array($type, self::ALLOWED_TYPES, true) ? $type : 'article';
    }
}
