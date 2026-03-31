<section class="section-space journals-page">
    <div class="container">
        <header class="journals-hero">
            <h1>Journaux epingles</h1>
            <p class="page-intro">
                Dossiers approfondis publies par la redaction: syntheses longues, notes strategiques et suivis chronologiques.
            </p>
        </header>

        <?php if (empty($journals)): ?>
            <article class="portal-card journal-card">
                <h2>Aucun journal disponible</h2>
                <p>La redaction n a publie aucun journal epingle pour le moment.</p>
            </article>
        <?php else: ?>
            <div class="journals-grid">
                <?php foreach ($journals as $journal): ?>
                    <?php
                    $publishedAt = (string) ($journal['published_at'] ?? '');
                    $formattedDate = $publishedAt !== '' ? date('d/m/Y H:i', strtotime($publishedAt)) : null;
                    ?>
                    <article class="portal-card journal-card">
                        <header class="journal-head">
                            <span class="journal-badge">Journal epingle</span>
                            <?php if ($formattedDate !== null): ?>
                                <time datetime="<?= htmlspecialchars($publishedAt) ?>"><?= htmlspecialchars($formattedDate) ?></time>
                            <?php endif; ?>
                        </header>
                        <h2><?= htmlspecialchars((string) $journal['title']) ?></h2>
                        <p class="journal-summary"><?= htmlspecialchars((string) $journal['summary']) ?></p>
                        <div class="portal-content"><?= $journal['content'] ?></div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
