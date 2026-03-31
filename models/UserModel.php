<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class UserModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        $sql = 'SELECT id, username, email, role, last_login, created_at
                FROM admin_users
                ORDER BY created_at DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, username, password_hash, email, role, last_login
             FROM admin_users
             WHERE username = :username
             LIMIT 1'
        );
        $stmt->execute([':username' => $username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function updateLastLogin(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE admin_users SET last_login = NOW() WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public function create(array $data): bool
    {
        $sql = 'INSERT INTO admin_users (username, password_hash, email, role)
                VALUES (:username, :password_hash, :email, :role)';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':username' => $data['username'],
            ':password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            ':email' => $data['email'],
            ':role' => $data['role'] ?? 'editor',
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM admin_users WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function changePassword(int $id, string $plainPassword): bool
    {
        $stmt = $this->db->prepare('UPDATE admin_users SET password_hash = :hash WHERE id = :id');
        return $stmt->execute([
            ':hash' => password_hash($plainPassword, PASSWORD_BCRYPT),
            ':id' => $id,
        ]);
    }
}
