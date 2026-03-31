<section class="section-space intelligence-hero">
    <?php $statsVisual = \App\Core\Helpers::getIntelligenceVisual('stats'); ?>
    <div class="container">
        <h1>Statistiques dynamiques du conflit</h1>
        <p class="page-intro">
            Analyse de l evolution des pertes humaines, deplacements de population et sanctions economiques.
        </p>
        <?php if ($statsVisual !== null): ?>
            <figure class="intel-hero-visual">
                <img src="<?= htmlspecialchars((string) $statsVisual['image']) ?>" alt="Illustration de donnees et graphiques statistiques" loading="lazy" width="1400" height="700">
                <figcaption>
                    <?= htmlspecialchars((string) $statsVisual['label']) ?>:
                    <a href="<?= htmlspecialchars((string) $statsVisual['source']) ?>" target="_blank" rel="noopener nofollow">ouvrir la reference</a>
                </figcaption>
            </figure>
        <?php endif; ?>

        <form method="GET" action="<?= BASE_URL ?>/statistiques" class="intel-filter-form">
            <label for="stats_type_filter">Type</label>
            <select id="stats_type_filter" name="type">
                <option value="">Tous</option>
                <?php foreach ($allowedStatTypes as $type): ?>
                    <option value="<?= htmlspecialchars($type) ?>" <?= (($statsFilters['type'] ?? '') === $type) ? 'selected' : '' ?>>
                        <?= htmlspecialchars(ucfirst($type)) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="stats_date_from">Du</label>
            <input id="stats_date_from" type="date" name="date_from" value="<?= htmlspecialchars((string) ($statsFilters['date_from'] ?? '')) ?>">

            <label for="stats_date_to">Au</label>
            <input id="stats_date_to" type="date" name="date_to" value="<?= htmlspecialchars((string) ($statsFilters['date_to'] ?? '')) ?>">
            <button class="btn-primary" type="submit">Filtrer</button>
        </form>

        <div class="front-chart-wrap">
            <canvas id="fo-stats-chart" height="120"></canvas>
        </div>
    </div>
</section>

<section class="section-space">
    <div class="container">
        <h2>Table des statistiques</h2>
        <div class="table-wrap">
            <table class="intel-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Valeur</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($statRows)): ?>
                        <tr><td colspan="3">Aucune statistique disponible.</td></tr>
                    <?php else: ?>
                        <?php foreach ($statRows as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars((string) $row['stat_date']) ?></td>
                                <td><?= htmlspecialchars((string) $row['type']) ?></td>
                                <td><?= number_format((int) $row['value'], 0, ',', ' ') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<script>
window.__FO_STATS_SERIES__ = <?= json_encode($statsSeries, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js" defer></script>
