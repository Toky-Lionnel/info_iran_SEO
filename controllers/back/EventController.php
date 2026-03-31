<?php

declare(strict_types=1);

namespace App\Controllers\Back;

use App\Core\Session;
use App\Core\Validator;
use App\Models\CacheModel;
use App\Models\EventModel;

final class EventController
{
    public function list(): void
    {
        Session::requireAdmin();

        $eventModel = new EventModel();
        $filters = [
            'type' => trim((string) ($_GET['type'] ?? '')),
            'date_from' => trim((string) ($_GET['date_from'] ?? '')),
            'date_to' => trim((string) ($_GET['date_to'] ?? '')),
        ];
        $events = $eventModel->getAll($filters, 300);
        $allowedTypes = $eventModel->getAllowedTypes();
        $seo = ['title' => 'Gestion carte evenements'];
        $adminPage = 'events';

        include ROOT . '/views/back/layouts/header.php';
        include ROOT . '/views/back/events/list.php';
        include ROOT . '/views/back/layouts/footer.php';
    }

    public function create(): void
    {
        Session::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Location: ' . ADMIN_PATH . '/events');
            exit;
        }

        $this->verifyCsrf();

        $eventModel = new EventModel();
        $data = $this->collectFormData();
        $errors = $this->validate($data, $eventModel->getAllowedTypes());
        if ($errors !== []) {
            Session::setFlash('error', implode(' ', array_values($errors)));
            header('Location: ' . ADMIN_PATH . '/events');
            exit;
        }

        $eventModel->create($data);
        (new CacheModel())->invalidatePrefix('api.events.');
        Session::setFlash('success', 'Evenement ajoute.');
        header('Location: ' . ADMIN_PATH . '/events');
        exit;
    }

    public function edit(int $id): void
    {
        Session::requireAdmin();

        $eventModel = new EventModel();
        $event = $eventModel->getById($id);
        if ($event === null) {
            Session::setFlash('error', 'Evenement introuvable.');
            header('Location: ' . ADMIN_PATH . '/events');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $data = $this->collectFormData();
            $errors = $this->validate($data, $eventModel->getAllowedTypes());
            if ($errors !== []) {
                Session::setFlash('error', implode(' ', array_values($errors)));
                header('Location: ' . ADMIN_PATH . '/events/edit/' . $id);
                exit;
            }

            $eventModel->update($id, $data);
            (new CacheModel())->invalidatePrefix('api.events.');
            Session::setFlash('success', 'Evenement mis a jour.');
            header('Location: ' . ADMIN_PATH . '/events');
            exit;
        }

        $allowedTypes = $eventModel->getAllowedTypes();
        $seo = ['title' => 'Modifier evenement'];
        $adminPage = 'events';

        include ROOT . '/views/back/layouts/header.php';
        include ROOT . '/views/back/events/edit.php';
        include ROOT . '/views/back/layouts/footer.php';
    }

    public function delete(int $id): void
    {
        Session::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Location: ' . ADMIN_PATH . '/events');
            exit;
        }
        $this->verifyCsrf();

        $eventModel = new EventModel();
        $eventModel->delete($id);
        (new CacheModel())->invalidatePrefix('api.events.');
        Session::setFlash('success', 'Evenement supprime.');
        header('Location: ' . ADMIN_PATH . '/events');
        exit;
    }

    private function collectFormData(): array
    {
        return [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'type' => trim((string) ($_POST['type'] ?? 'militaire')),
            'latitude' => trim((string) ($_POST['latitude'] ?? '')),
            'longitude' => trim((string) ($_POST['longitude'] ?? '')),
            'city' => trim((string) ($_POST['city'] ?? '')),
            'event_date' => trim((string) ($_POST['event_date'] ?? '')),
        ];
    }

    private function validate(array $data, array $allowedTypes): array
    {
        $validator = new Validator();
        $errors = $validator->validate(
            $data,
            [
                'title' => 'required|max:255',
                'description' => 'required|max:5000',
                'type' => 'required',
                'city' => 'required|max:120',
                'event_date' => 'required',
            ]
        );

        if (!in_array($data['type'], $allowedTypes, true)) {
            $errors['type'] = 'Type invalide.';
        }

        $lat = filter_var($data['latitude'], FILTER_VALIDATE_FLOAT);
        $lng = filter_var($data['longitude'], FILTER_VALIDATE_FLOAT);
        if ($lat === false || $lat < -90 || $lat > 90) {
            $errors['latitude'] = 'Latitude invalide.';
        }
        if ($lng === false || $lng < -180 || $lng > 180) {
            $errors['longitude'] = 'Longitude invalide.';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}(?:[ T]+\d{2}:\d{2}(?::\d{2})?)?$/', $data['event_date']) !== 1) {
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
