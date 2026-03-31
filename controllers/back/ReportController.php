<?php

declare(strict_types=1);

namespace App\Controllers\Back;

use App\Core\Database;
use App\Core\Session;
use App\Models\ReportModel;
use App\Models\SubscriberModel;
use PDO;
use Throwable;

final class ReportController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        Session::requireAdmin();

        $reportModel = new ReportModel();
        $recentLogs = $reportModel->getRecentLogs(40);

        $seo = ['title' => 'Exports / Imports'];
        $adminPage = 'reports';

        include ROOT . '/views/back/layouts/header.php';
        include ROOT . '/views/back/reports/index.php';
        include ROOT . '/views/back/layouts/footer.php';
    }

    public function export(string $dataset): void
    {
        Session::requireAdmin();

        $allowed = [
            'articles',
            'subscribers',
            'contacts',
            'comments',
            'reviews',
            'events',
            'timeline',
            'stats',
            'analytics',
            'security',
            'favorites',
            'notifications',
        ];
        if (!in_array($dataset, $allowed, true)) {
            http_response_code(404);
            exit('Dataset inconnu');
        }

        $rows = $this->getDatasetRows($dataset);
        $reportModel = new ReportModel();
        $fileName = $dataset . '-report-' . date('Y-m-d-His') . '.csv';

        $reportModel->logExchange(
            Session::isAdmin() ? (int) Session::get('admin_id') : null,
            'export',
            $dataset,
            $fileName,
            count($rows),
            'success',
            'Export CSV compatible Excel.'
        );

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'wb');
        if ($output === false) {
            exit;
        }

        // UTF-8 BOM for Excel.
        fwrite($output, "\xEF\xBB\xBF");

        if (!empty($rows)) {
            fputcsv($output, array_keys($rows[0]), ';');
            foreach ($rows as $row) {
                fputcsv($output, $row, ';');
            }
        } else {
            fputcsv($output, ['message'], ';');
            fputcsv($output, ['Aucune donnee disponible'], ';');
        }

        fclose($output);
        exit;
    }

    public function importSubscribers(): void
    {
        Session::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Location: ' . ADMIN_PATH . '/reports');
            exit;
        }

        $this->verifyCsrf();

        if (!isset($_FILES['subscribers_file']) || !is_array($_FILES['subscribers_file'])) {
            Session::setFlash('error', 'Aucun fichier recu.');
            header('Location: ' . ADMIN_PATH . '/reports');
            exit;
        }

        $file = $_FILES['subscribers_file'];
        if ((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            Session::setFlash('error', 'Echec upload fichier.');
            header('Location: ' . ADMIN_PATH . '/reports');
            exit;
        }

        $size = (int) ($file['size'] ?? 0);
        $maxSize = 5 * 1024 * 1024; // 5 MB.
        if ($size <= 0 || $size > $maxSize) {
            Session::setFlash('error', 'Taille fichier invalide. Maximum autorise: 5 MB.');
            header('Location: ' . ADMIN_PATH . '/reports');
            exit;
        }

        $tmpPath = (string) ($file['tmp_name'] ?? '');
        $originalName = (string) ($file['name'] ?? 'subscribers-import.csv');
        if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
            Session::setFlash('error', 'Fichier temporaire invalide.');
            header('Location: ' . ADMIN_PATH . '/reports');
            exit;
        }

        if (!$this->isAllowedCsvFile($originalName, $tmpPath)) {
            Session::setFlash('error', 'Format invalide. Import autorise uniquement pour les fichiers CSV.');
            header('Location: ' . ADMIN_PATH . '/reports');
            exit;
        }

        $handle = fopen($tmpPath, 'rb');
        if ($handle === false) {
            Session::setFlash('error', 'Impossible de lire le fichier CSV.');
            header('Location: ' . ADMIN_PATH . '/reports');
            exit;
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            Session::setFlash('error', 'CSV vide.');
            header('Location: ' . ADMIN_PATH . '/reports');
            exit;
        }

        $delimiter = $this->detectCsvDelimiter($firstLine);
        rewind($handle);

        $header = fgetcsv($handle, 0, $delimiter);
        if ($header === false) {
            fclose($handle);
            Session::setFlash('error', 'CSV vide.');
            header('Location: ' . ADMIN_PATH . '/reports');
            exit;
        }

        $headerMap = array_map(static fn(mixed $value): string => mb_strtolower(trim((string) $value)), $header);
        if (isset($headerMap[0])) {
            $headerMap[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headerMap[0]) ?? $headerMap[0];
        }

        $requiredColumns = ['full_name', 'email', 'password'];
        foreach ($requiredColumns as $column) {
            if (!in_array($column, $headerMap, true)) {
                fclose($handle);
                Session::setFlash('error', 'Colonne manquante: ' . $column);
                header('Location: ' . ADMIN_PATH . '/reports');
                exit;
            }
        }

        $subscriberModel = new SubscriberModel();
        $created = 0;
        $skipped = 0;
        $errors = 0;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if ($this->isCsvRowEmpty($row)) {
                continue;
            }

            $data = $this->mapCsvRow($headerMap, $row);
            $fullName = trim((string) ($data['full_name'] ?? ''));
            $email = trim((string) ($data['email'] ?? ''));
            $password = (string) ($data['password'] ?? '');

            if ($fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($password) < 8) {
                $errors++;
                continue;
            }

            try {
                if ($subscriberModel->findByEmail($email) !== null) {
                    $skipped++;
                    continue;
                }

                $plan = trim((string) ($data['plan'] ?? 'free'));
                if (!in_array($plan, ['free', 'premium'], true)) {
                    $plan = 'free';
                }
                $isSubscribed = $this->csvBoolToInt($data['is_subscribed'] ?? ($plan === 'premium' ? '1' : '0'));
                if ($plan === 'free') {
                    $isSubscribed = 0;
                }

                $subscriberModel->create([
                    'full_name' => $fullName,
                    'email' => $email,
                    'password' => $password,
                    'phone' => trim((string) ($data['phone'] ?? '')),
                    'country' => trim((string) ($data['country'] ?? '')),
                    'city' => trim((string) ($data['city'] ?? '')),
                    'interest_area' => trim((string) ($data['interest_area'] ?? 'geopolitique')),
                    'bio' => trim((string) ($data['bio'] ?? '')),
                    'newsletter_optin' => $this->csvBoolToInt($data['newsletter_optin'] ?? '1'),
                    'points' => max(0, (int) ($data['points'] ?? 0)),
                    'avatar_url' => trim((string) ($data['avatar_url'] ?? '')),
                    'plan' => $plan,
                    'is_subscribed' => $isSubscribed,
                    'is_active' => $this->csvBoolToInt($data['is_active'] ?? '1'),
                ]);

                $created++;
            } catch (Throwable $exception) {
                $errors++;
            }
        }

        fclose($handle);

        $reportModel = new ReportModel();
        $reportModel->logExchange(
            Session::isAdmin() ? (int) Session::get('admin_id') : null,
            'import',
            'subscribers',
            $originalName,
            $created,
            $errors > 0 ? 'failed' : 'success',
            'Import abonnes: crees=' . $created . ', ignores=' . $skipped . ', erreurs=' . $errors
        );

        Session::setFlash(
            $errors > 0 ? 'error' : 'success',
            'Import termine. Crees: ' . $created . ' | Ignores: ' . $skipped . ' | Erreurs: ' . $errors
        );
        header('Location: ' . ADMIN_PATH . '/reports');
        exit;
    }

    private function getDatasetRows(string $dataset): array
    {
        return match ($dataset) {
            'articles' => $this->db->query(
                "SELECT a.id, a.title, a.slug, a.status, a.views, a.published_at,
                        c.name AS category_name, au.name AS author_name
                 FROM articles a
                 JOIN categories c ON c.id = a.category_id
                 JOIN authors au ON au.id = a.author_id
                 ORDER BY COALESCE(a.published_at, a.created_at) DESC"
            )->fetchAll(),
            'subscribers' => $this->db->query(
                "SELECT id, full_name, email, phone, country, city, interest_area, points, plan,
                        is_subscribed, is_active, last_login, created_at
                 FROM subscribers
                 ORDER BY created_at DESC"
            )->fetchAll(),
            'contacts' => $this->db->query(
                "SELECT id, full_name, email, subject, status, created_at
                 FROM contact_messages
                 ORDER BY created_at DESC"
            )->fetchAll(),
            'comments' => $this->db->query(
                "SELECT ac.id, ac.article_id, a.title AS article_title, ac.author_name, ac.author_email,
                        ac.rating, ac.status, ac.created_at
                 FROM article_comments ac
                 JOIN articles a ON a.id = ac.article_id
                 ORDER BY ac.created_at DESC"
            )->fetchAll(),
            'reviews' => $this->db->query(
                "SELECT id, author_name, rating, comment, status, created_at
                 FROM site_reviews
                 ORDER BY created_at DESC"
            )->fetchAll(),
            'events' => $this->db->query(
                "SELECT id, title, type, city, latitude, longitude, event_date, created_at
                 FROM events
                 ORDER BY event_date DESC"
            )->fetchAll(),
            'timeline' => $this->db->query(
                "SELECT id, title, category, event_date, created_at
                 FROM timeline_events
                 ORDER BY event_date DESC"
            )->fetchAll(),
            'stats' => $this->db->query(
                "SELECT id, type, value, stat_date, created_at
                 FROM stats
                 ORDER BY stat_date DESC"
            )->fetchAll(),
            'analytics' => $this->db->query(
                "SELECT id, page, user_id, duration, created_at
                 FROM analytics
                 ORDER BY created_at DESC"
            )->fetchAll(),
            'security' => $this->db->query(
                "SELECT id, ip, action, status, created_at
                 FROM security_logs
                 ORDER BY created_at DESC"
            )->fetchAll(),
            'favorites' => $this->db->query(
                "SELECT f.id, f.user_id, s.email AS user_email, f.article_id, a.title AS article_title, f.created_at
                 FROM article_favorites f
                 JOIN subscribers s ON s.id = f.user_id
                 JOIN articles a ON a.id = f.article_id
                 ORDER BY f.created_at DESC"
            )->fetchAll(),
            'notifications' => $this->db->query(
                "SELECT n.id, n.user_id, s.email AS user_email, n.type, n.message, n.is_read, n.created_at
                 FROM notifications n
                 JOIN subscribers s ON s.id = n.user_id
                 ORDER BY n.created_at DESC"
            )->fetchAll(),
            default => [],
        };
    }

    private function mapCsvRow(array $headerMap, array $row): array
    {
        $assoc = [];
        foreach ($headerMap as $index => $column) {
            $assoc[$column] = isset($row[$index]) ? trim((string) $row[$index]) : '';
        }
        return $assoc;
    }

    private function isAllowedCsvFile(string $originalName, string $tmpPath): bool
    {
        $extension = mb_strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
        if ($extension !== 'csv') {
            return false;
        }

        if (!function_exists('mime_content_type')) {
            return true;
        }

        $mimeType = mb_strtolower((string) mime_content_type($tmpPath));
        if ($mimeType === '') {
            return true;
        }

        $allowedMimeTypes = [
            'text/csv',
            'text/plain',
            'application/csv',
            'application/vnd.ms-excel',
            'text/comma-separated-values',
            'application/octet-stream',
            'inode/x-empty',
        ];

        return in_array($mimeType, $allowedMimeTypes, true);
    }

    private function detectCsvDelimiter(string $line): string
    {
        $commaCount = substr_count($line, ',');
        $semicolonCount = substr_count($line, ';');
        return $commaCount > $semicolonCount ? ',' : ';';
    }

    private function isCsvRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function csvBoolToInt(mixed $value): int
    {
        $stringValue = mb_strtolower(trim((string) $value));
        return in_array($stringValue, ['1', 'true', 'yes', 'oui', 'on'], true) ? 1 : 0;
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
