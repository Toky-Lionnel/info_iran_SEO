<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class EventModel
{
    private PDO $db;
    private const ALLOWED_TYPES = ['militaire', 'politique', 'diplomatique', 'bombardement', 'manifestation'];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(array $filters = [], int $limit = 300): array
    {
        $where = [];
        $params = [];

        $type = trim((string) ($filters['type'] ?? ''));
        if (in_array($type, self::ALLOWED_TYPES, true)) {
            $where[] = 'type = :type';
            $params[':type'] = $type;
        }

        $dateFrom = $this->sanitizeDate((string) ($filters['date_from'] ?? ''));
        if ($dateFrom !== null) {
            $where[] = 'event_date >= :date_from';
            $params[':date_from'] = $dateFrom . ' 00:00:00';
        }

        $dateTo = $this->sanitizeDate((string) ($filters['date_to'] ?? ''));
        if ($dateTo !== null) {
            $where[] = 'event_date <= :date_to';
            $params[':date_to'] = $dateTo . ' 23:59:59';
        }

        $whereSql = $where === [] ? '' : ('WHERE ' . implode(' AND ', $where));
        $stmt = $this->db->prepare(
            "SELECT id, title, description, type, latitude, longitude, city, event_date, created_at
             FROM events
             {$whereSql}
             ORDER BY event_date DESC
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
            'SELECT id, title, description, type, latitude, longitude, city, event_date, created_at
             FROM events
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $type = $this->sanitizeType((string) ($data['type'] ?? 'militaire'));
        $stmt = $this->db->prepare(
            'INSERT INTO events (title, description, type, latitude, longitude, city, event_date)
             VALUES (:title, :description, :type, :latitude, :longitude, :city, :event_date)'
        );
        $stmt->execute([
            ':title' => $this->truncate((string) ($data['title'] ?? ''), 255),
            ':description' => $this->truncate((string) ($data['description'] ?? ''), 5000),
            ':type' => $type,
            ':latitude' => (float) ($data['latitude'] ?? 0),
            ':longitude' => (float) ($data['longitude'] ?? 0),
            ':city' => $this->truncate((string) ($data['city'] ?? ''), 120),
            ':event_date' => $this->sanitizeDateTime((string) ($data['event_date'] ?? '')) ?? date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $type = $this->sanitizeType((string) ($data['type'] ?? 'militaire'));
        $stmt = $this->db->prepare(
            'UPDATE events
             SET title = :title,
                 description = :description,
                 type = :type,
                 latitude = :latitude,
                 longitude = :longitude,
                 city = :city,
                 event_date = :event_date
             WHERE id = :id'
        );

        return $stmt->execute([
            ':title' => $this->truncate((string) ($data['title'] ?? ''), 255),
            ':description' => $this->truncate((string) ($data['description'] ?? ''), 5000),
            ':type' => $type,
            ':latitude' => (float) ($data['latitude'] ?? 0),
            ':longitude' => (float) ($data['longitude'] ?? 0),
            ':city' => $this->truncate((string) ($data['city'] ?? ''), 120),
            ':event_date' => $this->sanitizeDateTime((string) ($data['event_date'] ?? '')) ?? date('Y-m-d H:i:s'),
            ':id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM events WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function getAllowedTypes(): array
    {
        return self::ALLOWED_TYPES;
    }

    private function sanitizeType(string $type): string
    {
        $type = mb_strtolower(trim($type));
        return in_array($type, self::ALLOWED_TYPES, true) ? $type : 'militaire';
    }

    private function sanitizeDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '' || preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return null;
        }
        return $value;
    }

    private function sanitizeDateTime(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
            return $value . ' 00:00:00';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}(?::\d{2})?$/', $value) === 1) {
            return strlen($value) === 16 ? ($value . ':00') : $value;
        }
        return null;
    }

    private function truncate(string $value, int $max): string
    {
        return mb_substr(trim($value), 0, $max);
    }
}
