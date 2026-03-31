<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Core\Helpers;
use App\Core\Session;
use App\Models\ArticleModel;
use App\Models\CategoryModel;
use App\Models\CommunityModel;
use App\Models\SubscriberModel;
use Throwable;

final class PortalController
{
    public function nouveautes(): void
    {
        $articleModel = new ArticleModel();
        $communityModel = new CommunityModel();
        $categoryModel = new CategoryModel();

        $latestArticles = $articleModel->getPublished(12, 0);
        $latestJournals = $communityModel->getPublishedJournals(6);
        $latestDebates = $communityModel->getPublishedDebates(6);

        $seo = [
            'title' => 'Nouveautes - Informations verifiees',
            'description' => 'Consultez les dernieres publications, journaux epingles et debats ouverts.',
            'canonical' => BASE_URL . '/nouveautes',
            'og_type' => 'website',
        ];
        $currentPage = 'nouveautes';
        $extraCss = ['article.css', 'portal.css', 'responsive.css'];
        $extraJs = [];
        $navCategories = $categoryModel->getAll();

        include ROOT . '/views/front/layouts/header.php';
        include ROOT . '/views/front/portal/nouveautes.php';
        include ROOT . '/views/front/layouts/footer.php';
    }

    public function debates(): void
    {
        $communityModel = new CommunityModel();
        $categoryModel = new CategoryModel();
        $debates = $communityModel->getPublishedDebates(24);

        $seo = [
            'title' => 'Debats publics',
            'description' => 'Discussions ouvertes sur les scenarios, la diplomatie et les impacts regionaux.',
            'canonical' => BASE_URL . '/debats',
            'og_type' => 'website',
        ];
        $currentPage = 'debats';
        $extraCss = ['portal.css', 'responsive.css'];
        $extraJs = [];
        $navCategories = $categoryModel->getAll();

        include ROOT . '/views/front/layouts/header.php';
        include ROOT . '/views/front/portal/debats.php';
        include ROOT . '/views/front/layouts/footer.php';
    }

    public function debateDetail(string $slug): void
    {
        $communityModel = new CommunityModel();
        $categoryModel = new CategoryModel();
        $debate = $communityModel->getDebateBySlug($slug);
        if ($debate === null) {
            (new ErrorController())->notFound('Debat introuvable.');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $formType = (string) ($_POST['form_type'] ?? 'debate_comment');

            if ($formType === 'debate_comment') {
                $authorName = trim((string) ($_POST['author_name'] ?? ''));
                $content = trim((string) ($_POST['content'] ?? ''));

                if (Session::isSubscriber()) {
                    $authorName = (string) Session::get('subscriber_name', $authorName);
                }

                if ($authorName === '' || mb_strlen($authorName) < 2 || mb_strlen($content) < 8) {
                    Session::setFlash('error', 'Commentaire invalide. Minimum 8 caracteres.');
                } else {
                    $communityModel->createDebateComment(
                        (int) $debate['id'],
                        Session::isSubscriber() ? (int) Session::get('subscriber_id') : null,
                        Helpers::truncate($authorName, 100),
                        Helpers::truncate($content, 2000),
                        'pending'
                    );
                    Session::setFlash('success', 'Commentaire envoye pour moderation.');
                }
            } elseif ($formType === 'debate_reply') {
                $commentId = (int) ($_POST['comment_id'] ?? 0);
                $authorName = trim((string) ($_POST['author_name'] ?? ''));
                $content = trim((string) ($_POST['content'] ?? ''));

                if (Session::isSubscriber()) {
                    $authorName = (string) Session::get('subscriber_name', $authorName);
                }

                if ($commentId <= 0 || $authorName === '' || mb_strlen($content) < 4) {
                    Session::setFlash('error', 'Reponse invalide.');
                } else {
                    $communityModel->createDebateReply(
                        $commentId,
                        Session::isSubscriber() ? (int) Session::get('subscriber_id') : null,
                        Helpers::truncate($authorName, 100),
                        Helpers::truncate($content, 1200)
                    );
                    Session::setFlash('success', 'Reponse publiee.');
                }
            } elseif ($formType === 'debate_vote') {
                if (!Session::isSubscriber()) {
                    Session::setFlash('error', 'Connectez-vous pour voter.');
                } else {
                    $commentId = (int) ($_POST['comment_id'] ?? 0);
                    $vote = (int) ($_POST['vote'] ?? 0);
                    if ($commentId > 0 && in_array($vote, [-1, 1], true)) {
                        $communityModel->voteComment('debat', $commentId, (int) Session::get('subscriber_id'), $vote);
                        Session::setFlash('success', 'Vote enregistre.');
                    } else {
                        Session::setFlash('error', 'Vote invalide.');
                    }
                }
            }

            header('Location: ' . BASE_URL . '/debat/' . rawurlencode($slug) . '#debate-comments');
            exit;
        }

        $debateComments = $communityModel->getDebateComments((int) $debate['id']);
        $debateReplies = [];
        foreach ($debateComments as $comment) {
            $commentId = (int) ($comment['id'] ?? 0);
            if ($commentId > 0) {
                $debateReplies[$commentId] = $communityModel->getDebateReplies($commentId, 20);
            }
        }
        $seo = [
            'title' => (string) $debate['title'],
            'description' => Helpers::truncate((string) $debate['summary'], 160),
            'canonical' => BASE_URL . '/debat/' . $slug,
            'og_type' => 'article',
        ];
        $currentPage = 'debats';
        $extraCss = ['portal.css', 'responsive.css'];
        $extraJs = [];
        $navCategories = $categoryModel->getAll();

        include ROOT . '/views/front/layouts/header.php';
        include ROOT . '/views/front/portal/debat-detail.php';
        include ROOT . '/views/front/layouts/footer.php';
    }

