<?php

declare(strict_types=1);

namespace App\Controllers\Back;

use App\Core\Helpers;
use App\Core\Session;
use App\Core\Validator;
use App\Models\SubscriberModel;

final class SubscriberController
{
    public function list(): void
    {
        Session::requireAdmin();

        $subscriberModel = new SubscriberModel();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 12;
        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'plan' => trim((string) ($_GET['plan'] ?? '')),
            'active' => trim((string) ($_GET['active'] ?? '')),
        ];

        $total = $subscriberModel->countFiltered($filters);
        $pager = Helpers::paginate($total, $limit, $page);
        $subscribers = $subscriberModel->getFiltered($limit, (int) $pager['offset'], $filters);

        $stats = [
            'active' => $subscriberModel->countActive(),
            'premium' => $subscriberModel->countPremium(),
            'free' => $subscriberModel->countWithPlan('free'),
        ];

        $seo = ['title' => 'Gestion abonnes'];
        $adminPage = 'subscribers';

        include ROOT . '/views/back/layouts/header.php';
        include ROOT . '/views/back/subscribers/list.php';
        include ROOT . '/views/back/layouts/footer.php';
    }

    public function edit(int $id): void
    {
        Session::requireAdmin();

        $subscriberModel = new SubscriberModel();
        $subscriber = $subscriberModel->getById($id);
        if ($subscriber === null) {
            Session::setFlash('error', 'Abonne introuvable.');
            header('Location: ' . ADMIN_PATH . '/subscribers');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $action = trim((string) ($_POST['action'] ?? 'update'));

            if ($action === 'password') {
                $newPassword = (string) ($_POST['new_password'] ?? '');
                $newPasswordConfirm = (string) ($_POST['new_password_confirm'] ?? '');

                if (mb_strlen($newPassword) < 8 || $newPassword !== $newPasswordConfirm) {
                    Session::setFlash('error', 'Mot de passe invalide ou confirmation incorrecte.');
                } else {
                    $ok = $subscriberModel->updatePassword($id, $newPassword);
                    Session::setFlash($ok ? 'success' : 'error', $ok ? 'Mot de passe abonne modifie.' : 'Modification impossible.');
                }

                header('Location: ' . ADMIN_PATH . '/subscribers/edit/' . $id);
                exit;
            }

            $data = [
                'full_name' => trim((string) ($_POST['full_name'] ?? '')),
                'phone' => trim((string) ($_POST['phone'] ?? '')),
                'country' => trim((string) ($_POST['country'] ?? '')),
                'city' => trim((string) ($_POST['city'] ?? '')),
                'interest_area' => trim((string) ($_POST['interest_area'] ?? 'geopolitique')),
                'bio' => trim((string) ($_POST['bio'] ?? '')),
                'newsletter_optin' => isset($_POST['newsletter_optin']) ? 1 : 0,
                'points' => max(0, (int) ($_POST['points'] ?? 0)),
                'plan' => trim((string) ($_POST['plan'] ?? 'free')),
                'is_subscribed' => isset($_POST['is_subscribed']) ? 1 : 0,
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
            ];

            $validator = new Validator();
            $errors = $validator->validate(
                $data,
                [
                    'full_name' => 'required|min:3|max:120',
                    'phone' => 'max:30',
                    'country' => 'max:80',
                    'city' => 'max:120',
                    'interest_area' => 'max:120',
                    'bio' => 'max:1000',
                    'points' => 'required|max:10',
                    'plan' => 'required|in:free,premium',
                ]
            );

            if ($errors !== []) {
                Session::setFlash('error', implode(' ', array_values($errors)));
                header('Location: ' . ADMIN_PATH . '/subscribers/edit/' . $id);
                exit;
            }

            if ($data['plan'] === 'free') {
                $data['is_subscribed'] = 0;
            }

            $ok = $subscriberModel->updateAdminProfile($id, $data);
            Session::setFlash($ok ? 'success' : 'error', $ok ? 'Profil abonne mis a jour.' : 'Mise a jour impossible.');
            header('Location: ' . ADMIN_PATH . '/subscribers/edit/' . $id);
            exit;
        }

        $subscriber = $subscriberModel->getById($id);
        if ($subscriber === null) {
            Session::setFlash('error', 'Abonne introuvable.');
            header('Location: ' . ADMIN_PATH . '/subscribers');
            exit;
        }

        $seo = ['title' => 'Modifier abonne'];
        $adminPage = 'subscribers';
        include ROOT . '/views/back/layouts/header.php';
        include ROOT . '/views/back/subscribers/edit.php';
        include ROOT . '/views/back/layouts/footer.php';
    }

    public function delete(int $id): void
    {
        Session::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Location: ' . ADMIN_PATH . '/subscribers');
            exit;
        }

        $this->verifyCsrf();
        $subscriberModel = new SubscriberModel();
        $ok = $subscriberModel->delete($id);
        Session::setFlash($ok ? 'success' : 'error', $ok ? 'Abonne supprime.' : 'Suppression impossible.');
        header('Location: ' . ADMIN_PATH . '/subscribers');
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
