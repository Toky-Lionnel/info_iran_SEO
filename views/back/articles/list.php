<section class="dashboard">
    <div class="toolbar">
        <h1>Articles</h1>
        <a href="<?= ADMIN_PATH ?>/articles/create" class="btn-save">Nouvel article</a>
    </div>

    <form method="GET" action="<?= ADMIN_PATH ?>/articles" class="filter-form">
        <label for="status">Filtrer par statut</label>
        <select id="status" name="status">
            <option value="">Tous</option>
            <option value="draft" <?= ($statusFilter ?? '') === 'draft' ? 'selected' : '' ?>>draft</option>
            <option value="published" <?= ($statusFilter ?? '') === 'published' ? 'selected' : '' ?>>published</option>
            <option value="archived" <?= ($statusFilter ?? '') === 'archived' ? 'selected' : '' ?>>archived</option>
        </select>
        <button class="btn-sm" type="submit">Appliquer</button>
    </form>

    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Categorie</th>
                <th>Auteur</th>
                <th>Statut</th>
                <th>Vues</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($articles as $article): ?>
            <tr>
                <td><?= (int) $article['id'] ?></td>
                <td><?= htmlspecialchars((string) $article['title']) ?></td>
                <td><?= htmlspecialchars((string) $article['cat_name']) ?></td>
                <td><?= htmlspecialchars((string) $article['author_name']) ?></td>
                <td><span class="status status-<?= htmlspecialchars((string) $article['status']) ?>"><?= htmlspecialchars((string) $article['status']) ?></span></td>
                <td><?= (int) $article['views'] ?></td>
                <td>
                    <a class="btn-sm" href="<?= ADMIN_PATH ?>/articles/edit/<?= (int) $article['id'] ?>">Editer</a>
                    <a class="btn-sm" href="<?= BASE_URL ?>/article-<?= (int) $article['id'] ?>-<?= (int) $article['category_id'] ?>.html" target="_blank" rel="noopener">Voir</a>

                    <form method="POST" action="<?= ADMIN_PATH ?>/articles/toggle/<?= (int) $article['id'] ?>" style="display:inline-block;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                        <button class="btn-sm" type="submit"><?= $article['status'] === 'published' ? 'Depublier' : 'Publier' ?></button>
                    </form>

                    <form method="POST" action="<?= ADMIN_PATH ?>/articles/delete/<?= (int) $article['id'] ?>" style="display:inline-block;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                        <button class="btn-sm" type="submit" data-confirm="Supprimer cet article ?">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($totalPages > 1): ?>
        <nav class="pagination" aria-label="Pagination admin articles">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <?php
                $params = ['page' => $p];
                if (!empty($statusFilter)) {
                    $params['status'] = $statusFilter;
                }
                $url = ADMIN_PATH . '/articles?' . http_build_query($params);
                ?>
                <?php if ($p === $page): ?>
                    <span class="active"><?= $p ?></span>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($url) ?>"><?= $p ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
</section>
