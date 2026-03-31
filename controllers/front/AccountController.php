<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Core\Helpers;
use App\Core\Session;
use App\Core\Validator;
use App\Models\CategoryModel;
use App\Models\FavoriteModel;
use App\Models\NotificationModel;
use App\Models\SecurityLogModel;
use App\Models\SubscriberModel;
use PDOException;

final class AccountController
{
    public function login(): void
    {
        if (Session::isSubscriber()) {
            header('Location: ' . BASE_URL . '/compte/profil');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $securityModel = new SecurityLogModel();
            $clientIp = SecurityLogModel::resolveClientIp();
            $securityAction = 'subscriber_login';
            if ($securityModel->isBlocked($clientIp, $securityAction, 60)) {
                Session::setFlash('error', 'IP temporairement bloquee. Reessayez plus tard.');
                header('Location: ' . BASE_URL . '/compte/login');
                exit;
            }

            $email = trim((string) ($_POST['email'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');

            $validator = new Validator();
            $errors = $validator->validate(
                ['email' => $email, 'password' => $password],
                ['email' => 'required|email|max:190', 'password' => 'required|min:6|max:120']
            );

            if ($errors === []) {
                $subscriberModel = new SubscriberModel();
                $subscriber = $subscriberModel->findByEmail($email);
                if (
                    $subscriber !== null
                    && (int) $subscriber['is_active'] === 1
                    && $subscriberModel->verifyPassword($password, (string) $subscriber['password_hash'])
                ) {
                    $subscriberModel->updateLastLogin((int) $subscriber['id']);
                    $freshSubscriber = $subscriberModel->getById((int) $subscriber['id']) ?? $subscriber;
                    Session::regenerate();
                    Session::setSubscriber($freshSubscriber);
                    $securityModel->log($clientIp, $securityAction, 'success');
                    Session::setFlash('success', 'Connexion abonne reussie.');
                    header('Location: ' . BASE_URL . '/compte/profil');
                    exit;
                }
            }

            $securityModel->registerFailedAndMaybeBlock($clientIp, $securityAction, 7, 20);
            Session::setFlash('error', 'Connexion impossible. Verifiez votre email et mot de passe.');
            header('Location: ' . BASE_URL . '/compte/login');
            exit;
        }

        $this->render(
            'Connexion abonne',
            'Accedez a votre espace abonne premium.',
            ROOT . '/views/front/account/login.php',
            ['responsive.css', 'portal.css'],
            'account',
            BASE_URL . '/compte/login'
        );
    }

    public function register(): void
    {
        if (Session::isSubscriber()) {
            header('Location: ' . BASE_URL . '/compte/profil');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();

            $fullName = trim((string) ($_POST['full_name'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
            $phone = trim((string) ($_POST['phone'] ?? ''));
            $country = trim((string) ($_POST['country'] ?? ''));
            $city = trim((string) ($_POST['city'] ?? ''));
            $interestArea = trim((string) ($_POST['interest_area'] ?? 'geopolitique'));
            $bio = trim((string) ($_POST['bio'] ?? ''));
            $newsletterOptin = isset($_POST['newsletter_optin']) ? 1 : 0;

            $validator = new Validator();
            $errors = $validator->validate(
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'password' => $password,
                    'password_confirm' => $passwordConfirm,
                    'phone' => $phone,
                    'country' => $country,
                    'city' => $city,
                    'interest_area' => $interestArea,
                    'bio' => $bio,
                ],
                [
                    'full_name' => 'required|min:3|max:120',
                    'email' => 'required|email|max:190',
                    'password' => 'required|min:8|max:120',
                    'password_confirm' => 'required|min:8|max:120',
                    'phone' => 'max:30',
                    'country' => 'max:80',
                    'city' => 'max:120',
                    'interest_area' => 'max:120',
                    'bio' => 'max:1000',
                ]
            );

            if ($password !== $passwordConfirm) {
                $errors['password_confirm'] = 'La confirmation du mot de passe est invalide.';
            }

            if ($errors === []) {
                $subscriberModel = new SubscriberModel();
                if ($subscriberModel->findByEmail($email) !== null) {
                    Session::setFlash('error', 'Cet email est deja utilise.');
                    header('Location: ' . BASE_URL . '/compte/register');
                    exit;
                }

                try {
                    $subscriberId = $subscriberModel->create([
                        'full_name' => $fullName,
                        'email' => $email,
                        'password' => $password,
                        'phone' => $phone,
                        'country' => $country,
                        'city' => $city,
                        'interest_area' => $interestArea,
                        'bio' => $bio,
                        'newsletter_optin' => $newsletterOptin,
                        'plan' => 'free',
                        'is_subscribed' => 0,
                        'is_active' => 1,
                        'points' => 10,
                    ]);

                    $subscriber = $subscriberModel->getById($subscriberId);
                    if ($subscriber !== null) {
                        Session::regenerate();
                        Session::setSubscriber($subscriber);
                    }
                    Session::setFlash('success', 'Compte cree. Completez votre profil pour renforcer votre dossier abonne.');
                    header('Location: ' . BASE_URL . '/compte/profil');
                    exit;
                } catch (PDOException $exception) {
                    Session::setFlash('error', 'Creation du compte impossible. Reessayez.');
                }
            } else {
                Session::setFlash('error', implode(' ', array_values($errors)));
            }

            header('Location: ' . BASE_URL . '/compte/register');
            exit;
        }

        $this->render(
            'Creer un compte abonne',
            'Inscription rapide pour commenter, participer aux debats et acceder au menu premium.',
            ROOT . '/views/front/account/register.php',
            ['responsive.css', 'portal.css'],
            'account',
            BASE_URL . '/compte/register'
        );
    }

    public function profile(): void
    {
        if (!Session::isSubscriber()) {
            Session::setFlash('error', 'Connectez-vous pour acceder a votre profil abonne.');
            header('Location: ' . BASE_URL . '/compte/login');
            exit;
        }

        $subscriberModel = new SubscriberModel();
        $subscriberId = (int) Session::get('subscriber_id');
        $subscriber = $subscriberModel->getById($subscriberId);
        if ($subscriber === null) {
            Session::clearSubscriber();
            Session::setFlash('error', 'Votre session est invalide. Merci de vous reconnecter.');
            header('Location: ' . BASE_URL . '/compte/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $action = trim((string) ($_POST['profile_action'] ?? ''));

            if ($action === 'update_profile') {
                $this->updateProfile($subscriberModel, $subscriberId);
            } elseif ($action === 'change_password') {
                $this->updatePassword($subscriberModel, $subscriberId);
            } elseif ($action === 'delete_account') {
                $this->deleteAccount($subscriberModel, $subscriberId);
            } else {
                Session::setFlash('error', 'Action de profil inconnue.');
                header('Location: ' . BASE_URL . '/compte/profil');
                exit;
            }
        }

        $subscriber = $subscriberModel->getById($subscriberId);
        if ($subscriber === null) {
            Session::clearSubscriber();
            Session::setFlash('error', 'Compte introuvable.');
            header('Location: ' . BASE_URL . '/compte/login');
            exit;
        }

        Session::setSubscriber($subscriber);
        $favoriteModel = new FavoriteModel();
        $notificationModel = new NotificationModel();
        $userFavorites = $favoriteModel->getUserFavorites($subscriberId, 20);
        $notifications = $notificationModel->getByUser($subscriberId, 20);
        $unreadNotifications = $notificationModel->unreadCount($subscriberId);

        $categoryModel = new CategoryModel();
        $seo = [
            'title' => 'Mon profil abonne | ' . APP_NAME,
            'description' => 'Gerez vos informations abonne, votre securite et vos preferences.',
            'canonical' => BASE_URL . '/compte/profil',
            'og_type' => 'website',
        ];
        $extraCss = ['responsive.css', 'portal.css'];
        $extraJs = ['notifications.js'];
        $currentPage = 'account';
        $navCategories = $categoryModel->getAll();

        include ROOT . '/views/front/layouts/header.php';
        include ROOT . '/views/front/account/profile.php';
        include ROOT . '/views/front/layouts/footer.php';
    }

    public function logout(): void
    {
        Session::clearSubscriber();
        Session::setFlash('success', 'Vous etes deconnecte du compte abonne.');
        header('Location: ' . BASE_URL . '/');
        exit;
    }

    private function updateProfile(SubscriberModel $subscriberModel, int $subscriberId): void
    {
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $country = trim((string) ($_POST['country'] ?? ''));
        $city = trim((string) ($_POST['city'] ?? ''));
        $interestArea = trim((string) ($_POST['interest_area'] ?? 'geopolitique'));
        $bio = trim((string) ($_POST['bio'] ?? ''));
        $avatarUrl = trim((string) ($_POST['avatar_url'] ?? ''));
        $newsletterOptin = isset($_POST['newsletter_optin']) ? 1 : 0;

        $validator = new Validator();
        $errors = $validator->validate(
            [
                'full_name' => $fullName,
                'phone' => $phone,
                'country' => $country,
                'city' => $city,
                'interest_area' => $interestArea,
                'bio' => $bio,
                'avatar_url' => $avatarUrl,
            ],
            [
                'full_name' => 'required|min:3|max:120',
                'phone' => 'max:30',
                'country' => 'max:80',
                'city' => 'max:120',
                'interest_area' => 'max:120',
                'bio' => 'max:1000',
                'avatar_url' => 'max:255',
            ]
        );

        if ($avatarUrl !== '' && filter_var($avatarUrl, FILTER_VALIDATE_URL) === false && !str_starts_with($avatarUrl, '/')) {
            $errors['avatar_url'] = 'URL avatar invalide.';
        }

        if ($errors !== []) {
            Session::setFlash('error', implode(' ', array_values($errors)));
            header('Location: ' . BASE_URL . '/compte/profil');
            exit;
        }

        $ok = $subscriberModel->updateProfile($subscriberId, [
            'full_name' => $fullName,
            'phone' => $phone,
            'country' => $country,
            'city' => $city,
            'interest_area' => $interestArea,
            'bio' => Helpers::truncate($bio, 1000),
            'newsletter_optin' => $newsletterOptin,
            'avatar_url' => $avatarUrl,
        ]);

        Session::setFlash($ok ? 'success' : 'error', $ok ? 'Profil mis a jour.' : 'Mise a jour impossible.');
        header('Location: ' . BASE_URL . '/compte/profil');
        exit;
    }

    private function updatePassword(SubscriberModel $subscriberModel, int $subscriberId): void
    {
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $newPasswordConfirm = (string) ($_POST['new_password_confirm'] ?? '');

        $validator = new Validator();
        $errors = $validator->validate(
            [
                'current_password' => $currentPassword,
                'new_password' => $newPassword,
                'new_password_confirm' => $newPasswordConfirm,
            ],
            [
                'current_password' => 'required|min:6|max:120',
                'new_password' => 'required|min:8|max:120',
                'new_password_confirm' => 'required|min:8|max:120',
            ]
        );

        if ($newPassword !== $newPasswordConfirm) {
            $errors['new_password_confirm'] = 'La confirmation du nouveau mot de passe est invalide.';
        }

        if ($errors !== []) {
            Session::setFlash('error', implode(' ', array_values($errors)));
            header('Location: ' . BASE_URL . '/compte/profil');
            exit;
        }

        $subscriberWithPassword = $subscriberModel->getByIdWithPassword($subscriberId);
        if (
            $subscriberWithPassword === null
            || !$subscriberModel->verifyPassword($currentPassword, (string) $subscriberWithPassword['password_hash'])
        ) {
            Session::setFlash('error', 'Mot de passe actuel invalide.');
            header('Location: ' . BASE_URL . '/compte/profil');
            exit;
        }

        $ok = $subscriberModel->updatePassword($subscriberId, $newPassword);
        Session::setFlash($ok ? 'success' : 'error', $ok ? 'Mot de passe modifie.' : 'Modification impossible.');
        header('Location: ' . BASE_URL . '/compte/profil');
        exit;
    }

    private function deleteAccount(SubscriberModel $subscriberModel, int $subscriberId): void
    {
        $confirmation = trim((string) ($_POST['delete_confirm'] ?? ''));
        if ($confirmation !== 'SUPPRIMER') {
            Session::setFlash('error', 'Pour supprimer le compte, saisissez exactement SUPPRIMER.');
            header('Location: ' . BASE_URL . '/compte/profil');
            exit;
        }

        $ok = $subscriberModel->delete($subscriberId);
        if ($ok) {
            Session::clearSubscriber();
            Session::setFlash('success', 'Compte abonne supprime definitivement.');
            header('Location: ' . BASE_URL . '/');
            exit;
        }

        Session::setFlash('error', 'Suppression impossible pour le moment.');
        header('Location: ' . BASE_URL . '/compte/profil');
        exit;
    }

    private function render(
        string $title,
        string $description,
        string $viewPath,
        array $extraCss,
        string $currentPage,
        string $canonical
    ): void {
        $categoryModel = new CategoryModel();
        $seo = [
            'title' => $title . ' | ' . APP_NAME,
            'description' => $description,
            'canonical' => $canonical,
            'og_type' => 'website',
        ];
        $extraJs = [];
        $navCategories = $categoryModel->getAll();

        include ROOT . '/views/front/layouts/header.php';
        include $viewPath;
        include ROOT . '/views/front/layouts/footer.php';
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
