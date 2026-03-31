<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class SubscriberModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(array $data): int
    {
        $plan = $this->normalizePlan((string) ($data['plan'] ?? 'free'));
        $isSubscribed = $this->normalizeSubscriptionForPlan(
            $plan,
            $data['is_subscribed'] ?? ($plan === 'premium' ? 1 : 0)
        );

        $sql = 'INSERT INTO subscribers (
                    full_name, email, password_hash, phone, country, city, interest_area, bio,
                    newsletter_optin, points, avatar_url, plan, is_subscribed, is_active
                ) VALUES (
                    :full_name, :email, :password_hash, :phone, :country, :city, :interest_area, :bio,
                    :newsletter_optin, :points, :avatar_url, :plan, :is_subscribed, :is_active
                )';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':full_name' => trim((string) $data['full_name']),
            ':email' => mb_strtolower((string) $data['email']),
            ':password_hash' => password_hash((string) $data['password'], PASSWORD_BCRYPT),
            ':phone' => $this->nullableTrim($data['phone'] ?? null),
            ':country' => $this->nullableTrim($data['country'] ?? null),
            ':city' => $this->nullableTrim($data['city'] ?? null),
            ':interest_area' => $this->normalizeInterestArea((string) ($data['interest_area'] ?? 'geopolitique')),
            ':bio' => $this->nullableTrim($data['bio'] ?? null),
            ':newsletter_optin' => (int) ($data['newsletter_optin'] ?? 1),
            ':points' => max(0, (int) ($data['points'] ?? 0)),
            ':avatar_url' => $this->nullableTrim($data['avatar_url'] ?? null),
            ':plan' => $plan,
            ':is_subscribed' => $isSubscribed,
            ':is_active' => (int) ($data['is_active'] ?? 1),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, full_name, email, password_hash, phone, country, city, interest_area, bio,
                    newsletter_optin, points, avatar_url, plan, is_subscribed, is_active,
                    last_login, created_at, updated_at
             FROM subscribers
             WHERE email = :email
             LIMIT 1'
        );
        $stmt->execute([':email' => mb_strtolower($email)]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, full_name, email, phone, country, city, interest_area, bio,
                    newsletter_optin, points, avatar_url, plan, is_subscribed, is_active,
                    last_login, created_at, updated_at
             FROM subscribers
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function getByIdWithPassword(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, full_name, email, password_hash, phone, country, city, interest_area, bio,
                    newsletter_optin, points, avatar_url, plan, is_subscribed, is_active,
                    last_login, created_at, updated_at
             FROM subscribers
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getAll(int $limit = 100, array $filters = []): array
    {
        $where = [];
        $params = [];

        $query = trim((string) ($filters['q'] ?? ''));
        if ($query !== '') {
            $where[] = '(full_name LIKE :query OR email LIKE :query OR country LIKE :query OR city LIKE :query)';
            $params[':query'] = '%' . $query . '%';
        }

        $plan = trim((string) ($filters['plan'] ?? ''));
        if (in_array($plan, ['free', 'premium'], true)) {
            $where[] = 'plan = :plan';
            $params[':plan'] = $plan;
        }

        $active = $filters['active'] ?? '';
        if ($active === '1' || $active === 1) {
            $where[] = 'is_active = 1';
        } elseif ($active === '0' || $active === 0) {
            $where[] = 'is_active = 0';
        }

        $whereSql = $where === [] ? '' : ('WHERE ' . implode(' AND ', $where));

        $stmt = $this->db->prepare(
            "SELECT id, full_name, email, phone, country, city, interest_area, newsletter_optin,
                    points, plan, is_subscribed, is_active, last_login, created_at
             FROM subscribers
             {$whereSql}
             ORDER BY created_at DESC
             LIMIT :limit"
        );

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }

        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getFiltered(int $limit, int $offset, array $filters = []): array
    {
        $where = [];
        $params = [];

        $query = trim((string) ($filters['q'] ?? ''));
        if ($query !== '') {
            $where[] = '(full_name LIKE :query OR email LIKE :query OR country LIKE :query OR city LIKE :query)';
            $params[':query'] = '%' . $query . '%';
        }

        $plan = trim((string) ($filters['plan'] ?? ''));
        if (in_array($plan, ['free', 'premium'], true)) {
            $where[] = 'plan = :plan';
            $params[':plan'] = $plan;
        }

        $active = $filters['active'] ?? '';
        if ($active === '1' || $active === 1) {
            $where[] = 'is_active = 1';
        } elseif ($active === '0' || $active === 0) {
            $where[] = 'is_active = 0';
        }

        $whereSql = $where === [] ? '' : ('WHERE ' . implode(' AND ', $where));

        $sql = "SELECT id, full_name, email, phone, country, city, interest_area, newsletter_optin, points,
                       plan, is_subscribed, is_active, last_login, created_at
                FROM subscribers
                {$whereSql}
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }

        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countFiltered(array $filters = []): int
    {
        $where = [];
        $params = [];

        $query = trim((string) ($filters['q'] ?? ''));
        if ($query !== '') {
            $where[] = '(full_name LIKE :query OR email LIKE :query OR country LIKE :query OR city LIKE :query)';
            $params[':query'] = '%' . $query . '%';
        }

        $plan = trim((string) ($filters['plan'] ?? ''));
        if (in_array($plan, ['free', 'premium'], true)) {
            $where[] = 'plan = :plan';
            $params[':plan'] = $plan;
        }

        $active = $filters['active'] ?? '';
        if ($active === '1' || $active === 1) {
            $where[] = 'is_active = 1';
        } elseif ($active === '0' || $active === 0) {
            $where[] = 'is_active = 0';
        }

        $whereSql = $where === [] ? '' : ('WHERE ' . implode(' AND ', $where));
        $sql = "SELECT COUNT(*) FROM subscribers {$whereSql}";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function countActive(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM subscribers WHERE is_active = 1')->fetchColumn();
    }

    public function countPremium(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM subscribers WHERE is_active = 1 AND is_subscribed = 1 AND plan = 'premium'"
        )->fetchColumn();
    }

    public function countWithPlan(string $plan): int
    {
        if (!in_array($plan, ['free', 'premium'], true)) {
            return 0;
        }

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM subscribers WHERE plan = :plan');
        $stmt->execute([':plan' => $plan]);
        return (int) $stmt->fetchColumn();
    }

    public function toggleSubscription(int $id): bool
    {
        $sql = "UPDATE subscribers
                SET
                    is_subscribed = CASE WHEN is_subscribed = 1 THEN 0 ELSE 1 END,
                    plan = CASE WHEN is_subscribed = 1 THEN 'free' ELSE 'premium' END
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function toggleActive(int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE subscribers
             SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END
             WHERE id = :id'
        );
        return $stmt->execute([':id' => $id]);
    }

    public function updateProfile(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE subscribers
             SET full_name = :full_name,
                 phone = :phone,
                 country = :country,
                 city = :city,
                 interest_area = :interest_area,
                 bio = :bio,
                 newsletter_optin = :newsletter_optin,
                 avatar_url = :avatar_url
             WHERE id = :id'
        );

        return $stmt->execute([
            ':full_name' => trim((string) $data['full_name']),
            ':phone' => $this->nullableTrim($data['phone'] ?? null),
            ':country' => $this->nullableTrim($data['country'] ?? null),
            ':city' => $this->nullableTrim($data['city'] ?? null),
            ':interest_area' => $this->normalizeInterestArea((string) ($data['interest_area'] ?? 'geopolitique')),
            ':bio' => $this->nullableTrim($data['bio'] ?? null),
            ':newsletter_optin' => (int) ($data['newsletter_optin'] ?? 1),
            ':avatar_url' => $this->nullableTrim($data['avatar_url'] ?? null),
            ':id' => $id,
        ]);
    }

    public function updateAdminProfile(int $id, array $data): bool
    {
        $plan = $this->normalizePlan((string) ($data['plan'] ?? 'free'));
        $isSubscribed = $this->normalizeSubscriptionForPlan(
            $plan,
            $data['is_subscribed'] ?? ($plan === 'premium' ? 1 : 0)
        );

        $stmt = $this->db->prepare(
            'UPDATE subscribers
             SET full_name = :full_name,
                 phone = :phone,
                 country = :country,
                 city = :city,
                 interest_area = :interest_area,
                 bio = :bio,
                 newsletter_optin = :newsletter_optin,
                 points = :points,
                 plan = :plan,
                 is_subscribed = :is_subscribed,
                 is_active = :is_active
             WHERE id = :id'
        );

        return $stmt->execute([
            ':full_name' => trim((string) $data['full_name']),
            ':phone' => $this->nullableTrim($data['phone'] ?? null),
            ':country' => $this->nullableTrim($data['country'] ?? null),
            ':city' => $this->nullableTrim($data['city'] ?? null),
            ':interest_area' => $this->normalizeInterestArea((string) ($data['interest_area'] ?? 'geopolitique')),
            ':bio' => $this->nullableTrim($data['bio'] ?? null),
            ':newsletter_optin' => (int) ($data['newsletter_optin'] ?? 1),
            ':points' => max(0, (int) ($data['points'] ?? 0)),
            ':plan' => $plan,
            ':is_subscribed' => $isSubscribed,
            ':is_active' => (int) ($data['is_active'] ?? 1),
            ':id' => $id,
        ]);
    }

    public function updatePassword(int $id, string $plainPassword): bool
    {
        $stmt = $this->db->prepare('UPDATE subscribers SET password_hash = :password_hash WHERE id = :id');
        return $stmt->execute([
            ':password_hash' => password_hash($plainPassword, PASSWORD_BCRYPT),
            ':id' => $id,
        ]);
    }

    public function updateLastLogin(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE subscribers SET last_login = NOW() WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM subscribers WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function getNotificationTargets(int $limit = 200, bool $premiumOnly = false): array
    {
        $sql = 'SELECT id, full_name, email
                FROM subscribers
                WHERE is_active = 1
                  AND newsletter_optin = 1';
        if ($premiumOnly) {
            $sql .= " AND plan = 'premium' AND is_subscribed = 1";
        }
        $sql .= ' ORDER BY created_at DESC LIMIT :limit';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function normalizeInterestArea(string $interestArea): string
    {
        $interestArea = trim($interestArea);
        if ($interestArea === '') {
            return 'geopolitique';
        }
        return mb_substr($interestArea, 0, 120);
    }

    private function normalizePlan(string $plan): string
    {
        $plan = mb_strtolower(trim($plan));
        return in_array($plan, ['free', 'premium'], true) ? $plan : 'free';
    }

    private function normalizeSubscriptionForPlan(string $plan, mixed $rawValue): int
    {
        if ($plan === 'free') {
            return 0;
        }

        return (int) $rawValue === 1 ? 1 : 0;
    }

    private function nullableTrim(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $stringValue = trim((string) $value);
        return $stringValue === '' ? null : $stringValue;
    }
}
