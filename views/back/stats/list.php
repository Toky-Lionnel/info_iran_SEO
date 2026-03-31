<section class="dashboard">
    <h1>Statistiques dynamiques - BO</h1>

    <div class="stack-grid">
        <article class="admin-card">
            <h3>Filtres statistiques</h3>
            <form method="GET" action="<?= ADMIN_PATH ?>/stats" class="filter-form filter-form-wide">
                <label for="stats_type_filter">Type</label>
                <select id="stats_type_filter" name="type">
                    <option value="">Tous</option>
                    <?php foreach ($allowedTypes as $type): ?>
                        <option value="<?= htmlspecialchars($type) ?>" <?= (($filters['type'] ?? '') === $type) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucfirst($type)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="stats_from_filter">Du</label>
                <input id="stats_from_filter" type="date" name="date_from" value="<?= htmlspecialchars((string) ($filters['date_from'] ?? '')) ?>">
                <label for="stats_to_filter">Au</label>
                <input id="stats_to_filter" type="date" name="date_to" value="<?= htmlspecialchars((string) ($filters['date_to'] ?? '')) ?>">
                <button class="btn-sm" type="submit">Filtrer</button>
            </form>
            <div class="admin-chart-wrap">
                <canvas id="bo-stats-chart" height="120"></canvas>
            </div>
        </article>

        <article class="admin-card">
            <h3>Ajouter / Mettre a jour une valeur</h3>
            <form method="POST" action="<?= ADMIN_PATH ?>/stats/create" class="card-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                <div class="form-group">
                    <label for="stats_type">Type</label>
                    <select id="stats_type" name="type" required>
                        <?php foreach ($allowedTypes as $type): ?>
                            <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars(ucfirst($type)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="stats_value">Valeur</label>
                    <input id="stats_value" type="number" min="0" step="1" name="value" required>
                </div>
                <div class="form-group">
                    <label for="stats_date">Date</label>
                    <input id="stats_date" type="date" name="stat_date" required>
                </div>
                <button class="btn-save" type="submit">Enregistrer</button>
            </form>
        </article>
    </div>

    <section class="table-wrap">
        <h2>Historique des stats</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Valeur</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($stats)): ?>
                    <tr><td colspan="5">Aucune statistique.</td></tr>
                <?php else: ?>
                    <?php foreach ($stats as $row): ?>
                        <tr>
                            <td><?= (int) $row['id'] ?></td>
                            <td><?= htmlspecialchars((string) $row['stat_date']) ?></td>
                            <td><?= htmlspecialchars((string) $row['type']) ?></td>
                            <td><?= number_format((int) $row['value'], 0, ',', ' ') ?></td>
                            <td>
                                <a class="btn-sm" href="<?= ADMIN_PATH ?>/stats/edit/<?= (int) $row['id'] ?>">Editer</a>
                                <form method="POST" action="<?= ADMIN_PATH ?>/stats/delete/<?= (int) $row['id'] ?>" class="inline-form">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                                    <button class="btn-sm" type="submit" data-confirm="Supprimer cette statistique ?">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</section>

<script>
window.__BO_STATS_SERIES__ = <?= json_encode($statSeries, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js" defer></script>
<script src="<?= BASE_URL ?>/public/js/back/stats-chart.js" defer></script>
