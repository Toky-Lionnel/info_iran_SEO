<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class SeoAnalysisModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function analyzeAndStore(int $articleId, string $title, string $content): array
    {
        $titleScore = $this->computeTitleScore($title);
        $keywordScore = $this->computeKeywordScore($title, $content);
        $readabilityScore = $this->computeReadabilityScore($content);

        $stmt = $this->db->prepare(
            'INSERT INTO seo_analysis (article_id, title_score, keyword_score, readability_score)
             VALUES (:article_id, :title_score, :keyword_score, :readability_score)
             ON DUPLICATE KEY UPDATE
               title_score = VALUES(title_score),
               keyword_score = VALUES(keyword_score),
               readability_score = VALUES(readability_score)'
        );
        $stmt->execute([
            ':article_id' => $articleId,
            ':title_score' => $titleScore,
            ':keyword_score' => $keywordScore,
            ':readability_score' => $readabilityScore,
        ]);

        return [
            'title_score' => $titleScore,
            'keyword_score' => $keywordScore,
            'readability_score' => $readabilityScore,
        ];
    }

    public function getByArticleId(int $articleId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, article_id, title_score, keyword_score, readability_score, created_at, updated_at
             FROM seo_analysis
             WHERE article_id = :article_id
             LIMIT 1'
        );
        $stmt->execute([':article_id' => $articleId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getWorstScores(int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT s.article_id, s.title_score, s.keyword_score, s.readability_score, a.title
             FROM seo_analysis s
             JOIN articles a ON a.id = s.article_id
             ORDER BY (s.title_score + s.keyword_score + s.readability_score) ASC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function computeTitleScore(string $title): int
    {
        $length = mb_strlen(trim($title));
        if ($length === 0) {
            return 0;
        }
        if ($length >= 45 && $length <= 65) {
            return 95;
        }
        if ($length >= 35 && $length <= 75) {
            return 80;
        }
        if ($length >= 25 && $length <= 90) {
            return 65;
        }
        return 45;
    }

    private function computeKeywordScore(string $title, string $content): int
    {
        $plain = mb_strtolower(strip_tags($content));
        $titleWords = preg_split('/[^a-z0-9]+/i', mb_strtolower($title)) ?: [];
        $keywords = array_values(array_filter($titleWords, static fn(string $w): bool => mb_strlen($w) >= 4));
        $keywords = array_slice(array_unique($keywords), 0, 4);
        if ($keywords === []) {
            return 40;
        }

        $hits = 0;
        foreach ($keywords as $keyword) {
            $hits += substr_count($plain, $keyword);
        }

        if ($hits >= 20) {
            return 95;
        }
        if ($hits >= 10) {
            return 80;
        }
        if ($hits >= 5) {
            return 65;
        }
        return 45;
    }

    private function computeReadabilityScore(string $content): int
    {
        $plain = trim(preg_replace('/\s+/', ' ', strip_tags($content)) ?? '');
        if ($plain === '') {
            return 0;
        }

        $words = preg_split('/\s+/', $plain) ?: [];
        $sentences = preg_split('/[.!?]+/', $plain) ?: [];
        $wordCount = max(1, count(array_filter($words, static fn(string $w): bool => $w !== '')));
        $sentenceCount = max(1, count(array_filter($sentences, static fn(string $s): bool => trim($s) !== '')));
        $avgSentenceLength = $wordCount / $sentenceCount;

        if ($avgSentenceLength >= 12 && $avgSentenceLength <= 18) {
            return 92;
        }
        if ($avgSentenceLength >= 9 && $avgSentenceLength <= 22) {
            return 78;
        }
        if ($avgSentenceLength >= 6 && $avgSentenceLength <= 28) {
            return 62;
        }
        return 45;
    }
}
