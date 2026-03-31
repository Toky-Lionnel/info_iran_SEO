<?php

declare(strict_types=1);

namespace App\Controllers\Back;

use App\Core\Helpers;
use App\Core\Session;
use App\Models\CommunityModel;
use App\Models\SubscriberModel;
use PDOException;

final class CommunityController
{
    public function index(): void
    {
        Session::requireAdmin();

        $communityModel = new CommunityModel();
        $subscriberModel = new SubscriberModel();

        $counts = $communityModel->getAdminCounts();
        $subscribers = $subscriberModel->getAll(50);
        $pendingComments = $communityModel->getPendingComments(50);
        $pendingDebateComments = $communityModel->getPendingDebateComments(50);
        $pendingReviews = $communityModel->getPendingSiteReviews(50);
        $contacts = $communityModel->getContacts(50);
        $journals = $communityModel->getAllJournals(50);
        $debates = $communityModel->getAllDebates(50);

        $seo = ['title' => 'Communaute et premium'];
        $adminPage = 'community';

        include ROOT . '/views/back/layouts/header.php';
        include ROOT . '/views/back/community/index.php';
        include ROOT . '/views/back/layouts/footer.php';
    }

    public function moderateComment(string $action, int $id): void
    {
        Session::requireAdmin();
        $this->verifyPostAndCsrf();

        $communityModel = new CommunityModel();
        if ($action === 'approve') {
            $ok = $communityModel->updateArticleCommentStatus($id, 'approved');
            Session::setFlash($ok ? 'success' : 'error', $ok ? 'Commentaire approuve.' : 'Operation impossible.');
        } elseif ($action === 'reject') {
            $ok = $communityModel->updateArticleCommentStatus($id, 'rejected');
            Session::setFlash($ok ? 'success' : 'error', $ok ? 'Commentaire rejete.' : 'Operation impossible.');
        } else {
            $ok = $communityModel->deleteArticleComment($id);
            Session::setFlash($ok ? 'success' : 'error', $ok ? 'Commentaire supprime.' : 'Suppression impossible.');
        }

        header('Location: ' . ADMIN_PATH . '/community');
        exit;
    }

    public function moderateDebateComment(string $action, int $id): void
    {
        Session::requireAdmin();
        $this->verifyPostAndCsrf();

        $communityModel = new CommunityModel();
        if ($action === 'approve') {
            $ok = $communityModel->updateDebateCommentStatus($id, 'approved');
            Session::setFlash($ok ? 'success' : 'error', $ok ? 'Commentaire debat approuve.' : 'Operation impossible.');
        } elseif ($action === 'reject') {
            $ok = $communityModel->updateDebateCommentStatus($id, 'rejected');
            Session::setFlash($ok ? 'success' : 'error', $ok ? 'Commentaire debat rejete.' : 'Operation impossible.');
        } else {
            $ok = $communityModel->deleteDebateComment($id);
            Session::setFlash($ok ? 'success' : 'error', $ok ? 'Commentaire debat supprime.' : 'Suppression impossible.');
        }

        header('Location: ' . ADMIN_PATH . '/community');
        exit;
    }

    public function moderateReview(string $action, int $id): void
    {
        Session::requireAdmin();
        $this->verifyPostAndCsrf();

        $communityModel = new CommunityModel();
        if ($action === 'approve') {
            $ok = $communityModel->updateSiteReviewStatus($id, 'approved');
            Session::setFlash($ok ? 'success' : 'error', $ok ? 'Avis approuve.' : 'Operation impossible.');
        } elseif ($action === 'reject') {
            $ok = $communityModel->updateSiteReviewStatus($id, 'rejected');
            Session::setFlash($ok ? 'success' : 'error', $ok ? 'Avis rejete.' : 'Operation impossible.');
        } else {
            $ok = $communityModel->deleteSiteReview($id);
            Session::setFlash($ok ? 'success' : 'error', $ok ? 'Avis supprime.' : 'Suppression impossible.');
        }

        header('Location: ' . ADMIN_PATH . '/community');
        exit;
    }

    public function toggleSubscriber(int $id): void
    {
        Session::requireAdmin();
        $this->verifyPostAndCsrf();

        $subscriberModel = new SubscriberModel();
        $ok = $subscriberModel->toggleSubscription($id);
        Session::setFlash($ok ? 'success' : 'error', $ok ? 'Abonnement premium mis a jour.' : 'Operation impossible.');
        header('Location: ' . ADMIN_PATH . '/community');
        exit;
    }

    public function toggleSubscriberActive(int $id): void
    {
        Session::requireAdmin();
        $this->verifyPostAndCsrf();

        $subscriberModel = new SubscriberModel();
        $ok = $subscriberModel->toggleActive($id);
        Session::setFlash($ok ? 'success' : 'error', $ok ? 'Statut du compte mis a jour.' : 'Operation impossible.');
        header('Location: ' . ADMIN_PATH . '/community');
        exit;
    }

