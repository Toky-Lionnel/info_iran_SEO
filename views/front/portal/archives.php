<section class="section-space">
    <div class="container">
        <h1>Archives editoriales</h1>
        <p class="page-intro">
            Consultez les publications par mois pour suivre la progression du conflit et ses impacts.
        </p>

        <form method="GET" action="<?= BASE_URL ?>/archives" class="review-form">
            <div class="form-row">
                <label for="month">Mois (YYYY-MM)</label>
                <input id="month" name="month" type="month" value="<?= htmlspecialchars((string) ($_GET['month'] ?? '')) ?>">
            </div>
            <button class="btn-primary" type="submit">Filtrer</button>
        </form>

        <div class="portal-grid">
            <article class="portal-card">
                <h2>Mois disponibles</h2>
                <?php if (empty($archiveBuckets)): ?>
                    <p>Aucune archive.</p>
                <?php else: ?>
                    <ul class="portal-list">
                        <?php foreach ($archiveBuckets as $bucket): ?>
                            <?php
                            $value = sprintf('%04d-%02d', (int) $bucket['year_num'], (int) $bucket['month_num']);
                            ?>
                            <li>
                                <a href="<?= BASE_URL ?>/archives?month=<?= htmlspecialchars($value) ?>">
                                    <?= htmlspecialchars($value) ?>
                                </a>
                                <span>(<?= (int) $bucket['article_count'] ?> articles)</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </article>

            <article class="portal-card">
                <h2>Articles archives</h2>
                <?php if (empty($archiveArticles)): ?>
                    <p>Aucun article pour cette periode.</p>
                <?php else: ?>
                    <ul class="portal-list">
                        <?php foreach ($archiveArticles as $article): ?>
                            <li>
                                <a href="<?= BASE_URL ?>/article-<?= (int) $article['id'] ?>-<?= (int) $article['category_id'] ?>.html">
                                    <?= htmlspecialchars((string) $article['title']) ?>
                                </a>
                                <p><?= htmlspecialchars((string) $article['excerpt']) ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </article>
        </div>
    </div>
</section>
