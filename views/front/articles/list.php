<section class="section-space">
    <div class="container">
        <h1>Articles sur la guerre en Iran</h1>
        <p class="page-intro">Suivez les operations militaires, les impacts economiques et les initiatives diplomatiques entre 2024 et 2026.</p>

        <form method="GET" action="<?= BASE_URL ?>/articles" class="search-form" role="search" aria-label="Recherche d articles">
            <label for="q" class="visually-hidden">Rechercher</label>
            <input type="search" id="q" name="q" placeholder="Rechercher un article..." value="<?= htmlspecialchars((string) ($_GET['q'] ?? '')) ?>">
            <button type="submit" class="btn-primary">Rechercher</button>
        </form>

        <?php if (empty($articles)): ?>
            <p>Aucun article trouve pour votre recherche.</p>
        <?php else: ?>
            <div class="grid grid-3" role="list">
                <?php foreach ($articles as $article): ?>
                    <?php
                    $articleCard = $article;
                    $showMeta = true;
                    $titleTag = 'h2';
                    include ROOT . '/views/front/partials/article-card.php';
                    ?>
                <?php endforeach; ?>
            </div>

            <?php
            $paginationTotalPages = $totalPages;
            $paginationPage = $page;
            $paginationBaseUrl = BASE_URL . '/articles';
            $paginationQuery = [];
            if (!empty($_GET['q'])) {
                $paginationQuery['q'] = (string) $_GET['q'];
            }
            include ROOT . '/views/front/partials/pagination.php';
            ?>
        <?php endif; ?>
    </div>
</section>
