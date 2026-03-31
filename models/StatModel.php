<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class StatModel
{
    private PDO $db;
    private const ALLOWED_TYPES = ['pertes', 'deplacements', 'sanctions'];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(array $filters = [], int $limit = 400): array
    {
        $where = [];
        $params = [];

        $type = trim((string) ($filters['type'] ?? ''));
        if (in_array($type, self::ALLOWED_TYPES, true)) {
            $where[] = 'type = :type';
            $params[':type'] = $type;
        }

        $from = $this->sanitizeDate((string) ($filters['date_from'] ?? ''));
        if ($from !== null) {
            $where[] = 'stat_date >= :date_from';
            $params[':date_from'] = $from;
        }

        $to = $this->sanitizeDate((string) ($filters['date_to'] ?? ''));
        if ($to !== null) {
            $where[] = 'stat_date <= :date_to';
            $params[':date_to'] = $to;
        }

        $whereSql = $where === [] ? '' : ('WHERE ' . implode(' AND ', $where));
        $stmt = $this->db->prepare(
            "SELECT id, type, value, stat_date, created_at
             FROM stats
             {$whereSql}
             ORDER BY stat_date ASC
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
            'SELECT id, type, value, stat_date, created_at
             FROM stats
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function upsert(array $data): bool
    {
        $type = $this->sanitizeType((string) ($data['type'] ?? 'pertes'));
        $statDate = $this->sanitizeDate((string) ($data['stat_date'] ?? '')) ?? date('Y-m-d');

        $stmt = $this->db->prepare(
            'INSERT INTO stats (type, value, stat_date)
             VALUES (:type, :value, :stat_date)
             ON DUPLICATE KEY UPDATE value = VALUES(value)'
        );

        return $stmt->execute([
            ':type' => $type,
            ':value' => max(0, (int) ($data['value'] ?? 0)),
            ':stat_date' => $statDate,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE stats
             SET type = :type,
                 value = :value,
                 stat_date = :stat_date
             WHERE id = :id'
        );

        return $stmt->execute([
            ':type' => $this->sanitizeType((string) ($data['type'] ?? 'pertes')),
            ':value' => max(0, (int) ($data['value'] ?? 0)),
            ':stat_date' => $this->sanitizeDate((string) ($data['stat_date'] ?? '')) ?? date('Y-m-d'),
            ':id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM stats WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function getSeriesForChart(array $filters = []): array
    {
        $rows = $this->getAll($filters, 1000);
        $series = [
            'labels' => [],
            'pertes' => [],
            'deplacements' => [],
            'sanctions' => [],
        ];

        $bucket = [];
        foreach ($rows as $row) {
            $date = (string) ($row['stat_date'] ?? '');
            if ($date === '') {
                continue;
            }
            if (!isset($bucket[$date])) {
                $bucket[$date] = ['pertes' => 0, 'deplacements' => 0, 'sanctions' => 0];
            }
            $type = (string) ($row['type'] ?? '');
            if (isset($bucket[$date][$type])) {
                $bucket[$date][$type] = (int) ($row['value'] ?? 0);
            }
        }

        ksort($bucket);
        foreach ($bucket as $date => $values) {
            $series['labels'][] = $date;
            $series['pertes'][] = $values['pertes'];
            $series['deplacements'][] = $values['deplacements'];
            $series['sanctions'][] = $values['sanctions'];
        }

        return $series;
    }

    public function getAllowedTypes(): array
    {
        return self::ALLOWED_TYPES;
    }

    private function sanitizeType(string $type): string
    {
        $type = mb_strtolower(trim($type));
        return in_array($type, self::ALLOWED_TYPES, true) ? $type : 'pertes';
    }

    private function sanitizeDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '' || preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return null;
        }
        return $value;
    }
}
