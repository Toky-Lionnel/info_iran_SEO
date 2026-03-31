<section class="section-space intelligence-hero">
    <?php $timelineVisual = \App\Core\Helpers::getIntelligenceVisual('timeline'); ?>
    <div class="container">
        <h1>Timeline interactive 2024-2026</h1>
        <p class="page-intro">
            Chronologie horizontale storytelling des evenements militaires, politiques et diplomatiques.
        </p>
        <?php if ($timelineVisual !== null): ?>
            <figure class="intel-hero-visual">
                <img src="<?= htmlspecialchars((string) $timelineVisual['image']) ?>" alt="Illustration chronologique et archives du conflit" loading="lazy" width="1400" height="700">
                <figcaption>
                    <?= htmlspecialchars((string) $timelineVisual['label']) ?>:
                    <a href="<?= htmlspecialchars((string) $timelineVisual['source']) ?>" target="_blank" rel="noopener nofollow">ouvrir la reference</a>
                </figcaption>
            </figure>
        <?php endif; ?>

        <form method="GET" action="<?= BASE_URL ?>/timeline" class="intel-filter-form">
            <label for="timeline_category_filter">Categorie</label>
            <select id="timeline_category_filter" name="category">
                <option value="">Toutes</option>
                <?php foreach ($allowedTimelineCategories as $category): ?>
                    <option value="<?= htmlspecialchars($category) ?>" <?= (($timelineFilters['category'] ?? '') === $category) ? 'selected' : '' ?>>
                        <?= htmlspecialchars(ucfirst($category)) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="timeline_date_from">Du</label>
            <input id="timeline_date_from" type="date" name="date_from" value="<?= htmlspecialchars((string) ($timelineFilters['date_from'] ?? '')) ?>">

            <label for="timeline_date_to">Au</label>
            <input id="timeline_date_to" type="date" name="date_to" value="<?= htmlspecialchars((string) ($timelineFilters['date_to'] ?? '')) ?>">
            <button class="btn-primary" type="submit">Filtrer</button>
        </form>

        <div
            class="timeline-track-wrapper"
            id="timeline-track-wrapper"
            data-api-url="<?= BASE_URL ?>/api/timeline?category=<?= rawurlencode((string) ($timelineFilters['category'] ?? '')) ?>&date_from=<?= rawurlencode((string) ($timelineFilters['date_from'] ?? '')) ?>&date_to=<?= rawurlencode((string) ($timelineFilters['date_to'] ?? '')) ?>"
        >
            <div class="timeline-track" id="timeline-track">
                <?php foreach ($timelineEvents as $event): ?>
                    <article class="timeline-event-card" tabindex="0">
                        <span class="timeline-date"><?= htmlspecialchars((string) $event['event_date']) ?></span>
                        <span class="timeline-category"><?= htmlspecialchars((string) $event['category']) ?></span>
                        <h3><?= htmlspecialchars((string) $event['title']) ?></h3>
                        <p><?= htmlspecialchars((string) $event['description']) ?></p>
                    </article>
                <?php endforeach; ?>
                <?php if (empty($timelineEvents)): ?>
                    <p>Aucun evenement timeline disponible.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
