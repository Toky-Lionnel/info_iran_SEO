<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Core\Helpers;
use App\Core\Session;
use App\Models\ArticleModel;
use App\Models\CategoryModel;
use App\Models\CommunityModel;
use App\Models\FavoriteModel;
use App\Models\TagModel;

final class ArticleController
{
    public function list(int $page = 1): void
    {
        $articleModel = new ArticleModel();
        $categoryModel = new CategoryModel();

        $page = $this->sanitizePage($page);
        $query = trim((string) ($_GET['q'] ?? ''));
        $limit = ARTICLES_PER_PAGE;

        $total = $articleModel->countPublished($query);
        $pager = Helpers::paginate($total, $limit, $page);
        $page = $pager['current_page'];
        $offset = $pager['offset'];
        $articles = $articleModel->getPublished($limit, $offset, $query);
        $totalPages = $pager['total_pages'];
        $categories = $categoryModel->getAll();

        $titlePage = $page > 1 ? ' - page ' . $page : '';
        $seo = [
            'title' => 'Articles Guerre Iran 2024-2026' . $titlePage,
            'description' => $query !== ''
                ? 'Resultats pour "' . $query . '" sur la guerre en Iran.'
                : 'Tous les articles : chronologie, analyses et impacts du conflit en Iran.',
            'canonical' => BASE_URL . '/articles' . ($page > 1 ? '?page=' . $page : ''),
            'og_type' => 'website',
            'og_image' => BASE_URL . '/public/images/logo.webp',
        ];

        $currentPage = 'articles';
        $extraCss = ['article.css', 'responsive.css'];
        $extraJs = ['search.js'];
        $navCategories = $categories;

        include ROOT . '/views/front/layouts/header.php';
        include ROOT . '/views/front/articles/list.php';
        include ROOT . '/views/front/layouts/footer.php';
    }

    public function detail(int $id, int $catId): void
    {
        $articleModel = new ArticleModel();
        $categoryModel = new CategoryModel();
        $communityModel = new CommunityModel();
        $tagModel = new TagModel();

        $article = $articleModel->getPublishedByIdAndCategory($id, $catId);
        if ($article === null) {
            (new ErrorController())->notFound('L article demande n existe pas.');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $formType = (string) ($_POST['form_type'] ?? '');

            if ($formType === 'article_comment') {
                $authorName = trim((string) ($_POST['author_name'] ?? ''));
                $authorEmail = trim((string) ($_POST['author_email'] ?? ''));
                $content = trim((string) ($_POST['content'] ?? ''));
                $rating = (int) ($_POST['rating'] ?? 0);

                if (Session::isSubscriber()) {
                    $authorName = (string) Session::get('subscriber_name', $authorName);
                    $authorEmail = (string) Session::get('subscriber_email', $authorEmail);
                }

                if (
                    $authorName === ''
                    || !filter_var($authorEmail, FILTER_VALIDATE_EMAIL)
                    || mb_strlen($content) < 10
                    || $rating < 1
                    || $rating > 5
                ) {
                    Session::setFlash('error', 'Commentaire invalide. Verifiez les champs du formulaire.');
                } else {
                    $communityModel->createArticleComment(
                        (int) $article['id'],
                        Session::isSubscriber() ? (int) Session::get('subscriber_id') : null,
                        Helpers::truncate($authorName, 120),
                        Helpers::truncate($authorEmail, 190),
                        Helpers::truncate($content, 2000),
                        $rating
                    );
                    Session::setFlash('success', 'Commentaire envoye pour moderation.');
                }
            } elseif ($formType === 'article_reply') {
                $commentId = (int) ($_POST['comment_id'] ?? 0);
                $authorName = trim((string) ($_POST['author_name'] ?? ''));
                $content = trim((string) ($_POST['content'] ?? ''));
                if (Session::isSubscriber()) {
                    $authorName = (string) Session::get('subscriber_name', $authorName);
                }

                if ($commentId <= 0 || $authorName === '' || mb_strlen($content) < 4) {
                    Session::setFlash('error', 'Reponse invalide.');
                } else {
                    $communityModel->createArticleReply(
                        $commentId,
                        Session::isSubscriber() ? (int) Session::get('subscriber_id') : null,
                        Helpers::truncate($authorName, 120),
                        Helpers::truncate($content, 1200)
                    );
                    Session::setFlash('success', 'Reponse publiee.');
                }
            } elseif ($formType === 'article_vote') {
                if (!Session::isSubscriber()) {
                    Session::setFlash('error', 'Connectez-vous pour voter.');
                } else {
                    $commentId = (int) ($_POST['comment_id'] ?? 0);
                    $vote = (int) ($_POST['vote'] ?? 0);
                    if ($commentId > 0 && in_array($vote, [-1, 1], true)) {
                        $communityModel->voteComment('article', $commentId, (int) Session::get('subscriber_id'), $vote);
                        Session::setFlash('success', 'Vote enregistre.');
                    } else {
                        Session::setFlash('error', 'Vote invalide.');
                    }
                }
            }

            header('Location: ' . BASE_URL . '/article-' . (int) $article['id'] . '-' . (int) $article['category_id'] . '.html#comments');
            exit;
        }

        $articleModel->incrementViews((int) $article['id']);
        $article = $articleModel->getPublishedByIdAndCategory($id, $catId) ?? $article;

        $relatedArticles = array_filter(
            $articleModel->getByCategoryId((int) $article['category_id'], 4, 0),
            static fn(array $row): bool => (int) $row['id'] !== (int) $article['id']
        );
        $articleTags = $tagModel->getByArticle((int) $article['id']);
        $articleComments = $communityModel->getApprovedArticleComments((int) $article['id'], 30);
        $articleReplies = [];
        foreach ($articleComments as $comment) {
            $commentId = (int) ($comment['id'] ?? 0);
            if ($commentId > 0) {
                $articleReplies[$commentId] = $communityModel->getArticleReplies($commentId, 20);
            }
        }
        $commentStats = $communityModel->getArticleRatingStats((int) $article['id']);
        $favoriteModel = new FavoriteModel();
        $favoritesCount = $favoriteModel->countForArticle((int) $article['id']);
        $isFavorite = Session::isSubscriber()
            ? $favoriteModel->isFavorite((int) Session::get('subscriber_id'), (int) $article['id'])
            : false;
        $articleImage = Helpers::resolveAssetUrl((string) ($article['cover_image'] ?? ''));

        $seo = [
            'title' => $article['title'],
            'description' => mb_substr((string) $article['excerpt'], 0, 160),
            'canonical' => BASE_URL . '/article-' . (int) $article['id'] . '-' . (int) $article['category_id'] . '.html',
            'og_type' => 'article',
            'og_image' => $articleImage,
        ];

        $schemaOrg = '<script type="application/ld+json">' . json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article['title'],
            'description' => $article['excerpt'],
            'image' => $articleImage,
            'datePublished' => $article['published_at'],
            'dateModified' => $article['updated_at'],
            'author' => [
                '@type' => 'Person',
                'name' => $article['author_name'],
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => APP_NAME,
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $seo['canonical'],
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';

        $currentPage = 'articles';
        $extraCss = ['article.css', 'responsive.css'];
        $extraJs = ['share.js', 'tts.js', 'favorites.js'];
        $navCategories = $categoryModel->getAll();

        include ROOT . '/views/front/layouts/header.php';
        include ROOT . '/views/front/articles/detail.php';
        include ROOT . '/views/front/layouts/footer.php';
    }

    private function sanitizePage(int $value): int
    {
        return max(1, $value);
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
