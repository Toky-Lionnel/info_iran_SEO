<?php

declare(strict_types=1);

namespace App\Controllers\Back;

use App\Core\Helpers;
use App\Core\Session;
use App\Core\Validator;
use App\Models\ArticleModel;
use App\Models\AuthorModel;
use App\Models\CategoryModel;
use App\Models\CacheModel;
use App\Models\NotificationModel;
use App\Models\SeoAnalysisModel;
use App\Models\SubscriberModel;
use App\Models\TagModel;
use PDOException;

final class ArticleController
{
    public function list(): void
    {
        Session::requireAdmin();

        $articleModel = new ArticleModel();
        $categoryModel = new CategoryModel();

        $status = trim((string) ($_GET['status'] ?? ''));
        $statusFilter = in_array($status, ['draft', 'published', 'archived'], true) ? $status : null;

        $page = $this->sanitizePage((int) ($_GET['page'] ?? 1));
        $limit = ADMIN_ARTICLES_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $articles = $articleModel->getAll($statusFilter, $limit, $offset);
        $total = $articleModel->countAll($statusFilter);
        $totalPages = max(1, (int) ceil($total / $limit));
        $categories = $categoryModel->getAll();

        $seo = ['title' => 'Gestion des articles'];
        $adminPage = 'articles';

        include ROOT . '/views/back/layouts/header.php';
        include ROOT . '/views/back/articles/list.php';
        include ROOT . '/views/back/layouts/footer.php';
    }

    public function create(): void
    {
        Session::requireAdmin();

        $articleModel = new ArticleModel();
        $categoryModel = new CategoryModel();
        $authorModel = new AuthorModel();
        $tagModel = new TagModel();

        $categories = $categoryModel->getAll();
        $authors = $authorModel->getAll();
        $tags = $tagModel->getAll();
        $errors = [];
        $formData = [
            'title' => '',
            'slug' => '',
            'excerpt' => '',
            'content' => '',
            'category_id' => '',
            'author_id' => '1',
            'status' => 'draft',
            'cover_image' => '',
            'cover_alt' => '',
            'tags_input' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $formData = $this->collectFormData();
            $errors = $this->validate($formData, $categoryModel, $authorModel);

            if ($errors === []) {
                $formData['content'] = Helpers::sanitizeHtml($formData['content']);
                $tagsToSync = $this->parseTagsInput((string) $formData['tags_input']);
                try {
                    $articleId = $articleModel->create($formData);
                    $tagModel->syncTags($articleId, $tagsToSync);
                    $seoModel = new SeoAnalysisModel();
                    $seoModel->analyzeAndStore($articleId, (string) $formData['title'], (string) $formData['content']);
                    if ((string) $formData['status'] === 'published') {
                        $this->notifyPublishedArticle((string) $formData['title']);
                    }
                    (new CacheModel())->invalidatePrefix('api.articles.');
                    Session::setFlash('success', 'Article cree avec succes.');
                    header('Location: ' . ADMIN_PATH . '/articles/edit/' . $articleId);
                    exit;
                } catch (PDOException $exception) {
                    Session::setFlash('error', 'Erreur SQL: verifiez que le slug est unique.');
                }
            }
        }

        $seo = ['title' => 'Creer un article'];
        $adminPage = 'articles';

        include ROOT . '/views/back/layouts/header.php';
        include ROOT . '/views/back/articles/create.php';
        include ROOT . '/views/back/layouts/footer.php';
    }