    public function journals(): void
    {
        $communityModel = new CommunityModel();
        $categoryModel = new CategoryModel();
        $journals = $communityModel->getPublishedJournals(20);

        $seo = [
            'title' => 'Journaux epingles',
            'description' => 'Dossiers de fond, analyses longues et syntheses editoriales prioritaires.',
            'canonical' => BASE_URL . '/journaux',
            'og_type' => 'website',
        ];
        $currentPage = 'journaux';
        $extraCss = ['portal.css', 'responsive.css'];
        $extraJs = [];
        $navCategories = $categoryModel->getAll();

        include ROOT . '/views/front/layouts/header.php';
        include ROOT . '/views/front/portal/journaux.php';
        include ROOT . '/views/front/layouts/footer.php';
    }

    public function archives(): void
    {
        $articleModel = new ArticleModel();
        $categoryModel = new CategoryModel();

        $archiveBuckets = $articleModel->getArchiveBuckets();
        $selectedMonth = trim((string) ($_GET['month'] ?? ''));
        $archiveArticles = [];

        if (preg_match('/^(\d{4})-(\d{2})$/', $selectedMonth, $m) === 1) {
            $year = (int) $m[1];
            $month = (int) $m[2];
            if ($year >= 2020 && $year <= 2100 && $month >= 1 && $month <= 12) {
                $archiveArticles = $articleModel->getPublishedByYearMonth($year, $month, 50, 0);
            }
        }

        $seo = [
            'title' => 'Archives editoriales',
            'description' => 'Retrouvez les publications par mois pour suivre l evolution du conflit.',
            'canonical' => BASE_URL . '/archives',
            'og_type' => 'website',
        ];
        $currentPage = 'archives';
        $extraCss = ['article.css', 'portal.css', 'responsive.css'];
        $extraJs = [];
        $navCategories = $categoryModel->getAll();

        include ROOT . '/views/front/layouts/header.php';
        include ROOT . '/views/front/portal/archives.php';
        include ROOT . '/views/front/layouts/footer.php';
    }

    public function contact(): void
    {
        $categoryModel = new CategoryModel();
        $communityModel = new CommunityModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();

            $fullName = trim((string) ($_POST['full_name'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));
            $subject = trim((string) ($_POST['subject'] ?? ''));
            $message = trim((string) ($_POST['message'] ?? ''));

            if (
                mb_strlen($fullName) < 3
                || !filter_var($email, FILTER_VALIDATE_EMAIL)
                || mb_strlen($subject) < 5
                || mb_strlen($message) < 20
            ) {
                Session::setFlash('error', 'Formulaire invalide. Merci de completer tous les champs.');
            } else {
                $communityModel->createContactMessage([
                    'full_name' => Helpers::truncate($fullName, 120),
                    'email' => Helpers::truncate($email, 190),
                    'subject' => Helpers::truncate($subject, 160),
                    'message' => Helpers::truncate($message, 4000),
                ]);
                Session::setFlash('success', 'Message envoye. Notre redaction vous repondra rapidement.');
            }

            header('Location: ' . BASE_URL . '/contact');
            exit;
        }

        $seo = [
            'title' => 'Contact redaction',
            'description' => 'Contactez la redaction pour un signalement, une correction ou un partenariat.',
            'canonical' => BASE_URL . '/contact',
            'og_type' => 'website',
        ];
        $currentPage = 'contact';
        $extraCss = ['portal.css', 'responsive.css'];
        $extraJs = [];
        $navCategories = $categoryModel->getAll();

        include ROOT . '/views/front/layouts/header.php';
        include ROOT . '/views/front/portal/contact.php';
        include ROOT . '/views/front/layouts/footer.php';
    }