    public function contactAction(string $action, int $id): void
    {
        Session::requireAdmin();
        $this->verifyPostAndCsrf();

        $communityModel = new CommunityModel();
        if ($action === 'read') {
            $ok = $communityModel->updateContactStatus($id, 'read');
            Session::setFlash($ok ? 'success' : 'error', $ok ? 'Message marque comme lu.' : 'Operation impossible.');
        } elseif ($action === 'close') {
            $ok = $communityModel->updateContactStatus($id, 'closed');
            Session::setFlash($ok ? 'success' : 'error', $ok ? 'Message cloture.' : 'Operation impossible.');
        } else {
            $ok = $communityModel->deleteContact($id);
            Session::setFlash($ok ? 'success' : 'error', $ok ? 'Message supprime.' : 'Suppression impossible.');
        }

        header('Location: ' . ADMIN_PATH . '/community');
        exit;
    }

    public function createJournal(): void
    {
        Session::requireAdmin();
        $this->verifyPostAndCsrf();

        $title = trim((string) ($_POST['journal_title'] ?? ''));
        $summary = trim((string) ($_POST['journal_summary'] ?? ''));
        $content = trim((string) ($_POST['journal_content'] ?? ''));
        $pinnedOrder = (int) ($_POST['journal_pinned_order'] ?? 0);
        $status = trim((string) ($_POST['journal_status'] ?? 'published'));

        if ($title === '' || mb_strlen($summary) < 10 || mb_strlen($content) < 30) {
            Session::setFlash('error', 'Journal invalide. Champs incomplets.');
            header('Location: ' . ADMIN_PATH . '/community');
            exit;
        }

        $communityModel = new CommunityModel();
        try {
            $communityModel->createJournal([
                'slug' => Helpers::slugify($title, 150),
                'title' => Helpers::truncate($title, 255),
                'summary' => Helpers::truncate($summary, 1000),
                'content' => Helpers::sanitizeHtml($content),
                'pinned_order' => max(0, $pinnedOrder),
                'status' => in_array($status, ['draft', 'published'], true) ? $status : 'published',
                'published_at' => date('Y-m-d H:i:s'),
            ]);
            Session::setFlash('success', 'Journal epingle ajoute.');
        } catch (PDOException $exception) {
            Session::setFlash('error', 'Creation impossible (slug deja utilise ou erreur SQL).');
        }
        header('Location: ' . ADMIN_PATH . '/community');
        exit;
    }

    public function deleteJournal(int $id): void
    {
        Session::requireAdmin();
        $this->verifyPostAndCsrf();

        $communityModel = new CommunityModel();
        $ok = $communityModel->deleteJournal($id);
        Session::setFlash($ok ? 'success' : 'error', $ok ? 'Journal supprime.' : 'Suppression impossible.');
        header('Location: ' . ADMIN_PATH . '/community');
        exit;
    }

    public function createDebate(): void
    {
        Session::requireAdmin();
        $this->verifyPostAndCsrf();

        $title = trim((string) ($_POST['debate_title'] ?? ''));
        $summary = trim((string) ($_POST['debate_summary'] ?? ''));
        $body = trim((string) ($_POST['debate_body'] ?? ''));
        $status = trim((string) ($_POST['debate_status'] ?? 'open'));
        $isPinned = (int) ($_POST['debate_is_pinned'] ?? 0);

        if ($title === '' || mb_strlen($summary) < 10 || mb_strlen($body) < 30) {
            Session::setFlash('error', 'Debat invalide. Champs incomplets.');
            header('Location: ' . ADMIN_PATH . '/community');
            exit;
        }

        $communityModel = new CommunityModel();
        try {
            $communityModel->createDebate([
                'slug' => Helpers::slugify($title, 150),
                'title' => Helpers::truncate($title, 255),
                'summary' => Helpers::truncate($summary, 1000),
                'body' => Helpers::sanitizeHtml($body),
                'status' => in_array($status, ['open', 'closed'], true) ? $status : 'open',
                'is_pinned' => $isPinned === 1 ? 1 : 0,
            ]);
            Session::setFlash('success', 'Debat ajoute.');
        } catch (PDOException $exception) {
            Session::setFlash('error', 'Creation impossible (slug deja utilise ou erreur SQL).');
        }
        header('Location: ' . ADMIN_PATH . '/community');
        exit;
    }

    public function deleteDebate(int $id): void
    {
        Session::requireAdmin();
        $this->verifyPostAndCsrf();

        $communityModel = new CommunityModel();
        $ok = $communityModel->deleteDebate($id);
        Session::setFlash($ok ? 'success' : 'error', $ok ? 'Debat supprime.' : 'Suppression impossible.');
        header('Location: ' . ADMIN_PATH . '/community');
        exit;
    }

    private function verifyPostAndCsrf(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Location: ' . ADMIN_PATH . '/community');
            exit;
        }

        $sessionToken = (string) ($_SESSION['csrf_token'] ?? '');
        $postedToken = (string) ($_POST['csrf_token'] ?? '');
        if ($sessionToken === '' || !hash_equals($sessionToken, $postedToken)) {
            http_response_code(403);
            exit('Token CSRF invalide');
        }
    }
}
