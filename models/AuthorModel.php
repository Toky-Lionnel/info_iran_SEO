<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class AuthorModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        $sql = 'SELECT a.id, a.slug, a.name, a.bio, a.avatar_url, a.created_at, COUNT(ar.id) AS article_count
                FROM authors a
                LEFT JOIN articles ar ON ar.author_id = a.id
                GROUP BY a.id, a.slug, a.name, a.bio, a.avatar_url, a.created_at
                ORDER BY a.name ASC';
        return $this->db->query($sql)->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM authors WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM authors WHERE slug = :slug LIMIT 1');
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO authors (slug, name, bio, avatar_url) VALUES (:slug, :name, :bio, :avatar_url)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':slug' => $data['slug'],
            ':name' => $data['name'],
            ':bio' => $data['bio'] ?? null,
            ':avatar_url' => $data['avatar_url'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE authors SET slug = :slug, name = :name, bio = :bio, avatar_url = :avatar_url WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':slug' => $data['slug'],
            ':name' => $data['name'],
            ':bio' => $data['bio'] ?? null,
            ':avatar_url' => $data['avatar_url'] ?? null,
            ':id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM authors WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
}
