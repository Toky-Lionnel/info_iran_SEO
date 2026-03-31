<?php

declare(strict_types=1);

define('ROOT', __DIR__);

require_once ROOT . '/config/config.php';
require_once ROOT . '/core/Database.php';
require_once ROOT . '/core/Session.php';
require_once ROOT . '/core/Router.php';

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'App\\Core\\' => ROOT . '/core/',
        'App\\Models\\' => ROOT . '/models/',
        'App\\Controllers\\Front\\' => ROOT . '/controllers/front/',
        'App\\Controllers\\Back\\' => ROOT . '/controllers/back/',
        'App\\Controllers\\Api\\' => ROOT . '/controllers/api/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }

        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
});

use App\Core\Router;
use App\Core\Session;

Session::start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

try {
    $router = new Router();
    $router->dispatch($_GET['url'] ?? '');
} catch (\Throwable $exception) {
    http_response_code(500);
    error_log($exception->getMessage());
    $seo = [
        'title' => 'Erreur interne | ' . APP_NAME,
        'description' => 'Une erreur interne est survenue.',
        'canonical' => BASE_URL . '/500',
    ];
    $navCategories = [];
    include ROOT . '/views/front/layouts/header.php';
    ?>
    <section class="container section-space">
        <h1>Erreur interne</h1>
        <p>Une erreur est survenue. Veuillez reessayer plus tard.</p>
        <p><a class="btn-primary" href="<?= BASE_URL ?>/">Retour a l accueil</a></p>
    </section>
    <?php
    include ROOT . '/views/front/layouts/footer.php';
}
