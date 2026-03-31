<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class CacheModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function get(string $key): ?string
    {
        $key = $this->sanitizeKey($key);
        $stmt = $this->db->prepare(
            'SELECT id, content, expires_at
             FROM cache
             WHERE key_name = :key_name
             LIMIT 1'
        );
        $stmt->execute([':key_name' => $key]);
        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        $expiresAt = (string) ($row['expires_at'] ?? '');
        if ($expiresAt !== '' && strtotime($expiresAt) < time()) {
            $this->delete($key);
            return null;
        }

        return (string) ($row['content'] ?? '');
    }

    public function set(string $key, string $content, int $ttlSeconds = 300): bool
    {
        $key = $this->sanitizeKey($key);
        $expiresAt = date('Y-m-d H:i:s', time() + max(1, $ttlSeconds));
        $stmt = $this->db->prepare(
            'INSERT INTO cache (key_name, content, expires_at)
             VALUES (:key_name, :content, :expires_at)
             ON DUPLICATE KEY UPDATE
               content = VALUES(content),
               created_at = CURRENT_TIMESTAMP,
               expires_at = VALUES(expires_at)'
        );

        $ok = $stmt->execute([
            ':key_name' => mb_substr(trim($key), 0, 191),
            ':content' => $content,
            ':expires_at' => $expiresAt,
        ]);

        if ($ok) {
            $this->pruneExpiredRandomly();
        }

        return $ok;
    }

    public function delete(string $key): bool
    {
        $key = $this->sanitizeKey($key);
        $stmt = $this->db->prepare('DELETE FROM cache WHERE key_name = :key_name');
        return $stmt->execute([':key_name' => $key]);
    }

    public function invalidatePrefix(string $prefix): bool
    {
        $prefix = $this->sanitizeKey($prefix);
        $stmt = $this->db->prepare('DELETE FROM cache WHERE key_name LIKE :prefix');
        return $stmt->execute([':prefix' => $prefix . '%']);
    }

    public function clearExpired(): bool
    {
        $stmt = $this->db->prepare('DELETE FROM cache WHERE expires_at IS NOT NULL AND expires_at < NOW()');
        return $stmt->execute();
    }

    private function sanitizeKey(string $key): string
    {
        $key = mb_substr(trim($key), 0, 191);
        return $key === '' ? 'cache.default' : $key;
    }

    private function pruneExpiredRandomly(): void
    {
        try {
            if (random_int(1, 40) === 1) {
                $this->clearExpired();
            }
        } catch (\Throwable $exception) {
            // Cache pruning must never break runtime flow.
        }
    }
}
