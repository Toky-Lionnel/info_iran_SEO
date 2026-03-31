<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class TimelineEventModel
{
    private PDO $db;
    private const ALLOWED_CATEGORIES = ['militaire', 'politique', 'diplomatique'];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(array $filters = [], int $limit = 500): array
    {
        $where = [];
        $params = [];

        $category = trim((string) ($filters['category'] ?? ''));
        if (in_array($category, self::ALLOWED_CATEGORIES, true)) {
            $where[] = 'category = :category';
            $params[':category'] = $category;
        }

        $from = $this->sanitizeDate((string) ($filters['date_from'] ?? ''));
        if ($from !== null) {
            $where[] = 'event_date >= :date_from';
            $params[':date_from'] = $from;
        }

        $to = $this->sanitizeDate((string) ($filters['date_to'] ?? ''));
        if ($to !== null) {
            $where[] = 'event_date <= :date_to';
            $params[':date_to'] = $to;
        }

        $whereSql = $where === [] ? '' : ('WHERE ' . implode(' AND ', $where));
        $stmt = $this->db->prepare(
            "SELECT id, title, description, category, event_date, created_at
             FROM timeline_events
             {$whereSql}
             ORDER BY event_date ASC
             LIMIT :limit"
        );

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, title, description, category, event_date, created_at
             FROM timeline_events
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO timeline_events (title, description, category, event_date)
             VALUES (:title, :description, :category, :event_date)'
        );
        $stmt->execute([
            ':title' => $this->truncate((string) ($data['title'] ?? ''), 255),
            ':description' => $this->truncate((string) ($data['description'] ?? ''), 5000),
            ':category' => $this->sanitizeCategory((string) ($data['category'] ?? 'militaire')),
            ':event_date' => $this->sanitizeDate((string) ($data['event_date'] ?? '')) ?? date('Y-m-d'),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE timeline_events
             SET title = :title,
                 description = :description,
                 category = :category,
                 event_date = :event_date
             WHERE id = :id'
        );

        return $stmt->execute([
            ':title' => $this->truncate((string) ($data['title'] ?? ''), 255),
            ':description' => $this->truncate((string) ($data['description'] ?? ''), 5000),
            ':category' => $this->sanitizeCategory((string) ($data['category'] ?? 'militaire')),
            ':event_date' => $this->sanitizeDate((string) ($data['event_date'] ?? '')) ?? date('Y-m-d'),
            ':id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM timeline_events WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function getAllowedCategories(): array
    {
        return self::ALLOWED_CATEGORIES;
    }

    private function sanitizeCategory(string $category): string
    {
        $category = mb_strtolower(trim($category));
        return in_array($category, self::ALLOWED_CATEGORIES, true) ? $category : 'militaire';
    }

    private function sanitizeDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '' || preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return null;
        }
        return $value;
    }

    private function truncate(string $value, int $max): string
    {
        return mb_substr(trim($value), 0, $max);
    }
}
