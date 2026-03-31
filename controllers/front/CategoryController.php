<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Core\Helpers;
use App\Models\ArticleModel;
use App\Models\CategoryModel;

final class CategoryController
{
    public function show(string $slug, int $page = 1): void
    {
        $categoryModel = new CategoryModel();
        $articleModel = new ArticleModel();

        $category = $categoryModel->getBySlug($slug);
        if ($category === null) {
            $this->renderNotFound();
            return;
        }

        $page = $this->sanitizePage($page);
        $limit = ARTICLES_PER_PAGE;
        $total = $articleModel->countByCategory($slug);
        $pager = Helpers::paginate($total, $limit, $page);
        $page = $pager['current_page'];
        $articles = $articleModel->getByCategorySlug($slug, $limit, $pager['offset']);
        $totalPages = $pager['total_pages'];
        $categories = $categoryModel->getAll();

        $seo = [
            'title' => 'Categorie ' . $category['name'] . ' - Guerre en Iran',
            'description' => 'Articles de la categorie ' . $category['name'] . ' sur la guerre en Iran.',
            'canonical' => BASE_URL . '/categorie/' . $category['slug'] . '/' . $page,
            'og_type' => 'website',
        ];

        $currentPage = 'articles';
        $extraCss = ['article.css', 'responsive.css'];
        $extraJs = [];
        $navCategories = $categories;

        include ROOT . '/views/front/layouts/header.php';
        include ROOT . '/views/front/categories/list.php';
        include ROOT . '/views/front/layouts/footer.php';
    }

    public function showById(int $id, int $page = 1): void
    {
        $categoryModel = new CategoryModel();
        $articleModel = new ArticleModel();
        $category = $categoryModel->getById($id);

        if ($category === null) {
            $this->renderNotFound();
            return;
        }

        $page = $this->sanitizePage($page);
        $limit = ARTICLES_PER_PAGE;
        $total = $articleModel->countByCategoryId((int) $category['id']);
        $pager = Helpers::paginate($total, $limit, $page);
        $page = $pager['current_page'];
        $articles = $articleModel->getByCategoryId((int) $category['id'], $limit, $pager['offset']);
        $totalPages = $pager['total_pages'];
        $categories = $categoryModel->getAll();

        $seo = [
            'title' => 'Categorie ' . $category['name'] . ' - Guerre en Iran',
            'description' => 'Articles de la categorie ' . $category['name'] . ' sur la guerre en Iran.',
            'canonical' => BASE_URL . '/categorie-' . (int) $category['id'] . '-' . $page . '.html',
            'og_type' => 'website',
        ];

        $currentPage = 'articles';
        $extraCss = ['article.css', 'responsive.css'];
        $extraJs = [];
        $navCategories = $categories;

        include ROOT . '/views/front/layouts/header.php';
        include ROOT . '/views/front/categories/list.php';
        include ROOT . '/views/front/layouts/footer.php';
    }

    private function sanitizePage(int $value): int
    {
        return max(1, $value);
    }

    private function renderNotFound(): void
    {
        (new ErrorController())->notFound('La categorie demandee est introuvable.');
    }
}
