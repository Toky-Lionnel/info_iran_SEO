<?php

declare(strict_types=1);

namespace App\Controllers\Back;

use App\Core\Session;
use App\Core\Validator;
use App\Models\SecurityLogModel;
use App\Models\UserModel;

final class AuthController
{
    public function login(): void
    {
        if (Session::isAdmin()) {
            header('Location: ' . ADMIN_PATH);
            exit;
        }

        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $securityModel = new SecurityLogModel();
            $clientIp = SecurityLogModel::resolveClientIp();
            $securityAction = 'admin_login';

            if ($securityModel->isBlocked($clientIp, $securityAction, 60)) {
                Session::setFlash('error', 'IP temporairement bloquee suite a des tentatives suspectes.');
                header('Location: ' . ADMIN_PATH . '/login');
                exit;
            }

            if ($this->isRateLimited()) {
                $securityModel->log($clientIp, $securityAction, 'blocked');
                Session::setFlash('error', 'Trop de tentatives. Reessayez dans quelques minutes.');
                header('Location: ' . ADMIN_PATH . '/login');
                exit;
            }

            $username = trim((string) ($_POST['username'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $validator = new Validator();
            $errors = $validator->validate(
                [
                    'username' => $username,
                    'password' => $password,
                ],
                [
                    'username' => 'required|max:80',
                    'password' => 'required|min:6|max:120',
                ],
                [
                    'username.required' => 'Identifiant requis.',
                    'password.required' => 'Mot de passe requis.',
                ]
            );

            if ($errors === []) {
                $userModel = new UserModel();
                $user = $userModel->findByUsername($username);

                if ($user !== null && $userModel->verifyPassword($password, (string) $user['password_hash'])) {
                    $this->resetRateLimit();
                    Session::regenerate();
                    Session::set('admin_id', (int) $user['id']);
                    Session::set('admin_username', (string) $user['username']);
                    Session::set('admin_role', (string) $user['role']);
                    $userModel->updateLastLogin((int) $user['id']);
                    $securityModel->log($clientIp, $securityAction, 'success');

                    Session::setFlash('success', 'Connexion reussie.');
                    header('Location: ' . ADMIN_PATH);
                    exit;
                }

                $this->registerFailedAttempt();
                $isNowBlocked = $securityModel->registerFailedAndMaybeBlock($clientIp, $securityAction, 6, 15);
                Session::setFlash('error', 'Identifiants invalides.');
                if ($isNowBlocked) {
                    Session::setFlash('error', 'Trop de tentatives, IP temporairement bloquee.');
                }
                header('Location: ' . ADMIN_PATH . '/login');
                exit;
            }

            $this->registerFailedAttempt();
            $securityModel->registerFailedAndMaybeBlock($clientIp, $securityAction, 6, 15);
            Session::setFlash('error', implode(' ', array_values($errors)));
        }

        $seo = ['title' => 'Connexion administration'];
        $adminPage = 'login';
        $extraCss = ['login.css'];

        include ROOT . '/views/back/layouts/header.php';
        include ROOT . '/views/back/auth/login.php';
        include ROOT . '/views/back/layouts/footer.php';
    }

    public function logout(): void
    {
        Session::destroy();
        Session::start();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: ' . ADMIN_PATH . '/login');
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

    private function isRateLimited(): bool
    {
        $attempts = $_SESSION['login_attempts'] ?? [
            'count' => 0,
            'first' => time(),
        ];

        $windowSeconds = 300;
        if ((time() - (int) $attempts['first']) > $windowSeconds) {
            $this->resetRateLimit();
            return false;
        }

        return (int) $attempts['count'] >= 6;
    }

    private function registerFailedAttempt(): void
    {
        $attempts = $_SESSION['login_attempts'] ?? [
            'count' => 0,
            'first' => time(),
        ];

        $windowSeconds = 300;
        if ((time() - (int) $attempts['first']) > $windowSeconds) {
            $attempts = [
                'count' => 0,
                'first' => time(),
            ];
        }

        $attempts['count'] = (int) $attempts['count'] + 1;
        $_SESSION['login_attempts'] = $attempts;
    }

    private function resetRateLimit(): void
    {
        $_SESSION['login_attempts'] = [
            'count' => 0,
            'first' => time(),
        ];
    }
}
