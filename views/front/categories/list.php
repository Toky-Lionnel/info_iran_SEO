<section class="section-space">
    <div class="container">
        <h1>Categorie : <?= htmlspecialchars((string) $category['name']) ?></h1>
        <p class="page-intro">Articles classes dans le theme <?= htmlspecialchars((string) $category['name']) ?>.</p>

        <?php if (empty($articles)): ?>
            <p>Aucun article publie pour cette categorie.</p>
        <?php else: ?>
            <div class="grid grid-3" role="list">
                <?php foreach ($articles as $article): ?>
                    <?php
                    $articleCard = $article;
                    $showMeta = false;
                    $titleTag = 'h2';
                    include ROOT . '/views/front/partials/article-card.php';
                    ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php
        $paginationTotalPages = $totalPages;
        $paginationPage = $page;
        $paginationBaseUrl = BASE_URL . '/categorie-' . (int) $category['id'] . '-';
        $paginationQuery = [];
        if ($paginationTotalPages > 1):
            ?>
            <nav class="pagination" aria-label="Pagination categorie">
                <?php for ($p = 1; $p <= $paginationTotalPages; $p++): ?>
                    <?php $url = BASE_URL . '/categorie-' . (int) $category['id'] . '-' . $p . '.html'; ?>
                    <?php if ($p === $paginationPage): ?>
                        <span class="active" aria-current="page"><?= $p ?></span>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars($url) ?>"><?= $p ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </div>
</section>
