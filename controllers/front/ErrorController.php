<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Models\CategoryModel;

final class ErrorController
{
    public function notFound(string $message = 'La page demandee est introuvable.'): void
    {
        http_response_code(404);
        $this->render(
            404,
            'Page introuvable',
            $message,
            ROOT . '/views/front/errors/404.php'
        );
    }

    public function forbidden(string $message = 'Vous n avez pas les permissions necessaires pour cette page.'): void
    {
        http_response_code(403);
        $this->render(
            403,
            'Acces refuse',
            $message,
            ROOT . '/views/front/errors/403.php'
        );
    }

    private function render(int $statusCode, string $title, string $message, string $viewPath): void
    {
        $categoryModel = new CategoryModel();
        $seo = [
            'title' => $title . ' | ' . APP_NAME,
            'description' => $message,
            'canonical' => BASE_URL . '/' . $statusCode,
            'og_type' => 'website',
        ];
        $currentPage = '';
        $extraCss = ['responsive.css'];
        $extraJs = [];
        $navCategories = $categoryModel->getAll();
        $errorTitle = $title;
        $errorMessage = $message;

        include ROOT . '/views/front/layouts/header.php';
        include $viewPath;
        include ROOT . '/views/front/layouts/footer.php';
    }
}
