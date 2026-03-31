<section class="hero" aria-labelledby="hero-title">
    <div class="hero-content">
        <h1 id="hero-title">Guerre en Iran : suivi du conflit 2024-2026</h1>
        <p class="hero-subtitle">Chronologie, analyses geopolitique, consequences energetiques et bilan humanitaire.</p>
        <a href="<?= BASE_URL ?>/articles" class="btn-primary">Voir tous les articles</a>
    </div>
    <div class="hero-stats" role="complementary" aria-label="Chiffres cles">
        <div class="stat-card"><span class="stat-num">13-24 juin 2025</span><span class="stat-label">Guerre des Douze Jours</span></div>
        <div class="stat-card"><span class="stat-num">28 fev. 2026</span><span class="stat-label">Debut de la guerre ouverte</span></div>
        <div class="stat-card"><span class="stat-num">Ormuz</span><span class="stat-label">Tension sur l energie mondiale</span></div>
    </div>
</section>

<section class="articles-grid section-space" aria-labelledby="section-featured">
    <div class="container">
        <h2 id="section-featured">Dernieres actualites</h2>
        <div class="grid grid-3" role="list">
            <?php foreach ($featuredArticles as $article): ?>
                <?php
                $articleCard = $article;
                $showMeta = true;
                $titleTag = 'h3';
                include ROOT . '/views/front/partials/article-card.php';
                ?>
            <?php endforeach; ?>
        </div>
        <div class="text-center">
            <a href="<?= BASE_URL ?>/articles" class="btn-outline">Tous les articles</a>
        </div>
    </div>
</section>

