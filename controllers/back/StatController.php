<?php

declare(strict_types=1);

namespace App\Controllers\Back;

use App\Core\Session;
use App\Core\Validator;
use App\Models\CacheModel;
use App\Models\StatModel;

final class StatController
{
    public function list(): void
    {
        Session::requireAdmin();

        $statModel = new StatModel();
        $filters = [
            'type' => trim((string) ($_GET['type'] ?? '')),
            'date_from' => trim((string) ($_GET['date_from'] ?? '')),
            'date_to' => trim((string) ($_GET['date_to'] ?? '')),
        ];
        $stats = $statModel->getAll($filters, 400);
        $statSeries = $statModel->getSeriesForChart($filters);
        $allowedTypes = $statModel->getAllowedTypes();
        $seo = ['title' => 'Gestion statistiques'];
        $adminPage = 'stats';

        include ROOT . '/views/back/layouts/header.php';
        include ROOT . '/views/back/stats/list.php';
        include ROOT . '/views/back/layouts/footer.php';
    }

    public function create(): void
    {
        Session::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Location: ' . ADMIN_PATH . '/stats');
            exit;
        }
        $this->verifyCsrf();

        $statModel = new StatModel();
        $data = $this->collectFormData();
        $errors = $this->validate($data, $statModel->getAllowedTypes());
        if ($errors !== []) {
            Session::setFlash('error', implode(' ', array_values($errors)));
            header('Location: ' . ADMIN_PATH . '/stats');
            exit;
        }

        $statModel->upsert($data);
        (new CacheModel())->invalidatePrefix('api.stats.');
        Session::setFlash('success', 'Statistique enregistree.');
        header('Location: ' . ADMIN_PATH . '/stats');
        exit;
    }

    public function edit(int $id): void
    {
        Session::requireAdmin();

        $statModel = new StatModel();
        $stat = $statModel->getById($id);
        if ($stat === null) {
            Session::setFlash('error', 'Statistique introuvable.');
            header('Location: ' . ADMIN_PATH . '/stats');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $data = $this->collectFormData();
            $errors = $this->validate($data, $statModel->getAllowedTypes());
            if ($errors !== []) {
                Session::setFlash('error', implode(' ', array_values($errors)));
                header('Location: ' . ADMIN_PATH . '/stats/edit/' . $id);
                exit;
            }

            $statModel->update($id, $data);
            (new CacheModel())->invalidatePrefix('api.stats.');
            Session::setFlash('success', 'Statistique mise a jour.');
            header('Location: ' . ADMIN_PATH . '/stats');
            exit;
        }

        $allowedTypes = $statModel->getAllowedTypes();
        $seo = ['title' => 'Modifier statistique'];
        $adminPage = 'stats';

        include ROOT . '/views/back/layouts/header.php';
        include ROOT . '/views/back/stats/edit.php';
        include ROOT . '/views/back/layouts/footer.php';
    }

    public function delete(int $id): void
    {
        Session::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Location: ' . ADMIN_PATH . '/stats');
            exit;
        }
        $this->verifyCsrf();

        $statModel = new StatModel();
        $statModel->delete($id);
        (new CacheModel())->invalidatePrefix('api.stats.');
        Session::setFlash('success', 'Statistique supprimee.');
        header('Location: ' . ADMIN_PATH . '/stats');
        exit;
    }

    private function collectFormData(): array
    {
        return [
            'type' => trim((string) ($_POST['type'] ?? 'pertes')),
            'value' => trim((string) ($_POST['value'] ?? '0')),
            'stat_date' => trim((string) ($_POST['stat_date'] ?? '')),
        ];
    }

    private function validate(array $data, array $allowedTypes): array
    {
        $validator = new Validator();
        $errors = $validator->validate(
            $data,
            [
                'type' => 'required',
                'value' => 'required',
                'stat_date' => 'required',
            ]
        );

        if (!in_array($data['type'], $allowedTypes, true)) {
            $errors['type'] = 'Type invalide.';
        }

        if (filter_var($data['value'], FILTER_VALIDATE_INT) === false || (int) $data['value'] < 0) {
            $errors['value'] = 'Valeur invalide.';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['stat_date']) !== 1) {
            $errors['stat_date'] = 'Date invalide.';
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
