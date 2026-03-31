<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class ArticleModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getPublished(int $limit = 10, int $offset = 0, ?string $search = null): array
    {
        $sql = "SELECT a.*, c.name AS cat_name, c.slug AS cat_slug, c.color AS cat_color,
                       au.name AS author_name, au.slug AS author_slug
                FROM articles a
                JOIN categories c ON c.id = a.category_id
                JOIN authors au ON au.id = a.author_id
                WHERE a.status = 'published'";

        if ($search !== null && $search !== '') {
            $sql .= ' AND (a.title LIKE :query OR a.excerpt LIKE :query OR a.content LIKE :query)';
        }

        $sql .= ' ORDER BY a.published_at DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->db->prepare($sql);

        if ($search !== null && $search !== '') {
            $stmt->bindValue(':query', '%' . $search . '%', PDO::PARAM_STR);
        }

        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getBySlug(string $slug): ?array
    {
        $sql = "SELECT a.*, c.name AS cat_name, c.slug AS cat_slug, c.color AS cat_color,
                       au.name AS author_name, au.slug AS author_slug
                FROM articles a
                JOIN categories c ON c.id = a.category_id
                JOIN authors au ON au.id = a.author_id
                WHERE a.slug = :slug
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getPublishedByIdAndCategory(int $id, int $categoryId): ?array
    {
        $sql = "SELECT a.*, c.name AS cat_name, c.slug AS cat_slug, c.color AS cat_color,
                       au.name AS author_name, au.slug AS author_slug
                FROM articles a
                JOIN categories c ON c.id = a.category_id
                JOIN authors au ON au.id = a.author_id
                WHERE a.id = :id
                  AND a.category_id = :category_id
                  AND a.status = 'published'
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':category_id' => $categoryId,
        ]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getByCategorySlug(string $catSlug, int $limit, int $offset): array
    {
        $sql = "SELECT a.*, c.name AS cat_name, c.slug AS cat_slug, c.color AS cat_color,
                       au.name AS author_name, au.slug AS author_slug
                FROM articles a
                JOIN categories c ON c.id = a.category_id
                JOIN authors au ON au.id = a.author_id
                WHERE c.slug = :slug
                  AND a.status = 'published'
                ORDER BY a.published_at DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':slug', $catSlug, PDO::PARAM_STR);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByCategoryId(int $categoryId, int $limit, int $offset): array
    {
        $sql = "SELECT a.*, c.name AS cat_name, c.slug AS cat_slug, c.color AS cat_color,
                       au.name AS author_name, au.slug AS author_slug
                FROM articles a
                JOIN categories c ON c.id = a.category_id
                JOIN authors au ON au.id = a.author_id
                WHERE a.category_id = :category_id
                  AND a.status = 'published'
                ORDER BY a.published_at DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function incrementViews(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE articles SET views = views + 1 WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public function countPublished(?string $search = null): int
    {
        $sql = "SELECT COUNT(*) FROM articles WHERE status = 'published'";
        if ($search !== null && $search !== '') {
            $sql .= ' AND (title LIKE :query OR excerpt LIKE :query OR content LIKE :query)';
        }

        $stmt = $this->db->prepare($sql);
        if ($search !== null && $search !== '') {
            $stmt->bindValue(':query', '%' . $search . '%', PDO::PARAM_STR);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function countByCategory(string $catSlug): int
    {
        $sql = "SELECT COUNT(*)
                FROM articles a
                JOIN categories c ON c.id = a.category_id
                WHERE c.slug = :slug
                  AND a.status = 'published'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':slug' => $catSlug]);
        return (int) $stmt->fetchColumn();
    }

    public function countByCategoryId(int $categoryId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM articles WHERE category_id = :category_id AND status = 'published'"
        );
        $stmt->execute([':category_id' => $categoryId]);
        return (int) $stmt->fetchColumn();
    }

    public function countAllByCategoryId(int $categoryId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM articles WHERE category_id = :category_id');
        $stmt->execute([':category_id' => $categoryId]);
        return (int) $stmt->fetchColumn();
    }

    public function getAll(?string $status = null, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT a.*, c.name AS cat_name, c.color AS cat_color, au.name AS author_name
                FROM articles a
                JOIN categories c ON c.id = a.category_id
                JOIN authors au ON au.id = a.author_id";

        if ($status !== null && $status !== '' && in_array($status, ['draft', 'published', 'archived'], true)) {
            $sql .= ' WHERE a.status = :status';
        }

        $sql .= ' ORDER BY COALESCE(a.published_at, a.created_at) DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->db->prepare($sql);

        if ($status !== null && $status !== '' && in_array($status, ['draft', 'published', 'archived'], true)) {
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        }

        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countAll(?string $status = null): int
    {
        $sql = 'SELECT COUNT(*) FROM articles';
        if ($status !== null && $status !== '' && in_array($status, ['draft', 'published', 'archived'], true)) {
            $sql .= ' WHERE status = :status';
        }

        $stmt = $this->db->prepare($sql);
        if ($status !== null && $status !== '' && in_array($status, ['draft', 'published', 'archived'], true)) {
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        }
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function getById(int $id): ?array
    {
        $sql = "SELECT a.*, c.name AS cat_name, c.slug AS cat_slug, c.color AS cat_color,
                       au.name AS author_name, au.slug AS author_slug
                FROM articles a
                JOIN categories c ON c.id = a.category_id
                JOIN authors au ON au.id = a.author_id
                WHERE a.id = :id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $slug = trim((string) ($data['slug'] ?? ''));
        if ($slug === '') {
            $slug = $this->generateSlug((string) $data['title']);
        }

        $status = $this->sanitizeStatus((string) ($data['status'] ?? 'draft'));
        $publishedAt = $data['published_at'] ?? null;
        if ($status === 'published' && empty($publishedAt)) {
            $publishedAt = date('Y-m-d H:i:s');
        }

        $sql = "INSERT INTO articles (
                    slug, title, excerpt, content, cover_image, cover_alt,
                    category_id, author_id, status, reading_time, published_at
                ) VALUES (
                    :slug, :title, :excerpt, :content, :cover_image, :cover_alt,
                    :category_id, :author_id, :status, :reading_time, :published_at
                )";
        $stmt = $this->db->prepare($sql);
        $readingTime = $this->estimateReadingTime((string) $data['content']);
        $stmt->execute([
            ':slug' => $slug,
            ':title' => $data['title'],
            ':excerpt' => $data['excerpt'],
            ':content' => $data['content'],
            ':cover_image' => $data['cover_image'] ?: null,
            ':cover_alt' => $data['cover_alt'],
            ':category_id' => (int) $data['category_id'],
            ':author_id' => (int) ($data['author_id'] ?? 1),
            ':status' => $status,
            ':reading_time' => $readingTime,
            ':published_at' => $publishedAt,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $current = $this->getById($id);
        if ($current === null) {
            return false;
        }

        $slug = trim((string) ($data['slug'] ?? ''));
        if ($slug === '') {
            $slug = $this->generateSlug((string) $data['title'], $id);
        }

        $status = $this->sanitizeStatus((string) ($data['status'] ?? $current['status']));
        $publishedAt = $data['published_at'] ?? $current['published_at'];
        if ($status === 'published' && empty($publishedAt)) {
            $publishedAt = date('Y-m-d H:i:s');
        }

        $sql = "UPDATE articles
                SET slug = :slug,
                    title = :title,
                    excerpt = :excerpt,
                    content = :content,
                    cover_image = :cover_image,
                    cover_alt = :cover_alt,
                    category_id = :category_id,
                    author_id = :author_id,
                    status = :status,
                    reading_time = :reading_time,
                    published_at = :published_at
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $readingTime = $this->estimateReadingTime((string) $data['content']);

        return $stmt->execute([
            ':slug' => $slug,
            ':title' => $data['title'],
            ':excerpt' => $data['excerpt'],
            ':content' => $data['content'],
            ':cover_image' => $data['cover_image'] ?: null,
            ':cover_alt' => $data['cover_alt'],
            ':category_id' => (int) $data['category_id'],
            ':author_id' => (int) ($data['author_id'] ?? 1),
            ':status' => $status,
            ':reading_time' => $readingTime,
            ':published_at' => $publishedAt,
            ':id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM articles WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $status = $this->sanitizeStatus($status);
        $publishedAtSql = $status === 'published' ? ', published_at = COALESCE(published_at, NOW())' : '';

        $sql = "UPDATE articles SET status = :status {$publishedAtSql} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':id' => $id,
        ]);
    }

    public function generateSlug(string $title, int $excludeId = 0): string
    {
        $slug = strtolower(trim($title));
        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug);
        if ($transliterated !== false) {
            $slug = $transliterated;
        }

        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');
        if ($slug === '') {
            $slug = 'article-' . date('YmdHis');
        }

        $base = substr($slug, 0, 190);
        $candidate = $base;
        $counter = 2;

        while ($this->slugExists($candidate, $excludeId)) {
            $suffix = '-' . $counter;
            $candidate = substr($base, 0, 190 - strlen($suffix)) . $suffix;
            $counter++;
        }

        return $candidate;
    }

    public function getRecent(int $limit = 5): array
    {
        $sql = "SELECT a.*, c.name AS cat_name, c.color AS cat_color
                FROM articles a
                JOIN categories c ON c.id = a.category_id
                ORDER BY COALESCE(a.published_at, a.created_at) DESC
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getDashboardRows(array $filters = [], int $limit = 20): array
    {
        $where = [];
        $params = [];

        $query = trim((string) ($filters['q'] ?? ''));
        if ($query !== '') {
            $where[] = '(a.title LIKE :query OR a.excerpt LIKE :query)';
            $params[':query'] = '%' . $query . '%';
        }

        $status = trim((string) ($filters['status'] ?? ''));
        if (in_array($status, ['draft', 'published', 'archived'], true)) {
            $where[] = 'a.status = :status';
            $params[':status'] = $status;
        }

        $categoryId = (int) ($filters['category_id'] ?? 0);
        if ($categoryId > 0) {
            $where[] = 'a.category_id = :category_id';
            $params[':category_id'] = $categoryId;
        }

        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        if ($dateFrom !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom) === 1) {
            $where[] = 'DATE(COALESCE(a.published_at, a.created_at)) >= :date_from';
            $params[':date_from'] = $dateFrom;
        }

        $dateTo = trim((string) ($filters['date_to'] ?? ''));
        if ($dateTo !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo) === 1) {
            $where[] = 'DATE(COALESCE(a.published_at, a.created_at)) <= :date_to';
            $params[':date_to'] = $dateTo;
        }

        $sort = trim((string) ($filters['sort'] ?? 'latest'));
        $orderBy = 'COALESCE(a.published_at, a.created_at) DESC';
        if ($sort === 'views') {
            $orderBy = 'a.views DESC';
        } elseif ($sort === 'comments') {
            $orderBy = 'comments_count DESC';
        } elseif ($sort === 'shares') {
            $orderBy = 'shares_count DESC';
        }

        $whereSql = $where === [] ? '' : ('WHERE ' . implode(' AND ', $where));
        $sql = "SELECT a.id, a.title, a.status, a.views, a.published_at, a.created_at, a.category_id,
                       c.name AS cat_name, c.color AS cat_color,
                       au.name AS author_name,
                       COUNT(DISTINCT CASE WHEN ac.status = 'approved' THEN ac.id END) AS comments_count,
                       COUNT(DISTINCT sh.id) AS shares_count
                FROM articles a
                JOIN categories c ON c.id = a.category_id
                JOIN authors au ON au.id = a.author_id
                LEFT JOIN article_comments ac ON ac.article_id = a.id
                LEFT JOIN article_shares sh ON sh.article_id = a.id
                {$whereSql}
                GROUP BY a.id, a.title, a.status, a.views, a.published_at, a.created_at, a.category_id,
                         c.name, c.color, au.name
                ORDER BY {$orderBy}
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            if ($key === ':category_id') {
                $stmt->bindValue($key, (int) $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }

        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getArchiveBuckets(): array
    {
        $sql = "SELECT YEAR(published_at) AS year_num,
                       MONTH(published_at) AS month_num,
                       COUNT(*) AS article_count
                FROM articles
                WHERE status = 'published'
                  AND published_at IS NOT NULL
                GROUP BY YEAR(published_at), MONTH(published_at)
                ORDER BY year_num DESC, month_num DESC";

        return $this->db->query($sql)->fetchAll();
    }

    public function getPublishedByYearMonth(int $year, int $month, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT a.*, c.name AS cat_name, c.slug AS cat_slug, c.color AS cat_color,
                       au.name AS author_name, au.slug AS author_slug
                FROM articles a
                JOIN categories c ON c.id = a.category_id
                JOIN authors au ON au.id = a.author_id
                WHERE a.status = 'published'
                  AND YEAR(a.published_at) = :year_num
                  AND MONTH(a.published_at) = :month_num
                ORDER BY a.published_at DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':year_num', $year, PDO::PARAM_INT);
        $stmt->bindValue(':month_num', $month, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getStats(): array
    {
        $sql = "SELECT
                    SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) AS published_articles,
                    SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS draft_articles,
                    COALESCE(SUM(views), 0) AS total_views
                FROM articles";
        $stats = $this->db->query($sql)->fetch();

        return [
            'published_articles' => (int) ($stats['published_articles'] ?? 0),
            'draft_articles' => (int) ($stats['draft_articles'] ?? 0),
            'total_views' => (int) ($stats['total_views'] ?? 0),
        ];
    }

    private function sanitizeStatus(string $status): string
    {
        $allowed = ['draft', 'published', 'archived'];
        return in_array($status, $allowed, true) ? $status : 'draft';
    }

    private function slugExists(string $slug, int $excludeId = 0): bool
    {
        $sql = 'SELECT 1 FROM articles WHERE slug = :slug';
        if ($excludeId > 0) {
            $sql .= ' AND id <> :exclude_id';
        }
        $sql .= ' LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
        if ($excludeId > 0) {
            $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }

    private function estimateReadingTime(string $content): int
    {
        $plain = trim(strip_tags($content));
        if ($plain === '') {
            return 1;
        }

        $wordCount = preg_match_all('/\b[\p{L}\p{N}\']+\b/u', $plain, $matches);
        $words = max(1, (int) $wordCount);
        $minutes = (int) ceil($words / 220);
        return max(1, min(60, $minutes));
    }
}
