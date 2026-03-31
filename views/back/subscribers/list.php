<section class="dashboard">
    <div class="toolbar">
        <h1>Utilisateurs abonnes</h1>
        <div class="toolbar-meta">
            <span class="status status-published">Actifs: <?= (int) ($stats['active'] ?? 0) ?></span>
            <span class="status status-draft">Premium: <?= (int) ($stats['premium'] ?? 0) ?></span>
            <span class="status status-archived">Free: <?= (int) ($stats['free'] ?? 0) ?></span>
        </div>
    </div>

    <form method="GET" action="<?= ADMIN_PATH ?>/subscribers" class="filter-form">
        <label for="q">Recherche</label>
        <input id="q" name="q" value="<?= htmlspecialchars((string) ($filters['q'] ?? '')) ?>" placeholder="Nom, email, pays, ville">

        <label for="plan">Plan</label>
        <select id="plan" name="plan">
            <option value="">Tous</option>
            <option value="free" <?= (($filters['plan'] ?? '') === 'free') ? 'selected' : '' ?>>free</option>
            <option value="premium" <?= (($filters['plan'] ?? '') === 'premium') ? 'selected' : '' ?>>premium</option>
        </select>

        <label for="active">Statut</label>
        <select id="active" name="active">
            <option value="">Tous</option>
            <option value="1" <?= (($filters['active'] ?? '') === '1') ? 'selected' : '' ?>>Actif</option>
            <option value="0" <?= (($filters['active'] ?? '') === '0') ? 'selected' : '' ?>>Suspendu</option>
        </select>

        <button class="btn-sm" type="submit">Filtrer</button>
    </form>

    <div class="table-wrap">
        <table class="admin-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Pays / Ville</th>
                <th>Plan</th>
                <th>Points</th>
                <th>Actif</th>
                <th>Derniere connexion</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($subscribers)): ?>
                <tr><td colspan="9">Aucun abonne trouve.</td></tr>
            <?php else: ?>
                <?php foreach ($subscribers as $row): ?>
                    <tr>
                        <td><?= (int) $row['id'] ?></td>
                        <td><?= htmlspecialchars((string) $row['full_name']) ?></td>
                        <td><?= htmlspecialchars((string) $row['email']) ?></td>
                        <td>
                            <?= htmlspecialchars((string) ($row['country'] ?? '-')) ?>
                            /
                            <?= htmlspecialchars((string) ($row['city'] ?? '-')) ?>
                        </td>
                        <td><?= htmlspecialchars((string) $row['plan']) ?></td>
                        <td><?= (int) ($row['points'] ?? 0) ?></td>
                        <td><?= ((int) ($row['is_active'] ?? 0) === 1) ? 'Oui' : 'Non' ?></td>
                        <td><?= htmlspecialchars((string) ($row['last_login'] ?? '-')) ?></td>
                        <td>
                            <a class="btn-sm" href="<?= ADMIN_PATH ?>/subscribers/edit/<?= (int) $row['id'] ?>">Editer</a>
                            <form method="POST" action="<?= ADMIN_PATH ?>/subscribers/delete/<?= (int) $row['id'] ?>" style="display:inline-block;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                                <button class="btn-sm" type="submit" data-confirm="Supprimer cet abonne ?">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (($pager['total_pages'] ?? 1) > 1): ?>
        <nav class="pagination" aria-label="Pagination abonnes">
            <?php for ($p = 1; $p <= (int) $pager['total_pages']; $p++): ?>
                <?php
                $params = [
                    'page' => $p,
                    'q' => (string) ($filters['q'] ?? ''),
                    'plan' => (string) ($filters['plan'] ?? ''),
                    'active' => (string) ($filters['active'] ?? ''),
                ];
                $url = ADMIN_PATH . '/subscribers?' . http_build_query($params);
                ?>
                <?php if ($p === (int) ($pager['current_page'] ?? 1)): ?>
                    <span class="active"><?= $p ?></span>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($url) ?>"><?= $p ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
</section>
