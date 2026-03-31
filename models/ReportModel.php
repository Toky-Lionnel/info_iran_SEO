<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class ReportModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function logExchange(
        ?int $adminUserId,
        string $exchangeType,
        string $dataset,
        ?string $fileName,
        int $rowsCount,
        string $status,
        ?string $notes = null
    ): bool {
        if (!in_array($exchangeType, ['export', 'import'], true)) {
            return false;
        }
        if (!in_array(
            $dataset,
            ['articles', 'subscribers', 'contacts', 'comments', 'reviews', 'events', 'timeline', 'stats', 'analytics', 'security', 'favorites', 'notifications'],
            true
        )) {
            return false;
        }
        if (!in_array($status, ['success', 'failed'], true)) {
            return false;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO data_exchange_logs (admin_user_id, exchange_type, dataset, file_name, rows_count, status, notes)
             VALUES (:admin_user_id, :exchange_type, :dataset, :file_name, :rows_count, :status, :notes)'
        );

        return $stmt->execute([
            ':admin_user_id' => $adminUserId,
            ':exchange_type' => $exchangeType,
            ':dataset' => $dataset,
            ':file_name' => $fileName,
            ':rows_count' => max(0, $rowsCount),
            ':status' => $status,
            ':notes' => $notes,
        ]);
    }

    public function getRecentLogs(int $limit = 30): array
    {
        $stmt = $this->db->prepare(
            "SELECT l.id, l.exchange_type, l.dataset, l.file_name, l.rows_count, l.status, l.notes, l.created_at,
                    u.username AS admin_username
             FROM data_exchange_logs l
             LEFT JOIN admin_users u ON u.id = l.admin_user_id
             ORDER BY l.created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
