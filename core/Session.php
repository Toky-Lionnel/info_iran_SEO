<?php

declare(strict_types=1);

namespace App\Core;

final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $isHttps = (
                (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
            );

            ini_set('session.use_strict_mode', '1');
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_samesite', 'Lax');
            ini_set('session.cache_limiter', '');
            session_cache_limiter('');

            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return array_key_exists($key, $_SESSION);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'msg' => $message,
        ];
    }

    public static function getFlash(): ?array
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }

    public static function isAdmin(): bool
    {
        return isset($_SESSION['admin_id']);
    }

    public static function isSubscriber(): bool
    {
        return isset($_SESSION['subscriber_id']);
    }

    public static function isPremiumSubscriber(): bool
    {
        return self::isSubscriber()
            && (bool) ($_SESSION['subscriber_is_active'] ?? false)
            && (bool) ($_SESSION['subscriber_is_subscribed'] ?? false)
            && (string) ($_SESSION['subscriber_plan'] ?? 'free') === 'premium';
    }

    public static function setSubscriber(array $subscriber): void
    {
        $_SESSION['subscriber_id'] = (int) ($subscriber['id'] ?? 0);
        $_SESSION['subscriber_name'] = (string) ($subscriber['full_name'] ?? '');
        $_SESSION['subscriber_email'] = (string) ($subscriber['email'] ?? '');
        $_SESSION['subscriber_phone'] = (string) ($subscriber['phone'] ?? '');
        $_SESSION['subscriber_country'] = (string) ($subscriber['country'] ?? '');
        $_SESSION['subscriber_city'] = (string) ($subscriber['city'] ?? '');
        $_SESSION['subscriber_interest_area'] = (string) ($subscriber['interest_area'] ?? '');
        $_SESSION['subscriber_points'] = (int) ($subscriber['points'] ?? 0);
        $_SESSION['subscriber_plan'] = (string) ($subscriber['plan'] ?? 'free');
        $_SESSION['subscriber_is_subscribed'] = (int) ($subscriber['is_subscribed'] ?? 0) === 1;
        $_SESSION['subscriber_is_active'] = (int) ($subscriber['is_active'] ?? 0) === 1;
    }

    public static function clearSubscriber(): void
    {
        unset(
            $_SESSION['subscriber_id'],
            $_SESSION['subscriber_name'],
            $_SESSION['subscriber_email'],
            $_SESSION['subscriber_phone'],
            $_SESSION['subscriber_country'],
            $_SESSION['subscriber_city'],
            $_SESSION['subscriber_interest_area'],
            $_SESSION['subscriber_points'],
            $_SESSION['subscriber_plan'],
            $_SESSION['subscriber_is_subscribed'],
            $_SESSION['subscriber_is_active']
        );
    }

    public static function requireAdmin(): void
    {
        if (!self::isAdmin()) {
            header('Location: ' . ADMIN_PATH . '/login');
            exit;
        }
    }

    public static function requirePremiumSubscriber(): void
    {
        if (!self::isPremiumSubscriber()) {
            header('Location: ' . BASE_URL . '/403');
            exit;
        }
    }

    public static function destroy(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }
}