    public function edit(int $id): void
    {
        Session::requireAdmin();

        $articleModel = new ArticleModel();
        $categoryModel = new CategoryModel();
        $authorModel = new AuthorModel();
        $tagModel = new TagModel();

        $article = $articleModel->getById($id);
        if ($article === null) {
            Session::setFlash('error', 'Article introuvable.');
            header('Location: ' . ADMIN_PATH . '/articles');
            exit;
        }

        $categories = $categoryModel->getAll();
        $authors = $authorModel->getAll();
        $tags = $tagModel->getAll();
        $articleTags = $tagModel->getByArticle($id);
        $errors = [];
        $formData = [
            'title' => (string) $article['title'],
            'slug' => (string) $article['slug'],
            'excerpt' => (string) $article['excerpt'],
            'content' => (string) $article['content'],
            'category_id' => (string) $article['category_id'],
            'author_id' => (string) $article['author_id'],
            'status' => (string) $article['status'],
            'cover_image' => (string) ($article['cover_image'] ?? ''),
            'cover_alt' => (string) $article['cover_alt'],
            'published_at' => (string) ($article['published_at'] ?? ''),
            'tags_input' => implode(', ', array_column($articleTags, 'name')),
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $formData = $this->collectFormData();
            $formData['published_at'] = (string) ($article['published_at'] ?? '');
            $errors = $this->validate($formData, $categoryModel, $authorModel, true);

            if ($errors === []) {
                $formData['content'] = Helpers::sanitizeHtml($formData['content']);
                $tagsToSync = $this->parseTagsInput((string) $formData['tags_input']);
                $wasPublished = ((string) ($article['status'] ?? 'draft')) === 'published';
                $nowPublished = ((string) ($formData['status'] ?? 'draft')) === 'published';
                try {
                    $articleModel->update($id, $formData);
                    $tagModel->syncTags($id, $tagsToSync);
                    $seoModel = new SeoAnalysisModel();
                    $seoModel->analyzeAndStore($id, (string) $formData['title'], (string) $formData['content']);
                    if (!$wasPublished && $nowPublished) {
                        $this->notifyPublishedArticle((string) $formData['title']);
                    }
                    (new CacheModel())->invalidatePrefix('api.articles.');
                    Session::setFlash('success', 'Article mis a jour.');
                    header('Location: ' . ADMIN_PATH . '/articles/edit/' . $id);
                    exit;
                } catch (PDOException $exception) {
                    Session::setFlash('error', 'Erreur SQL: verifiez que le slug est unique.');
                }
            }
        }

        $seo = ['title' => 'Modifier un article'];
        $adminPage = 'articles';

        include ROOT . '/views/back/layouts/header.php';
        include ROOT . '/views/back/articles/edit.php';
        include ROOT . '/views/back/layouts/footer.php';
    }

    public function delete(int $id): void
    {
        Session::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            Session::setFlash('error', 'Methode non autorisee pour cette action.');
            header('Location: ' . ADMIN_PATH . '/articles');
            exit;
        }

        $this->verifyCsrf();

        $articleModel = new ArticleModel();
        if ($articleModel->delete($id)) {
            (new CacheModel())->invalidatePrefix('api.articles.');
            Session::setFlash('success', 'Article supprime.');
        } else {
            Session::setFlash('error', 'Suppression impossible.');
        }

