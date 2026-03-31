<?php

declare(strict_types=1);

namespace App\Controllers\Back;

use App\Core\Session;
use App\Core\Validator;
use App\Models\UserModel;
use PDOException;

final class UserController
{
    public function list(): void
    {
        Session::requireAdmin();

        $userModel = new UserModel();
        $users = $userModel->getAll();
        $seo = ['title' => 'Utilisateurs admin'];
        $adminPage = 'users';

        include ROOT . '/views/back/layouts/header.php';
        ?>
        <section class="dashboard">
            <h1>Utilisateurs backoffice</h1>
            <form method="POST" action="<?= ADMIN_PATH ?>/users/create" class="card-form" style="margin-bottom:2rem;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required maxlength="80">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required maxlength="150">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role">
                        <option value="editor">editor</option>
                        <option value="admin">admin</option>
                    </select>
                </div>
                <button class="btn-save" type="submit">Creer utilisateur</button>
            </form>

            <form method="POST" action="<?= ADMIN_PATH ?>/users/change-password" class="card-form" style="margin-bottom:2rem;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                <h2>Changer mon mot de passe</h2>
                <div class="form-group">
                    <label for="current_password">Mot de passe actuel</label>
                    <input type="password" id="current_password" name="current_password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="new_password">Nouveau mot de passe</label>
                    <input type="password" id="new_password" name="new_password" required minlength="8">
                </div>
                <div class="form-group">
                    <label for="new_password_confirm">Confirmation</label>
                    <input type="password" id="new_password_confirm" name="new_password_confirm" required minlength="8">
                </div>
                <button class="btn-save" type="submit">Changer le mot de passe</button>
            </form>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Derniere connexion</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= (int) $user['id'] ?></td>
                        <td><?= htmlspecialchars((string) $user['username']) ?></td>
                        <td><?= htmlspecialchars((string) $user['email']) ?></td>
                        <td><?= htmlspecialchars((string) $user['role']) ?></td>
                        <td><?= htmlspecialchars((string) ($user['last_login'] ?? '-')) ?></td>
                        <td>
                            <?php if ((int) $user['id'] !== (int) Session::get('admin_id')): ?>
                                <form method="POST" action="<?= ADMIN_PATH ?>/users/delete/<?= (int) $user['id'] ?>" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                                    <button class="btn-sm" type="submit" data-confirm="Supprimer cet utilisateur ?">Supprimer</button>
                                </form>
                            <?php else: ?>
                                <span class="status status-published">Session active</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <?php
        include ROOT . '/views/back/layouts/footer.php';
    }

    public function create(): void
    {
        Session::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . ADMIN_PATH . '/users');
            exit;
        }

        $this->verifyCsrf();

        $username = trim((string) ($_POST['username'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $role = trim((string) ($_POST['role'] ?? 'editor'));

        if ($username === '' || mb_strlen($username) > 80) {
            Session::setFlash('error', 'Username invalide.');
            header('Location: ' . ADMIN_PATH . '/users');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('error', 'Email invalide.');
            header('Location: ' . ADMIN_PATH . '/users');
            exit;
        }

        if (mb_strlen($password) < 6) {
            Session::setFlash('error', 'Mot de passe trop court (min 6).');
            header('Location: ' . ADMIN_PATH . '/users');
            exit;
        }

        if (!in_array($role, ['admin', 'editor'], true)) {
            $role = 'editor';
        }

        $userModel = new UserModel();
        try {
            $ok = $userModel->create([
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'role' => $role,
            ]);
            Session::setFlash($ok ? 'success' : 'error', $ok ? 'Utilisateur cree.' : 'Creation impossible.');
        } catch (PDOException $exception) {
            Session::setFlash('error', 'Creation impossible: username deja utilise.');
        }
        header('Location: ' . ADMIN_PATH . '/users');
        exit;
    }

    public function delete(int $id): void
    {
        Session::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Location: ' . ADMIN_PATH . '/users');
            exit;
        }

        $this->verifyCsrf();

        if ($id === (int) Session::get('admin_id')) {
            Session::setFlash('error', 'Impossible de supprimer la session connectee.');
            header('Location: ' . ADMIN_PATH . '/users');
            exit;
        }

        $userModel = new UserModel();
        $ok = $userModel->delete($id);
        Session::setFlash($ok ? 'success' : 'error', $ok ? 'Utilisateur supprime.' : 'Suppression impossible.');
        header('Location: ' . ADMIN_PATH . '/users');
        exit;
    }

    public function changePassword(): void
    {
        Session::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Location: ' . ADMIN_PATH . '/users');
            exit;
        }

        $this->verifyCsrf();

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
            ],
            [
                'current_password.required' => 'Mot de passe actuel requis.',
                'new_password.required' => 'Nouveau mot de passe requis.',
            ]
        );

        if ($newPassword !== $newPasswordConfirm) {
            $errors['new_password_confirm'] = 'La confirmation ne correspond pas.';
        }

        if ($errors !== []) {
            Session::setFlash('error', implode(' ', array_values($errors)));
            header('Location: ' . ADMIN_PATH . '/users');
            exit;
        }

        $userModel = new UserModel();
        $currentUser = $userModel->findByUsername((string) Session::get('admin_username'));
        if ($currentUser === null || !$userModel->verifyPassword($currentPassword, (string) $currentUser['password_hash'])) {
            Session::setFlash('error', 'Mot de passe actuel invalide.');
            header('Location: ' . ADMIN_PATH . '/users');
            exit;
        }

        if ($userModel->changePassword((int) $currentUser['id'], $newPassword)) {
            Session::setFlash('success', 'Mot de passe modifie.');
        } else {
            Session::setFlash('error', 'Modification impossible.');
        }

        header('Location: ' . ADMIN_PATH . '/users');
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
