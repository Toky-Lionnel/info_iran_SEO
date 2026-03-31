<section class="section-space">
    <div class="container">
        <h1>Nouveautes editoriales</h1>
        <p class="page-intro">
            Cette page centralise les dernieres publications du site: articles, debats et journaux epingles.
        </p>
    </div>
</section>

<section class="section-space">
    <div class="container">
        <h2>Derniers articles verifies</h2>
        <div class="grid grid-3">
            <?php foreach ($latestArticles as $article): ?>
                <?php
                $articleCard = $article;
                $showMeta = true;
                $titleTag = 'h3';
                include ROOT . '/views/front/partials/article-card.php';
                ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-space">
    <div class="container portal-grid">
        <article class="portal-card">
            <h2>Journaux epingles</h2>
            <?php if (empty($latestJournals)): ?>
                <p>Aucun journal disponible.</p>
            <?php else: ?>
                <ul class="portal-list">
                    <?php foreach ($latestJournals as $journal): ?>
                        <li>
                            <h3><?= htmlspecialchars((string) $journal['title']) ?></h3>
                            <p><?= htmlspecialchars((string) $journal['summary']) ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p><a class="btn-outline" href="<?= BASE_URL ?>/journaux">Voir tous les journaux</a></p>
            <?php endif; ?>
        </article>

        <article class="portal-card">
            <h2>Debats ouverts</h2>
            <?php if (empty($latestDebates)): ?>
                <p>Aucun debat disponible.</p>
            <?php else: ?>
                <ul class="portal-list">
                    <?php foreach ($latestDebates as $debate): ?>
                        <li>
                            <h3>
                                <a href="<?= BASE_URL ?>/debat/<?= htmlspecialchars((string) $debate['slug']) ?>">
                                    <?= htmlspecialchars((string) $debate['title']) ?>
                                </a>
                            </h3>
                            <p><?= htmlspecialchars((string) $debate['summary']) ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p><a class="btn-outline" href="<?= BASE_URL ?>/debats">Voir tous les debats</a></p>
            <?php endif; ?>
        </article>
    </div>
</section>
