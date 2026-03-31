<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class CommunityModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getPublishedJournals(int $limit = 8): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, slug, title, summary, content, published_at
             FROM pinned_journals
             WHERE status = 'published'
             ORDER BY pinned_order ASC, COALESCE(published_at, created_at) DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllJournals(int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, slug, title, summary, status, pinned_order, published_at, created_at
             FROM pinned_journals
             ORDER BY pinned_order ASC, created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getPublishedDebates(int $limit = 8): array
    {
        $stmt = $this->db->prepare(
            "SELECT d.id, d.slug, d.title, d.summary, d.status, d.is_pinned, d.created_at,
                    COUNT(dc.id) AS comments_count
             FROM debates d
             LEFT JOIN debate_comments dc
                    ON dc.debate_id = d.id AND dc.status = 'approved'
             WHERE d.status IN ('open', 'closed')
             GROUP BY d.id, d.slug, d.title, d.summary, d.status, d.is_pinned, d.created_at
             ORDER BY d.is_pinned DESC, d.created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllDebates(int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            "SELECT d.id, d.slug, d.title, d.summary, d.status, d.is_pinned, d.created_at,
                    COUNT(dc.id) AS comments_count
             FROM debates d
             LEFT JOIN debate_comments dc
                    ON dc.debate_id = d.id AND dc.status = 'approved'
             GROUP BY d.id, d.slug, d.title, d.summary, d.status, d.is_pinned, d.created_at
             ORDER BY d.is_pinned DESC, d.created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getDebateBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, slug, title, summary, body, status, is_pinned, created_at
             FROM debates
             WHERE slug = :slug
             LIMIT 1"
        );
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getDebateComments(int $debateId, int $limit = 100): array
    {
        $stmt = $this->db->prepare(
            "SELECT dc.id, dc.author_name, dc.content, dc.created_at, s.full_name AS subscriber_name,
                    COALESCE(SUM(cv.vote), 0) AS vote_score
             FROM debate_comments dc
             LEFT JOIN subscribers s ON s.id = dc.subscriber_id
             LEFT JOIN comment_votes cv ON cv.comment_type = 'debat' AND cv.comment_id = dc.id
             WHERE dc.debate_id = :debate_id
               AND dc.status = 'approved'
             GROUP BY dc.id, dc.author_name, dc.content, dc.created_at, s.full_name
             ORDER BY dc.created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':debate_id', $debateId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function createDebateComment(
        int $debateId,
        ?int $subscriberId,
        string $authorName,
        string $content,
        string $status = 'pending'
    ): bool {
        $stmt = $this->db->prepare(
            'INSERT INTO debate_comments (debate_id, subscriber_id, author_name, content, status)
             VALUES (:debate_id, :subscriber_id, :author_name, :content, :status)'
        );
        return $stmt->execute([
            ':debate_id' => $debateId,
            ':subscriber_id' => $subscriberId,
            ':author_name' => $authorName,
            ':content' => $content,
            ':status' => $status,
        ]);
    }

    public function createArticleComment(
        int $articleId,
        ?int $subscriberId,
        string $authorName,
        string $authorEmail,
        string $content,
        int $rating
    ): bool {
        $stmt = $this->db->prepare(
            'INSERT INTO article_comments (article_id, subscriber_id, author_name, author_email, content, rating, status)
             VALUES (:article_id, :subscriber_id, :author_name, :author_email, :content, :rating, :status)'
        );
        return $stmt->execute([
            ':article_id' => $articleId,
            ':subscriber_id' => $subscriberId,
            ':author_name' => $authorName,
            ':author_email' => $authorEmail,
            ':content' => $content,
            ':rating' => max(1, min(5, $rating)),
            ':status' => 'pending',
        ]);
    }

    public function getApprovedArticleComments(int $articleId, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            "SELECT ac.id, ac.author_name, ac.content, ac.rating, ac.created_at, s.full_name AS subscriber_name,
                    COALESCE(SUM(cv.vote), 0) AS vote_score
             FROM article_comments ac
             LEFT JOIN subscribers s ON s.id = ac.subscriber_id
             LEFT JOIN comment_votes cv ON cv.comment_type = 'article' AND cv.comment_id = ac.id
             WHERE ac.article_id = :article_id
               AND ac.status = 'approved'
             GROUP BY ac.id, ac.author_name, ac.content, ac.rating, ac.created_at, s.full_name
             ORDER BY ac.created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':article_id', $articleId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getArticleRatingStats(int $articleId): array
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total_reviews, COALESCE(ROUND(AVG(rating), 1), 0) AS avg_rating
             FROM article_comments
             WHERE article_id = :article_id
               AND status = 'approved'"
        );
        $stmt->execute([':article_id' => $articleId]);
        $row = $stmt->fetch() ?: [];

        return [
            'total_reviews' => (int) ($row['total_reviews'] ?? 0),
            'avg_rating' => (float) ($row['avg_rating'] ?? 0),
        ];
    }

    public function getPendingComments(int $limit = 100): array
    {
        $stmt = $this->db->prepare(
            "SELECT ac.id, ac.article_id, a.category_id, a.title AS article_title, ac.author_name, ac.rating, ac.content, ac.created_at
             FROM article_comments ac
             JOIN articles a ON a.id = ac.article_id
             WHERE ac.status = 'pending'
             ORDER BY ac.created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getPendingDebateComments(int $limit = 100): array
    {
        $stmt = $this->db->prepare(
            "SELECT dc.id, dc.debate_id, d.slug AS debate_slug, d.title AS debate_title, dc.author_name, dc.content, dc.created_at
             FROM debate_comments dc
             JOIN debates d ON d.id = dc.debate_id
             WHERE dc.status = 'pending'
             ORDER BY dc.created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateDebateCommentStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['approved', 'rejected', 'pending'], true)) {
            return false;
        }

        $stmt = $this->db->prepare('UPDATE debate_comments SET status = :status WHERE id = :id');
        return $stmt->execute([
            ':status' => $status,
            ':id' => $id,
        ]);
    }

    public function deleteDebateComment(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM debate_comments WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function getPendingSiteReviews(int $limit = 100): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, author_name, rating, comment, created_at
             FROM site_reviews
             WHERE status = 'pending'
             ORDER BY created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateSiteReviewStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['approved', 'rejected', 'pending'], true)) {
            return false;
        }

        $stmt = $this->db->prepare('UPDATE site_reviews SET status = :status WHERE id = :id');
        return $stmt->execute([
            ':status' => $status,
            ':id' => $id,
        ]);
    }

    public function deleteSiteReview(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM site_reviews WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function updateArticleCommentStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['approved', 'rejected', 'pending'], true)) {
            return false;
        }

        $stmt = $this->db->prepare('UPDATE article_comments SET status = :status WHERE id = :id');
        return $stmt->execute([
            ':status' => $status,
            ':id' => $id,
        ]);
    }

    public function deleteArticleComment(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM article_comments WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function getContacts(int $limit = 100): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, full_name, email, subject, message, status, created_at
             FROM contact_messages
             ORDER BY created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function createContactMessage(array $data): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO contact_messages (full_name, email, subject, message, status)
             VALUES (:full_name, :email, :subject, :message, :status)'
        );
        return $stmt->execute([
            ':full_name' => $data['full_name'],
            ':email' => mb_strtolower((string) $data['email']),
            ':subject' => $data['subject'],
            ':message' => $data['message'],
            ':status' => 'new',
        ]);
    }

    public function updateContactStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['new', 'read', 'closed'], true)) {
            return false;
        }

        $stmt = $this->db->prepare('UPDATE contact_messages SET status = :status WHERE id = :id');
        return $stmt->execute([
            ':status' => $status,
            ':id' => $id,
        ]);
    }

    public function deleteContact(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM contact_messages WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function createJournal(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO pinned_journals (slug, title, summary, content, pinned_order, status, published_at)
             VALUES (:slug, :title, :summary, :content, :pinned_order, :status, :published_at)"
        );
        $stmt->execute([
            ':slug' => $data['slug'],
            ':title' => $data['title'],
            ':summary' => $data['summary'],
            ':content' => $data['content'],
            ':pinned_order' => (int) ($data['pinned_order'] ?? 0),
            ':status' => $data['status'] ?? 'published',
            ':published_at' => $data['published_at'] ?? date('Y-m-d H:i:s'),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function deleteJournal(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM pinned_journals WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function createDebate(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO debates (slug, title, summary, body, status, is_pinned)
             VALUES (:slug, :title, :summary, :body, :status, :is_pinned)'
        );
        $stmt->execute([
            ':slug' => $data['slug'],
            ':title' => $data['title'],
            ':summary' => $data['summary'],
            ':body' => $data['body'],
            ':status' => $data['status'] ?? 'open',
            ':is_pinned' => (int) ($data['is_pinned'] ?? 0),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function deleteDebate(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM debates WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function getApprovedReviews(int $limit = 8): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, author_name, rating, comment, created_at
             FROM site_reviews
             WHERE status = 'approved'
             ORDER BY created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function createSiteReview(?int $subscriberId, string $authorName, int $rating, string $comment): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO site_reviews (subscriber_id, author_name, rating, comment, status)
             VALUES (:subscriber_id, :author_name, :rating, :comment, :status)'
        );
        return $stmt->execute([
            ':subscriber_id' => $subscriberId,
            ':author_name' => $authorName,
            ':rating' => max(1, min(5, $rating)),
            ':comment' => $comment,
            ':status' => 'pending',
        ]);
    }

    public function logArticleShare(int $articleId, string $channel): bool
    {
        $allowed = ['copy', 'x', 'facebook', 'linkedin', 'whatsapp', 'email'];
        if (!in_array($channel, $allowed, true)) {
            return false;
        }

        $stmt = $this->db->prepare('INSERT INTO article_shares (article_id, channel) VALUES (:article_id, :channel)');
        return $stmt->execute([
            ':article_id' => $articleId,
            ':channel' => $channel,
        ]);
    }

    public function getAdminCounts(): array
    {
        $pendingArticleComments = (int) $this->db->query(
            "SELECT COUNT(*) FROM article_comments WHERE status = 'pending'"
        )->fetchColumn();
        $pendingDebateComments = (int) $this->db->query(
            "SELECT COUNT(*) FROM debate_comments WHERE status = 'pending'"
        )->fetchColumn();
        $pendingReviews = (int) $this->db->query(
            "SELECT COUNT(*) FROM site_reviews WHERE status = 'pending'"
        )->fetchColumn();
        $newContacts = (int) $this->db->query(
            "SELECT COUNT(*) FROM contact_messages WHERE status = 'new'"
        )->fetchColumn();
        $openDebates = (int) $this->db->query(
            "SELECT COUNT(*) FROM debates WHERE status = 'open'"
        )->fetchColumn();

        return [
            'pending_comments' => $pendingArticleComments + $pendingDebateComments + $pendingReviews,
            'new_contacts' => $newContacts,
            'open_debates' => $openDebates,
        ];
    }

    public function getArticleReplies(int $commentId, int $limit = 30): array
    {
        $stmt = $this->db->prepare(
            "SELECT r.id, r.comment_id, r.author_name, r.content, r.created_at, s.full_name AS subscriber_name
             FROM article_comment_replies r
             LEFT JOIN subscribers s ON s.id = r.subscriber_id
             WHERE r.comment_id = :comment_id
               AND r.status = 'approved'
             ORDER BY r.created_at ASC
             LIMIT :limit"
        );
        $stmt->bindValue(':comment_id', $commentId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getDebateReplies(int $commentId, int $limit = 30): array
    {
        $stmt = $this->db->prepare(
            "SELECT r.id, r.comment_id, r.author_name, r.content, r.created_at, s.full_name AS subscriber_name
             FROM debate_comment_replies r
             LEFT JOIN subscribers s ON s.id = r.subscriber_id
             WHERE r.comment_id = :comment_id
               AND r.status = 'approved'
             ORDER BY r.created_at ASC
             LIMIT :limit"
        );
        $stmt->bindValue(':comment_id', $commentId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function createArticleReply(int $commentId, ?int $subscriberId, string $authorName, string $content): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO article_comment_replies (comment_id, subscriber_id, author_name, content, status)
             VALUES (:comment_id, :subscriber_id, :author_name, :content, :status)'
        );
        $ok = $stmt->execute([
            ':comment_id' => $commentId,
            ':subscriber_id' => $subscriberId,
            ':author_name' => $authorName,
            ':content' => $content,
            ':status' => 'approved',
        ]);

        if ($ok) {
            $this->notifyReplyTarget('article', $commentId, $authorName);
        }
        return $ok;
    }

    public function createDebateReply(int $commentId, ?int $subscriberId, string $authorName, string $content): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO debate_comment_replies (comment_id, subscriber_id, author_name, content, status)
             VALUES (:comment_id, :subscriber_id, :author_name, :content, :status)'
        );
        $ok = $stmt->execute([
            ':comment_id' => $commentId,
            ':subscriber_id' => $subscriberId,
            ':author_name' => $authorName,
            ':content' => $content,
            ':status' => 'approved',
        ]);

        if ($ok) {
            $this->notifyReplyTarget('debat', $commentId, $authorName);
        }
        return $ok;
    }

    public function voteComment(string $commentType, int $commentId, int $subscriberId, int $vote): bool
    {
        if (!in_array($commentType, ['article', 'debat'], true)) {
            return false;
        }
        if (!in_array($vote, [-1, 1], true)) {
            return false;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO comment_votes (comment_type, comment_id, subscriber_id, vote)
             VALUES (:comment_type, :comment_id, :subscriber_id, :vote)
             ON DUPLICATE KEY UPDATE vote = VALUES(vote)'
        );
        return $stmt->execute([
            ':comment_type' => $commentType,
            ':comment_id' => $commentId,
            ':subscriber_id' => $subscriberId,
            ':vote' => $vote,
        ]);
    }

    private function notifyReplyTarget(string $commentType, int $commentId, string $replyAuthor): void
    {
        if ($commentType === 'article') {
            $stmt = $this->db->prepare(
                'SELECT subscriber_id FROM article_comments WHERE id = :id LIMIT 1'
            );
        } else {
            $stmt = $this->db->prepare(
                'SELECT subscriber_id FROM debate_comments WHERE id = :id LIMIT 1'
            );
        }

        $stmt->execute([':id' => $commentId]);
        $targetId = (int) ($stmt->fetchColumn() ?: 0);
        if ($targetId <= 0) {
            return;
        }

        $notificationType = $commentType === 'article' ? 'commentaire' : 'debat';
        $message = 'Nouvelle reponse de ' . $replyAuthor . ' sur votre commentaire.';
        $insertStmt = $this->db->prepare(
            'INSERT INTO notifications (user_id, type, message, is_read)
             VALUES (:user_id, :type, :message, 0)'
        );
        $insertStmt->execute([
            ':user_id' => $targetId,
            ':type' => $notificationType,
            ':message' => mb_substr($message, 0, 255),
        ]);
    }
}