<section class="section-space categories-section" aria-labelledby="section-categories">
    <div class="container">
        <h2 id="section-categories">Parcourir par theme</h2>
        <nav class="categories-nav" aria-label="Categories d articles">
            <?php foreach ($categories as $cat): ?>
                <a href="<?= BASE_URL ?>/categorie-<?= (int) $cat['id'] ?>-1.html"
                   class="category-pill"
                   style="border-color:<?= htmlspecialchars((string) $cat['color']) ?>;color:<?= htmlspecialchars((string) $cat['color']) ?>">
                    <?= htmlspecialchars((string) $cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
</section>

<section class="section-space context-section" aria-labelledby="section-context">
    <div class="container">
        <h2 id="section-context">Contexte elargi du conflit</h2>
        <p class="context-intro">
            Cette rubrique donne une vision plus large du conflit, au-dela de la seule chronologie militaire:
            rapports de force, effets economiques, risques humanitaires et dynamique diplomatique.
        </p>

        <div class="context-grid">
            <article class="context-card">
                <h3>Acteurs et objectifs</h3>
                <p>
                    Les logiques strategiques des differents acteurs (Iran, Israel, Etats-Unis, puissances regionales)
                    combinent deterrence, influence regionale, securite interieure et controle de l escalade.
                </p>
            </article>
            <article class="context-card">
                <h3>Energie et commerce mondial</h3>
                <p>
                    Le detroit d Ormuz reste un point critique: transport petrogazier, assurance maritime, prix de l energie
                    et cout des importations sont directement sensibles a chaque phase de tension.
                </p>
            </article>
            <article class="context-card">
                <h3>Dimension humanitaire</h3>
                <p>
                    Les populations civiles subissent les effets cumules: deplacements forces, pression sur les hopitaux,
                    fragilite alimentaire et perte d acces aux services essentiels.
                </p>
            </article>
            <article class="context-card">
                <h3>Diplomatie et scenarios</h3>
                <p>
                    Les mediations restent fragiles. Les prochaines sequences dependent de la capacite a contenir la riposte,
                    relancer des canaux de negociation et proteger les infrastructures vitales.
                </p>
            </article>
        </div>
    </div>
</section>

<section class="section-space" aria-labelledby="section-data-live">
    <div class="container">
        <h2 id="section-data-live">Data interactive en direct</h2>
        <div class="context-grid">
            <article class="context-card">
                <h3>Carte des evenements</h3>
                <p>Visualisez les points geographiques, les zones sensibles et les pics d activite.</p>
                <p><a class="btn-outline" href="<?= BASE_URL ?>/carte">Ouvrir la carte</a></p>
            </article>
            <article class="context-card">
                <h3>Timeline 2024-2026</h3>
                <p>Parcourez l histoire recente du conflit avec une chronologie horizontale storytelling.</p>
                <p><a class="btn-outline" href="<?= BASE_URL ?>/timeline">Voir la timeline</a></p>
            </article>
            <article class="context-card">
                <h3>Statistiques dynamiques</h3>
                <p>Suivez l evolution des pertes, deplacements et sanctions via des graphes actualises.</p>
                <p><a class="btn-outline" href="<?= BASE_URL ?>/statistiques">Consulter les stats</a></p>
            </article>
        </div>
    </div>
</section>

<section class="section-space" aria-labelledby="section-editorial">
    <div class="container">
        <h2 id="section-editorial">Editorial premium et debats du moment</h2>
        <div class="context-grid">
            <?php foreach ($pinnedJournals as $journal): ?>
                <article class="context-card">
                    <h3><?= htmlspecialchars((string) $journal['title']) ?></h3>
                    <p><?= htmlspecialchars((string) $journal['summary']) ?></p>
                    <p><a href="<?= BASE_URL ?>/journaux" class="btn-outline">Voir les journaux</a></p>
                </article>
            <?php endforeach; ?>

            <?php foreach ($featuredDebates as $debate): ?>
                <article class="context-card">
                    <h3><?= htmlspecialchars((string) $debate['title']) ?></h3>
                    <p><?= htmlspecialchars((string) $debate['summary']) ?></p>
                    <p><a href="<?= BASE_URL ?>/debat/<?= htmlspecialchars((string) $debate['slug']) ?>" class="btn-outline">Participer au debat</a></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if (!empty($latestArticles)): ?>
    <section class="section-space">
        <div class="container">
            <h2>Analyses recentes</h2>
            <div class="grid grid-3" role="list">
                <?php foreach ($latestArticles as $article): ?>
                    <?php
                    $articleCard = $article;
                    $showMeta = false;
                    $titleTag = 'h3';
                    include ROOT . '/views/front/partials/article-card.php';
                    ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="section-space context-section" id="avis-utilisateurs">
    <div class="container">
        <h2>Avis utilisateurs et critiques de la redaction</h2>
        <p class="context-intro">
            Les avis sont moderes pour garantir un espace de discussion utile et respectueux.
            Un compte abonne n est pas obligatoire pour envoyer un avis, mais il permet un suivi premium.
        </p>

        <div class="context-grid">
            <article class="context-card">
                <h3>Donner un avis</h3>
                <form method="POST" action="<?= BASE_URL ?>/avis/create" class="review-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                    <div class="form-row">
                        <label for="author_name">Nom</label>
                        <input id="author_name" name="author_name" maxlength="120"
                               value="<?= htmlspecialchars((string) (\App\Core\Session::get('subscriber_name') ?? '')) ?>"
                               <?= \App\Core\Session::isSubscriber() ? 'readonly' : '' ?>>
                    </div>
                    <div class="form-row">
                        <label for="rating">Note (1-5)</label>
                        <select id="rating" name="rating" required>
                            <option value="">Choisir</option>
                            <option value="5">5 - Excellent</option>
                            <option value="4">4 - Tres bon</option>
                            <option value="3">3 - Correct</option>
                            <option value="2">2 - Faible</option>
                            <option value="1">1 - Insuffisant</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="comment">Commentaire</label>
                        <textarea id="comment" name="comment" minlength="12" maxlength="1200" required></textarea>
                    </div>
                    <button class="btn-primary" type="submit">Envoyer mon avis</button>
                </form>
            </article>

            <article class="context-card">
                <h3>Derniers avis approuves</h3>
                <?php if (empty($approvedReviews)): ?>
                    <p>Aucun avis publie pour le moment.</p>
                <?php else: ?>
                    <ul class="review-list">
                        <?php foreach ($approvedReviews as $review): ?>
                            <li>
                                <strong><?= htmlspecialchars((string) $review['author_name']) ?></strong>
                                <span>(<?= (int) $review['rating'] ?>/5)</span>
                                <p><?= htmlspecialchars((string) $review['comment']) ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </article>
        </div>
    </div>
</section>
