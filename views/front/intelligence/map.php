<section class="section-space intelligence-hero">
    <?php $mapVisual = \App\Core\Helpers::getIntelligenceVisual('map'); ?>
    <div class="container">
        <h1>Carte interactive des evenements</h1>
        <p class="page-intro">
            Suivez les points chauds du conflit en Iran via une carte dynamique (filtres type/date, clustering, zones et heatmap).
        </p>
        <?php if ($mapVisual !== null): ?>
            <figure class="intel-hero-visual">
                <img src="<?= htmlspecialchars((string) $mapVisual['image']) ?>" alt="Illustration de carte geopolitique de l Iran" loading="lazy" width="1400" height="700">
                <figcaption>
                    <?= htmlspecialchars((string) $mapVisual['label']) ?>:
                    <a href="<?= htmlspecialchars((string) $mapVisual['source']) ?>" target="_blank" rel="noopener nofollow">ouvrir la reference</a>
                </figcaption>
            </figure>
        <?php endif; ?>

        <form method="GET" action="<?= BASE_URL ?>/carte" class="intel-filter-form">
            <label for="map_type_filter">Type</label>
            <select id="map_type_filter" name="type">
                <option value="">Tous</option>
                <?php foreach ($allowedEventTypes as $type): ?>
                    <option value="<?= htmlspecialchars($type) ?>" <?= (($mapFilters['type'] ?? '') === $type) ? 'selected' : '' ?>>
                        <?= htmlspecialchars(ucfirst($type)) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="map_date_from">Du</label>
            <input id="map_date_from" type="date" name="date_from" value="<?= htmlspecialchars((string) ($mapFilters['date_from'] ?? '')) ?>">

            <label for="map_date_to">Au</label>
            <input id="map_date_to" type="date" name="date_to" value="<?= htmlspecialchars((string) ($mapFilters['date_to'] ?? '')) ?>">
            <button class="btn-primary" type="submit">Filtrer la carte</button>
        </form>

        <div
            id="fo-events-map"
            class="front-map"
            data-api-url="<?= BASE_URL ?>/api/events?type=<?= rawurlencode((string) ($mapFilters['type'] ?? '')) ?>&date_from=<?= rawurlencode((string) ($mapFilters['date_from'] ?? '')) ?>&date_to=<?= rawurlencode((string) ($mapFilters['date_to'] ?? '')) ?>"
        ></div>
        <p class="map-legend">
            <span class="legend-dot militaire"></span> Militaire
            <span class="legend-dot politique"></span> Politique
            <span class="legend-dot diplomatique"></span> Diplomatique
            <span class="legend-dot bombardement"></span> Bombardement
            <span class="legend-dot manifestation"></span> Manifestation
        </p>
    </div>
</section>

<section class="section-space">
    <div class="container">
        <h2>Derniers evenements geolocalises</h2>
        <div class="portal-grid">
            <?php foreach ($latestEvents as $event): ?>
                <article class="portal-card">
                    <h3><?= htmlspecialchars((string) $event['title']) ?></h3>
                    <p><strong>Type:</strong> <?= htmlspecialchars((string) $event['type']) ?></p>
                    <p><strong>Ville:</strong> <?= htmlspecialchars((string) $event['city']) ?></p>
                    <p><strong>Date:</strong> <?= htmlspecialchars((string) $event['event_date']) ?></p>
                    <p><?= htmlspecialchars((string) $event['description']) ?></p>
                </article>
            <?php endforeach; ?>
            <?php if (empty($latestEvents)): ?>
                <p>Aucun evenement pour ces filtres.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="anonymous">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer crossorigin="anonymous"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js" defer></script>
<script src="https://unpkg.com/leaflet.heat/dist/leaflet-heat.js" defer></script>
