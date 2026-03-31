<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Helpers;
use PDO;
use Throwable;

final class TagModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        $sql = 'SELECT t.id, t.slug, t.name, COUNT(at.article_id) AS article_count
                FROM tags t
                LEFT JOIN article_tags at ON at.tag_id = t.id
                GROUP BY t.id, t.slug, t.name
                ORDER BY t.name ASC';
        return $this->db->query($sql)->fetchAll();
    }

    public function getByArticle(int $articleId): array
    {
        $stmt = $this->db->prepare(
            'SELECT t.id, t.slug, t.name
             FROM tags t
             JOIN article_tags at ON at.tag_id = t.id
             WHERE at.article_id = :article_id
             ORDER BY t.name ASC'
        );
        $stmt->execute([':article_id' => $articleId]);
        return $stmt->fetchAll();
    }

    public function syncTags(int $articleId, array $tags): void
    {
        $normalizedTags = $this->normalizeTagList($tags);

        $this->db->beginTransaction();
        try {
            $deleteStmt = $this->db->prepare('DELETE FROM article_tags WHERE article_id = :article_id');
            $deleteStmt->execute([':article_id' => $articleId]);

            if ($normalizedTags !== []) {
                $insertPivotStmt = $this->db->prepare(
                    'INSERT INTO article_tags (article_id, tag_id) VALUES (:article_id, :tag_id)'
                );

                foreach ($normalizedTags as $name) {
                    $tagId = $this->findOrCreateTagId($name);
                    $insertPivotStmt->execute([
                        ':article_id' => $articleId,
                        ':tag_id' => $tagId,
                    ]);
                }
            }

            $this->db->commit();
        } catch (Throwable $throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $throwable;
        }
    }

    private function normalizeTagList(array $tags): array
    {
        $normalized = [];
        foreach ($tags as $tag) {
            $name = trim((string) $tag);
            if ($name === '') {
                continue;
            }
            $normalized[] = mb_substr($name, 0, 100);
        }

        $normalized = array_values(array_unique($normalized));
        sort($normalized, SORT_NATURAL | SORT_FLAG_CASE);
        return $normalized;
    }

    private function findOrCreateTagId(string $name): int
    {
        $slug = Helpers::slugify($name, 80);

        $selectStmt = $this->db->prepare('SELECT id FROM tags WHERE slug = :slug LIMIT 1');
        $selectStmt->execute([':slug' => $slug]);
        $existingId = $selectStmt->fetchColumn();
        if ($existingId !== false) {
            return (int) $existingId;
        }

        $insertStmt = $this->db->prepare('INSERT INTO tags (slug, name) VALUES (:slug, :name)');
        $insertStmt->execute([
            ':slug' => $slug,
            ':name' => $name,
        ]);

        return (int) $this->db->lastInsertId();
    }
}
