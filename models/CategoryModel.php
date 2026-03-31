<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class CategoryModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        $sql = 'SELECT c.id, c.slug, c.name, c.color, c.created_at, COUNT(a.id) AS article_count
                FROM categories c
                LEFT JOIN articles a ON a.category_id = c.id
                GROUP BY c.id, c.slug, c.name, c.color, c.created_at
                ORDER BY c.name ASC';
        return $this->db->query($sql)->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE slug = :slug LIMIT 1');
        $stmt->execute([':slug' => $slug]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM categories WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO categories (slug, name, color) VALUES (:slug, :name, :color)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':slug' => $data['slug'],
            ':name' => $data['name'],
            ':color' => $data['color'] ?? '#C62828',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE categories
                SET slug = :slug, name = :name, color = :color
                WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':slug' => $data['slug'],
            ':name' => $data['name'],
            ':color' => $data['color'] ?? '#C62828',
            ':id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM categories WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM categories')->fetchColumn();
    }
}
