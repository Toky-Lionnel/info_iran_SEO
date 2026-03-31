<?php

declare(strict_types=1);

date_default_timezone_set('Europe/Paris');

$loadEnvFile = static function (string $path): void {
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);
        if ($key === '') {
            continue;
        }

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"'))
            || (str_starts_with($value, '\'') && str_ends_with($value, '\''))
        ) {
            $value = substr($value, 1, -1);
        }

        if (getenv($key) === false) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
};

$loadEnvFile(dirname(__DIR__) . '/.env');

$envValue = static function (string $key, string $default): string {
    $value = getenv($key);
    if ($value === false || $value === '') {
        return $default;
    }
    return (string) $value;
};

define('DB_HOST', $envValue('DB_HOST', 'localhost'));
define('DB_PORT', (int) $envValue('DB_PORT', '3306'));
define('DB_NAME', $envValue('DB_NAME', 'iran_war_db'));
define('DB_USER', $envValue('DB_USER', 'root'));
define('DB_PASS', $envValue('DB_PASS', ''));
define('DB_CHARSET', $envValue('DB_CHARSET', 'utf8mb4'));

define('APP_DIR_NAME', basename(dirname(__DIR__)));
$defaultBaseUrl = 'http://localhost/SEMESTRE%206/iran_final/' . APP_DIR_NAME;
$configuredBaseUrl = $envValue('APP_BASE_URL', $envValue('BASE_URL', $defaultBaseUrl));
$configuredBaseUrl = rtrim($configuredBaseUrl, '/');
if ($configuredBaseUrl === '') {
    $configuredBaseUrl = $defaultBaseUrl;
}
define('BASE_URL', $configuredBaseUrl);
define('APP_NAME', 'Guerre en Iran - Actualites & Analyses');
define('APP_DESC', 'Suivi complet du conflit irano-americano-israelien : chronologie, analyses geopolitiques et impacts mondiaux.');

define('ADMIN_PATH', BASE_URL . '/admin');
define('ARTICLES_PER_PAGE', 6);
define('ADMIN_ARTICLES_PER_PAGE', 12);
