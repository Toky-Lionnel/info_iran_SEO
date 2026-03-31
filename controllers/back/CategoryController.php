<?php

declare(strict_types=1);

namespace App\Controllers\Back;

use App\Core\Session;
use App\Models\ArticleModel;
use App\Models\CategoryModel;
use PDOException;

final class CategoryController
{
    public function list(): void
    {
        Session::requireAdmin();

        $categoryModel = new CategoryModel();
        $categories = $categoryModel->getAll();

        $seo = ['title' => 'Gestion des categories'];
        $adminPage = 'categories';

        include ROOT . '/views/back/layouts/header.php';
        include ROOT . '/views/back/categories/list.php';
        include ROOT . '/views/back/layouts/footer.php';
    }

    public function create(): void
    {
        Session::requireAdmin();

        $categoryModel = new CategoryModel();
        $errors = [];
        $formData = [
            'name' => '',
            'slug' => '',
            'color' => '#C62828',
        ];
        $isEdit = false;
        $category = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $formData = [
                'name' => trim((string) ($_POST['name'] ?? '')),
                'slug' => trim((string) ($_POST['slug'] ?? '')),
                'color' => trim((string) ($_POST['color'] ?? '#C62828')),
            ];
            $errors = $this->validate($formData);
            if ($errors === []) {
                if ($formData['slug'] === '') {
                    $formData['slug'] = $this->generateSlug($formData['name']);
                }
                try {
                    $categoryModel->create($formData);
                    Session::setFlash('success', 'Categorie creee.');
                    header('Location: ' . ADMIN_PATH . '/categories');
                    exit;
                } catch (PDOException $exception) {
                    Session::setFlash('error', 'Erreur SQL: verifiez que le slug de categorie est unique.');
                }
            }
        }

        $seo = ['title' => 'Creer une categorie'];
        $adminPage = 'categories';

        include ROOT . '/views/back/layouts/header.php';
        include ROOT . '/views/back/categories/form.php';
        include ROOT . '/views/back/layouts/footer.php';
    }

    public function edit(int $id): void
    {
        Session::requireAdmin();

        $categoryModel = new CategoryModel();
        $category = $categoryModel->getById($id);
        if ($category === null) {
            Session::setFlash('error', 'Categorie introuvable.');
            header('Location: ' . ADMIN_PATH . '/categories');
            exit;
        }

        $errors = [];
        $isEdit = true;
        $formData = [
            'name' => (string) $category['name'],
            'slug' => (string) $category['slug'],
            'color' => (string) $category['color'],
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $formData = [
                'name' => trim((string) ($_POST['name'] ?? '')),
                'slug' => trim((string) ($_POST['slug'] ?? '')),
                'color' => trim((string) ($_POST['color'] ?? '#C62828')),
            ];
            $errors = $this->validate($formData);
            if ($errors === []) {
                if ($formData['slug'] === '') {
                    $formData['slug'] = $this->generateSlug($formData['name']);
                }
                try {
                    $categoryModel->update($id, $formData);
                    Session::setFlash('success', 'Categorie mise a jour.');
                    header('Location: ' . ADMIN_PATH . '/categories/edit/' . $id);
                    exit;
                } catch (PDOException $exception) {
                    Session::setFlash('error', 'Erreur SQL: verifiez que le slug de categorie est unique.');
                }
            }
        }

        $seo = ['title' => 'Modifier une categorie'];
        $adminPage = 'categories';

        include ROOT . '/views/back/layouts/header.php';
        include ROOT . '/views/back/categories/form.php';
        include ROOT . '/views/back/layouts/footer.php';
    }

    public function delete(int $id): void
    {
        Session::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Location: ' . ADMIN_PATH . '/categories');
            exit;
        }

        $this->verifyCsrf();

        $articleModel = new ArticleModel();
        if ($articleModel->countAllByCategoryId($id) > 0) {
            Session::setFlash('error', 'Suppression impossible : des articles sont lies a cette categorie.');
            header('Location: ' . ADMIN_PATH . '/categories');
            exit;
        }

        $categoryModel = new CategoryModel();
        if ($categoryModel->delete($id)) {
            Session::setFlash('success', 'Categorie supprimee.');
        } else {
            Session::setFlash('error', 'Suppression impossible.');
        }

        header('Location: ' . ADMIN_PATH . '/categories');
        exit;
    }

    private function validate(array $data): array
    {
        $errors = [];

        if ($data['name'] === '') {
            $errors['name'] = 'Le nom est obligatoire.';
        } elseif (mb_strlen($data['name']) > 150) {
            $errors['name'] = 'Le nom doit contenir au maximum 150 caracteres.';
        }

        if ($data['slug'] !== '' && !preg_match('/^[a-z0-9-]{2,100}$/', $data['slug'])) {
            $errors['slug'] = 'Le slug doit contenir uniquement lettres minuscules, chiffres et tirets.';
        }

        if (!preg_match('/^#[a-fA-F0-9]{6}$/', $data['color'])) {
            $errors['color'] = 'La couleur doit etre au format hexadecimal (#RRGGBB).';
        }

        return $errors;
    }

    private function generateSlug(string $text): string
    {
        $slug = strtolower(trim($text));
        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug);
        if ($transliterated !== false) {
            $slug = $transliterated;
        }

        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');
        return $slug !== '' ? substr($slug, 0, 100) : 'categorie';
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