    public function premium(): void
    {
        $categoryModel = new CategoryModel();

        if (!Session::isSubscriber()) {
            Session::setFlash('error', 'Connectez-vous avec un compte abonne pour acceder a cet espace.');
            header('Location: ' . BASE_URL . '/compte/login');
            exit;
        }

        $subscriberModel = new SubscriberModel();
        $subscriber = $subscriberModel->getById((int) Session::get('subscriber_id'));
        if ($subscriber === null) {
            Session::clearSubscriber();
            Session::setFlash('error', 'Votre session abonne est invalide. Merci de vous reconnecter.');
            header('Location: ' . BASE_URL . '/compte/login');
            exit;
        }

        Session::setSubscriber($subscriber);

        if (!Session::isPremiumSubscriber()) {
            (new ErrorController())->forbidden(
                'Votre compte existe, mais l acces premium est reserve aux abonnes actifs. Contactez la redaction.'
            );
            return;
        }

        $seo = [
            'title' => 'Espace abonnes premium',
            'description' => 'Analyses exclusives, notes strategiques et dossiers reserves aux abonnes.',
            'canonical' => BASE_URL . '/abonnes',
            'og_type' => 'website',
        ];
        $currentPage = 'abonnes';
        $extraCss = ['portal.css', 'responsive.css'];
        $extraJs = [];
        $navCategories = $categoryModel->getAll();

        include ROOT . '/views/front/layouts/header.php';
        include ROOT . '/views/front/portal/abonnes.php';
        include ROOT . '/views/front/layouts/footer.php';
    }

    public function createReview(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/');
            exit;
        }

        $this->verifyCsrf();
        $communityModel = new CommunityModel();

        $authorName = trim((string) ($_POST['author_name'] ?? ''));
        $comment = trim((string) ($_POST['comment'] ?? ''));
        $rating = (int) ($_POST['rating'] ?? 0);

        if (Session::isSubscriber()) {
            $authorName = (string) Session::get('subscriber_name', $authorName);
        }

        if ($authorName === '' || mb_strlen($comment) < 12 || $rating < 1 || $rating > 5) {
            Session::setFlash('error', 'Avis invalide. Merci de completer correctement le formulaire.');
        } else {
            $communityModel->createSiteReview(
                Session::isSubscriber() ? (int) Session::get('subscriber_id') : null,
                Helpers::truncate($authorName, 120),
                $rating,
                Helpers::truncate($comment, 1200)
            );
            Session::setFlash('success', 'Merci pour votre avis. Il sera publie apres moderation.');
        }

        header('Location: ' . BASE_URL . '/#avis-utilisateurs');
        exit;
    }

    public function logShare(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
            return;
        }

        $sessionToken = (string) ($_SESSION['csrf_token'] ?? '');
        $postedToken = (string) ($_POST['csrf_token'] ?? '');
        if ($sessionToken === '' || !hash_equals($sessionToken, $postedToken)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => 'Invalid CSRF token']);
            return;
        }

        $articleId = (int) ($_POST['article_id'] ?? 0);
        $channel = mb_strtolower(trim((string) ($_POST['channel'] ?? '')));
        if ($articleId <= 0 || $channel === '') {
            http_response_code(422);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => 'Invalid payload']);
            return;
        }

        $articleModel = new ArticleModel();
        $article = $articleModel->getById($articleId);
        if ($article === null || (string) ($article['status'] ?? '') !== 'published') {
            http_response_code(422);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => 'Unknown article']);
            return;
        }

        $communityModel = new CommunityModel();
        try {
            $ok = $communityModel->logArticleShare($articleId, $channel);
        } catch (Throwable $exception) {
            $ok = false;
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => $ok]);
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