        header('Location: ' . ADMIN_PATH . '/articles');
        exit;
    }

    public function toggleStatus(int $id): void
    {
        Session::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Location: ' . ADMIN_PATH . '/articles');
            exit;
        }

        $this->verifyCsrf();

        $articleModel = new ArticleModel();
        $article = $articleModel->getById($id);
        if ($article === null) {
            Session::setFlash('error', 'Article introuvable.');
            header('Location: ' . ADMIN_PATH . '/articles');
            exit;
        }

        $newStatus = $article['status'] === 'published' ? 'draft' : 'published';
        $articleModel->updateStatus($id, $newStatus);
        (new CacheModel())->invalidatePrefix('api.articles.');
        if ($newStatus === 'published') {
            $updatedArticle = $articleModel->getById($id);
            if ($updatedArticle !== null) {
                $seoModel = new SeoAnalysisModel();
                $seoModel->analyzeAndStore($id, (string) $updatedArticle['title'], (string) $updatedArticle['content']);
                $this->notifyPublishedArticle((string) $updatedArticle['title']);
            }
        }
        Session::setFlash('success', 'Statut mis a jour : ' . $newStatus . '.');
        header('Location: ' . ADMIN_PATH . '/articles');
        exit;
    }

    private function collectFormData(): array
    {
        return [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'slug' => trim((string) ($_POST['slug'] ?? '')),
            'excerpt' => trim((string) ($_POST['excerpt'] ?? '')),
            'content' => trim((string) ($_POST['content'] ?? '')),
            'category_id' => trim((string) ($_POST['category_id'] ?? '')),
            'author_id' => trim((string) ($_POST['author_id'] ?? '1')),
            'status' => trim((string) ($_POST['status'] ?? 'draft')),
            'cover_image' => trim((string) ($_POST['cover_image'] ?? '')),
            'cover_alt' => trim((string) ($_POST['cover_alt'] ?? '')),
            'tags_input' => trim((string) ($_POST['tags_input'] ?? '')),
        ];
    }

    private function validate(array $data, CategoryModel $categoryModel, AuthorModel $authorModel, bool $isEdit = false): array
    {
        $validator = new Validator();
        $errors = $validator->validate(
            $data,
            [
                'title' => 'required|max:255',
                'slug' => 'max:200',
                'excerpt' => 'required|max:300',
                'content' => 'required',
                'status' => 'required|in:draft,published,archived',
                'cover_alt' => 'required|max:255',
                'tags_input' => 'max:500',
            ],
            [
                'title.required' => 'Le titre est obligatoire.',
                'title.max' => 'Le titre doit contenir au maximum 255 caracteres.',
                'slug.max' => 'Le slug doit contenir au maximum 200 caracteres.',
                'excerpt.required' => 'L extrait est obligatoire.',
                'excerpt.max' => 'L extrait doit contenir au maximum 300 caracteres.',
                'content.required' => 'Le contenu est obligatoire.',
                'status.in' => 'Statut invalide.',
                'cover_alt.required' => 'Le texte alternatif (alt) est obligatoire.',
                'tags_input.max' => 'La liste de tags est trop longue.',
            ]
        );

        $categoryId = (int) $data['category_id'];
        if ($categoryId <= 0 || !$categoryModel->exists($categoryId)) {
            $errors['category_id'] = 'Categorie invalide.';
        }

        $authorId = (int) $data['author_id'];
        if ($authorId <= 0 || $authorModel->getById($authorId) === null) {
            $errors['author_id'] = 'Auteur invalide.';
        }

        if ($data['cover_image'] !== '' && filter_var($data['cover_image'], FILTER_VALIDATE_URL) === false) {
            if (!str_starts_with($data['cover_image'], '/')) {
                $errors['cover_image'] = 'L URL de couverture doit etre absolue ou commencer par /.';
            }
        }

        return $errors;
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

    private function parseTagsInput(string $input): array
    {
        $tags = array_map('trim', explode(',', $input));
        $tags = array_filter($tags, static fn(string $name): bool => $name !== '');
        $tags = array_map(static fn(string $name): string => mb_substr($name, 0, 100), $tags);
        return array_values(array_unique($tags));
    }

    private function sanitizePage(int $page): int
    {
        return max(1, $page);
    }

    private function notifyPublishedArticle(string $articleTitle): void
    {
        $title = Helpers::truncate($articleTitle, 140);
        $notificationModel = new NotificationModel();
        $notificationModel->broadcast('article', 'Nouvel article publie: ' . $title, false);

        $subscriberModel = new SubscriberModel();
        $targets = $subscriberModel->getNotificationTargets(60, false);
        foreach ($targets as $target) {
            $email = (string) ($target['email'] ?? '');
            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                continue;
            }
            @mail(
                $email,
                'Nouvel article - ' . APP_NAME,
                "Un nouvel article vient d etre publie:\n\n" . $title . "\n\n" . BASE_URL . '/articles'
            );
        }
    }
}





