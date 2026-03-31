<section class="dashboard">
    <h1>Tableau de bord</h1>

    <div class="stats-grid">
        <?php
        $statIcon = 'ART';
        $statValue = (string) ((int) $stats['total_articles']);
        $statLabel = 'Articles publies';
        include ROOT . '/views/back/partials/stat-card.php';

        $statIcon = 'VUE';
        $statValue = number_format((int) $stats['total_views'], 0, ',', ' ');
        $statLabel = 'Lectures totales';
        include ROOT . '/views/back/partials/stat-card.php';

        $statIcon = 'CAT';
        $statValue = (string) ((int) $stats['total_categories']);
        $statLabel = 'Categories';
        include ROOT . '/views/back/partials/stat-card.php';

        $statIcon = 'DRF';
        $statValue = (string) ((int) $stats['drafts']);
        $statLabel = 'Brouillons';
        include ROOT . '/views/back/partials/stat-card.php';

        $statIcon = 'COM';
        $statValue = (string) ((int) ($stats['pending_comments'] ?? 0));
        $statLabel = 'Moderations en attente';
        include ROOT . '/views/back/partials/stat-card.php';

        $statIcon = 'MSG';
        $statValue = (string) ((int) ($stats['new_contacts'] ?? 0));
        $statLabel = 'Messages contact';
        include ROOT . '/views/back/partials/stat-card.php';

        $statIcon = 'PREM';
        $statValue = (string) ((int) ($stats['premium_subscribers'] ?? 0));
        $statLabel = 'Abonnes premium';
        include ROOT . '/views/back/partials/stat-card.php';

        $statIcon = 'ACT';
        $statValue = (string) ((int) ($stats['active_subscribers'] ?? 0));
        $statLabel = 'Abonnes actifs';
        include ROOT . '/views/back/partials/stat-card.php';

        $statIcon = 'AVG';
        $statValue = number_format((float) ($stats['avg_reading_duration'] ?? 0), 1, ',', ' ') . ' s';
        $statLabel = 'Temps lecture moyen';
        include ROOT . '/views/back/partials/stat-card.php';

        $statIcon = 'CVR';
        $statValue = number_format((float) ($stats['conversion_rate'] ?? 0), 2, ',', ' ') . ' %';
        $statLabel = 'Conversion abonnes';
        include ROOT . '/views/back/partials/stat-card.php';
        ?>
    </div>

    <section class="dashboard-chart-grid">
        <article class="admin-card">
            <h2>Trafic 30 derniers jours</h2>
            <div class="admin-chart-wrap">
                <canvas id="dashboard-traffic-chart" height="120"></canvas>
            </div>
        </article>

        <article class="admin-card">
            <h2>Articles les plus lus</h2>
            <?php if (empty($mostReadArticles)): ?>
                <p>Aucune donnee de lecture disponible.</p>
            <?php else: ?>
                <ul class="admin-kpi-list">
                    <?php foreach ($mostReadArticles as $topArticle): ?>
                        <li>
                            <strong><?= htmlspecialchars((string) $topArticle['title']) ?></strong>
                            <p>
                                <?= number_format((int) $topArticle['views'], 0, ',', ' ') ?> vues
                                | <?= (int) $topArticle['reading_time'] ?> min
                            </p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </article>
    </section>

    <section class="dashboard-chart-grid">
        <article class="admin-card">
            <h2>Top pages visitees</h2>
            <table class="admin-table">
                <thead>
                <tr>
                    <th>Page</th>
                    <th>Visites</th>
                    <th>Duree moyenne</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($topPages)): ?>
                    <tr><td colspan="3">Aucune donnee analytics.</td></tr>
                <?php else: ?>
                    <?php foreach ($topPages as $page): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $page['page']) ?></td>
                            <td><?= (int) ($page['visits'] ?? 0) ?></td>
                            <td><?= number_format((float) ($page['avg_duration'] ?? 0), 1, ',', ' ') ?> s</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </article>

        <article class="admin-card">
            <h2>SEO a optimiser</h2>
            <?php if (empty($seoWeakArticles)): ?>
                <p>Aucune analyse SEO disponible.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                    <tr>
                        <th>Article</th>
                        <th>Title</th>
                        <th>Keywords</th>
                        <th>Lisibilite</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($seoWeakArticles as $seoRow): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $seoRow['title']) ?></td>
                            <td><?= (int) ($seoRow['title_score'] ?? 0) ?></td>
                            <td><?= (int) ($seoRow['keyword_score'] ?? 0) ?></td>
                            <td><?= (int) ($seoRow['readability_score'] ?? 0) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </article>
    </section>

    <section>
        <div class="toolbar">
            <h2>Table base dashboard (filtres + recherche)</h2>
            <a class="btn-sm" href="<?= ADMIN_PATH ?>/reports">Exports / Imports Excel</a>
        </div>

        <form method="GET" action="<?= ADMIN_PATH ?>" class="filter-form filter-form-wide">
            <label for="dashboard_q">Recherche</label>
            <input id="dashboard_q" name="q" value="<?= htmlspecialchars((string) ($dashboardFilters['q'] ?? '')) ?>" placeholder="Titre / extrait">

            <label for="dashboard_status">Statut</label>
            <select id="dashboard_status" name="status">
                <option value="">Tous</option>
                <option value="draft" <?= (($dashboardFilters['status'] ?? '') === 'draft') ? 'selected' : '' ?>>draft</option>
                <option value="published" <?= (($dashboardFilters['status'] ?? '') === 'published') ? 'selected' : '' ?>>published</option>
                <option value="archived" <?= (($dashboardFilters['status'] ?? '') === 'archived') ? 'selected' : '' ?>>archived</option>
            </select>

            <label for="dashboard_category">Categorie</label>
            <select id="dashboard_category" name="category_id">
                <option value="0">Toutes</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int) $cat['id'] ?>" <?= ((int) ($dashboardFilters['category_id'] ?? 0) === (int) $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) $cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="dashboard_from">Du</label>
            <input id="dashboard_from" name="date_from" type="date" value="<?= htmlspecialchars((string) ($dashboardFilters['date_from'] ?? '')) ?>">

            <label for="dashboard_to">Au</label>
            <input id="dashboard_to" name="date_to" type="date" value="<?= htmlspecialchars((string) ($dashboardFilters['date_to'] ?? '')) ?>">

            <label for="dashboard_sort">Tri</label>
            <select id="dashboard_sort" name="sort">
                <option value="latest" <?= (($dashboardFilters['sort'] ?? 'latest') === 'latest') ? 'selected' : '' ?>>Plus recents</option>
                <option value="views" <?= (($dashboardFilters['sort'] ?? '') === 'views') ? 'selected' : '' ?>>Vues</option>
                <option value="comments" <?= (($dashboardFilters['sort'] ?? '') === 'comments') ? 'selected' : '' ?>>Commentaires</option>
                <option value="shares" <?= (($dashboardFilters['sort'] ?? '') === 'shares') ? 'selected' : '' ?>>Partages</option>
            </select>

            <button class="btn-sm" type="submit">Appliquer</button>
        </form>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Categorie</th>
                    <th>Auteur</th>
                    <th>Statut</th>
                    <th>Vues</th>
                    <th>Commentaires</th>
                    <th>Partages</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentArticles)): ?>
                    <tr>
                        <td colspan="9">Aucun article ne correspond aux filtres.</td>
                    </tr>
                <?php else: ?>
                <?php foreach ($recentArticles as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $a['title']) ?></td>
                        <td>
                            <span class="badge" style="background:<?= htmlspecialchars((string) $a['cat_color']) ?>">
                                <?= htmlspecialchars((string) $a['cat_name']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars((string) $a['author_name']) ?></td>
                        <td><span class="status status-<?= htmlspecialchars((string) $a['status']) ?>"><?= htmlspecialchars((string) $a['status']) ?></span></td>
                        <td><?= (int) $a['views'] ?></td>
                        <td><?= (int) ($a['comments_count'] ?? 0) ?></td>
                        <td><?= (int) ($a['shares_count'] ?? 0) ?></td>
                        <td><?= htmlspecialchars((string) ($a['published_at'] ?? $a['created_at'] ?? '-')) ?></td>
                        <td>
                            <a class="btn-sm" href="<?= ADMIN_PATH ?>/articles/edit/<?= (int) $a['id'] ?>">Editer</a>
                            <a class="btn-sm" href="<?= BASE_URL ?>/article-<?= (int) $a['id'] ?>-<?= (int) $a['category_id'] ?>.html" target="_blank" rel="noopener">Voir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

    <section class="table-wrap">
        <h2>Historique des exports / imports</h2>
        <table class="admin-table">
            <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Dataset</th>
                <th>Fichier</th>
                <th>Lignes</th>
                <th>Statut</th>
                <th>Admin</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($recentExchangeLogs)): ?>
                <tr><td colspan="7">Aucune operation enregistree.</td></tr>
            <?php else: ?>
                <?php foreach ($recentExchangeLogs as $log): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $log['created_at']) ?></td>
                        <td><?= htmlspecialchars((string) $log['exchange_type']) ?></td>
                        <td><?= htmlspecialchars((string) $log['dataset']) ?></td>
                        <td><?= htmlspecialchars((string) ($log['file_name'] ?? '-')) ?></td>
                        <td><?= (int) ($log['rows_count'] ?? 0) ?></td>
                        <td><?= htmlspecialchars((string) ($log['status'] ?? '-')) ?></td>
                        <td><?= htmlspecialchars((string) ($log['admin_username'] ?? '-')) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </section>
</section>

<script>
window.__DASHBOARD_TRAFFIC__ = <?= json_encode($dailyTraffic, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js" defer></script>
<script src="<?= BASE_URL ?>/public/js/back/dashboard-charts.js" defer></script>
