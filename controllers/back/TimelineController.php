<?php

declare(strict_types=1);

namespace App\Controllers\Back;

use App\Core\Session;
use App\Core\Validator;
use App\Models\CacheModel;
use App\Models\TimelineEventModel;

final class TimelineController
{
    public function list(): void
    {
        Session::requireAdmin();

        $timelineModel = new TimelineEventModel();
        $filters = [
            'category' => trim((string) ($_GET['category'] ?? '')),
            'date_from' => trim((string) ($_GET['date_from'] ?? '')),
            'date_to' => trim((string) ($_GET['date_to'] ?? '')),
        ];
        $timelineEvents = $timelineModel->getAll($filters, 300);
        $allowedCategories = $timelineModel->getAllowedCategories();
        $seo = ['title' => 'Gestion timeline'];
        $adminPage = 'timeline';

        include ROOT . '/views/back/layouts/header.php';
        include ROOT . '/views/back/timeline/list.php';
        include ROOT . '/views/back/layouts/footer.php';
    }

    public function create(): void
    {
        Session::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Location: ' . ADMIN_PATH . '/timeline');
            exit;
        }
        $this->verifyCsrf();

        $timelineModel = new TimelineEventModel();
        $data = $this->collectFormData();
        $errors = $this->validate($data, $timelineModel->getAllowedCategories());
        if ($errors !== []) {
            Session::setFlash('error', implode(' ', array_values($errors)));
            header('Location: ' . ADMIN_PATH . '/timeline');
            exit;
        }

        $timelineModel->create($data);
        (new CacheModel())->invalidatePrefix('api.timeline.');
        Session::setFlash('success', 'Element timeline ajoute.');
        header('Location: ' . ADMIN_PATH . '/timeline');
        exit;
    }

    public function edit(int $id): void
    {
        Session::requireAdmin();

        $timelineModel = new TimelineEventModel();
        $timelineEvent = $timelineModel->getById($id);
        if ($timelineEvent === null) {
            Session::setFlash('error', 'Element timeline introuvable.');
            header('Location: ' . ADMIN_PATH . '/timeline');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $data = $this->collectFormData();
            $errors = $this->validate($data, $timelineModel->getAllowedCategories());
            if ($errors !== []) {
                Session::setFlash('error', implode(' ', array_values($errors)));
                header('Location: ' . ADMIN_PATH . '/timeline/edit/' . $id);
                exit;
            }

            $timelineModel->update($id, $data);
            (new CacheModel())->invalidatePrefix('api.timeline.');
            Session::setFlash('success', 'Element timeline mis a jour.');
            header('Location: ' . ADMIN_PATH . '/timeline');
            exit;
        }

        $allowedCategories = $timelineModel->getAllowedCategories();
        $seo = ['title' => 'Modifier timeline'];
        $adminPage = 'timeline';

        include ROOT . '/views/back/layouts/header.php';
        include ROOT . '/views/back/timeline/edit.php';
        include ROOT . '/views/back/layouts/footer.php';
    }

    public function delete(int $id): void
    {
        Session::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Location: ' . ADMIN_PATH . '/timeline');
            exit;
        }
        $this->verifyCsrf();

        $timelineModel = new TimelineEventModel();
        $timelineModel->delete($id);
        (new CacheModel())->invalidatePrefix('api.timeline.');
        Session::setFlash('success', 'Element timeline supprime.');
        header('Location: ' . ADMIN_PATH . '/timeline');
        exit;
    }

    private function collectFormData(): array
    {
        return [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'category' => trim((string) ($_POST['category'] ?? 'militaire')),
            'event_date' => trim((string) ($_POST['event_date'] ?? '')),
        ];
    }

    private function validate(array $data, array $allowedCategories): array
    {
        $validator = new Validator();
        $errors = $validator->validate(
            $data,
            [
                'title' => 'required|max:255',
                'description' => 'required|max:5000',
                'category' => 'required',
                'event_date' => 'required',
            ]
        );

        if (!in_array($data['category'], $allowedCategories, true)) {
            $errors['category'] = 'Categorie invalide.';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['event_date']) !== 1) {
            $errors['event_date'] = 'Date invalide.';
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
}
