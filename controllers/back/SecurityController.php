<?php

declare(strict_types=1);

namespace App\Controllers\Back;

use App\Core\Session;
use App\Models\CacheModel;
use App\Models\SecurityLogModel;

final class SecurityController
{
    public function index(): void
    {
        Session::requireAdmin();

        $securityModel = new SecurityLogModel();
        $recentLogs = $securityModel->getRecentLogs(200);
        $suspiciousIps = $securityModel->getSuspiciousIps(120, 40);

        $seo = ['title' => 'Securite et journaux'];
        $adminPage = 'security';

        include ROOT . '/views/back/layouts/header.php';
        include ROOT . '/views/back/security/index.php';
        include ROOT . '/views/back/layouts/footer.php';
    }

    public function clearExpiredCache(): void
    {
        Session::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Location: ' . ADMIN_PATH . '/security');
            exit;
        }

        $this->verifyCsrf();
        $cacheModel = new CacheModel();
        $cacheModel->clearExpired();
        Session::setFlash('success', 'Cache expire nettoye.');
        header('Location: ' . ADMIN_PATH . '/security');
        exit;
    }

    private function verifyCsrf(): void
    {
        $sessionToken = (string) ($_SESSION['csrf_token'] ?? '');
        $postedToken = (string) ($_POST['csrf_token'] ?? '');
        if ($sessionToken === '' || !hash_equals($sessionToken, $postedToken)) {
            http_response_code(403);
            exit('Token CSRF invalide');
        }
    }
}
