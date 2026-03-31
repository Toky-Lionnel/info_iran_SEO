<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class FavoriteModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function isFavorite(int $userId, int $articleId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1
             FROM article_favorites
             WHERE user_id = :user_id
               AND article_id = :article_id
             LIMIT 1'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':article_id' => $articleId,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function toggle(int $userId, int $articleId): bool
    {
        if ($this->isFavorite($userId, $articleId)) {
            $stmt = $this->db->prepare(
                'DELETE FROM article_favorites
                 WHERE user_id = :user_id
                   AND article_id = :article_id'
            );
            $stmt->execute([
                ':user_id' => $userId,
                ':article_id' => $articleId,
            ]);
            return false;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO article_favorites (user_id, article_id)
             VALUES (:user_id, :article_id)'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':article_id' => $articleId,
        ]);
        return true;
    }

    public function getUserFavorites(int $userId, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            "SELECT f.id, f.created_at, a.id AS article_id, a.category_id, a.title, a.excerpt, a.slug, a.cover_image, a.published_at
             FROM article_favorites f
             JOIN articles a ON a.id = f.article_id
             WHERE f.user_id = :user_id
               AND a.status = 'published'
             ORDER BY f.created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countForArticle(int $articleId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM article_favorites WHERE article_id = :article_id');
        $stmt->execute([':article_id' => $articleId]);
        return (int) $stmt->fetchColumn();
    }
}
