<article class="article-detail" itemscope itemtype="https://schema.org/Article">
    <?php
    $articleSourceUrl = \App\Core\Helpers::getArticleImageSourceUrl((int) ($article['id'] ?? 0));
    ?>
    <header class="article-header">
        <div class="container">
            <?php
            $breadcrumbItems = [
                ['name' => 'Accueil', 'url' => BASE_URL . '/'],
                ['name' => 'Articles', 'url' => BASE_URL . '/articles'],
                ['name' => (string) $article['title'], 'url' => ''],
            ];
            include ROOT . '/views/front/partials/breadcrumb.php';
            ?>

            <?php
            $badgeColor = (string) $article['cat_color'];
            $badgeName = (string) $article['cat_name'];
            $badgeLink = BASE_URL . '/categorie-' . (int) $article['category_id'] . '-1.html';
            include ROOT . '/views/front/partials/category-badge.php';
            ?>

            <h1 itemprop="headline"><?= htmlspecialchars((string) $article['title']) ?></h1>
            <p class="article-excerpt" itemprop="description"><?= htmlspecialchars((string) $article['excerpt']) ?></p>

            <div class="article-meta">
                <span itemprop="author" itemscope itemtype="https://schema.org/Person">
                    Par <span itemprop="name"><?= htmlspecialchars((string) $article['author_name']) ?></span>
                </span>
                <time itemprop="datePublished" datetime="<?= htmlspecialchars((string) $article['published_at']) ?>">
                    <?= date('d/m/Y H:i', strtotime((string) $article['published_at'])) ?>
                </time>
                <meta itemprop="dateModified" content="<?= htmlspecialchars((string) $article['updated_at']) ?>">
                <span><?= (int) $article['views'] ?> lectures</span>
            </div>

            <?php if (!empty($articleTags)): ?>
                <div class="article-tags">
                    <?php foreach ($articleTags as $tag): ?>
                        <span class="category-pill">#<?= htmlspecialchars((string) $tag['name']) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <figure class="article-cover">
        <img src="<?= htmlspecialchars(\App\Core\Helpers::resolveArticleCover($article)) ?>"
             alt="<?= htmlspecialchars((string) ($article['cover_alt'] ?: $article['title'])) ?>"
             itemprop="image"
             width="1200"
             height="630"
             loading="eager">
        <figcaption><?= htmlspecialchars((string) $article['cover_alt']) ?></figcaption>
        <?php if ($articleSourceUrl !== null): ?>
            <p class="image-source-link">
                Source image:
                <a href="<?= htmlspecialchars($articleSourceUrl) ?>" target="_blank" rel="noopener nofollow">ouvrir la reference</a>
            </p>
        <?php endif; ?>
    </figure>

    <div class="article-content container" itemprop="articleBody">
        <?= $article['content'] ?>
    </div>

    <section class="container section-space article-engagement">
        <h2>Partage et avis</h2>
        <p class="article-rating-summary">
            Note moyenne: <strong><?= number_format((float) ($commentStats['avg_rating'] ?? 0), 1, ',', ' ') ?>/5</strong>
            (<?= (int) ($commentStats['total_reviews'] ?? 0) ?> avis publies)
        </p>
        <div class="share-buttons">
            <button type="button" class="btn-outline tts-toggle" data-target=".article-content">Ecouter</button>
            <?php if (\App\Core\Session::isSubscriber()): ?>
                <button type="button" class="btn-outline favorite-btn <?= !empty($isFavorite) ? 'active' : '' ?>" data-article-id="<?= (int) $article['id'] ?>">
                    <?= !empty($isFavorite) ? 'Retire de lire plus tard' : 'Sauvegarder article' ?> (<?= (int) ($favoritesCount ?? 0) ?>)
                </button>
            <?php else: ?>
                <a class="btn-outline" href="<?= BASE_URL ?>/compte/login">Se connecter pour sauvegarder</a>
            <?php endif; ?>
        </div>
        <div class="share-buttons" data-article-id="<?= (int) $article['id'] ?>">
            <button type="button" class="btn-outline share-btn" data-channel="copy">Copier le lien</button>
            <a class="btn-outline share-btn" data-channel="x" target="_blank" rel="noopener"
               href="https://x.com/intent/tweet?text=<?= rawurlencode((string) $article['title']) ?>&url=<?= rawurlencode(BASE_URL . '/article-' . (int) $article['id'] . '-' . (int) $article['category_id'] . '.html') ?>">X</a>
            <a class="btn-outline share-btn" data-channel="facebook" target="_blank" rel="noopener"
               href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode(BASE_URL . '/article-' . (int) $article['id'] . '-' . (int) $article['category_id'] . '.html') ?>">Facebook</a>
            <a class="btn-outline share-btn" data-channel="linkedin" target="_blank" rel="noopener"
               href="https://www.linkedin.com/sharing/share-offsite/?url=<?= rawurlencode(BASE_URL . '/article-' . (int) $article['id'] . '-' . (int) $article['category_id'] . '.html') ?>">LinkedIn</a>
        </div>
    </section>

    <section class="container section-space" id="comments">
        <h2>Commentaires et debats lecteurs</h2>
        <p>Les commentaires sont publies apres moderation editoriale.</p>

        <form method="POST" action="<?= BASE_URL ?>/article-<?= (int) $article['id'] ?>-<?= (int) $article['category_id'] ?>.html#comments" class="comment-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
            <input type="hidden" name="form_type" value="article_comment">

            <div class="form-row">
                <label for="author_name">Nom</label>
                <input id="author_name" name="author_name" maxlength="120"
                       value="<?= htmlspecialchars((string) (\App\Core\Session::get('subscriber_name') ?? '')) ?>"
                       <?= \App\Core\Session::isSubscriber() ? 'readonly' : 'required' ?>>
            </div>
            <div class="form-row">
                <label for="author_email">Email</label>
                <input id="author_email" type="email" name="author_email" maxlength="190"
                       value="<?= htmlspecialchars((string) (\App\Core\Session::get('subscriber_email') ?? '')) ?>"
                       <?= \App\Core\Session::isSubscriber() ? 'readonly' : 'required' ?>>
            </div>
            <div class="form-row">
                <label for="rating">Votre note</label>
                <select id="rating" name="rating" required>
                    <option value="">Choisir</option>
                    <option value="5">5 - Excellent</option>
                    <option value="4">4 - Tres bon</option>
                    <option value="3">3 - Correct</option>
                    <option value="2">2 - Moyen</option>
                    <option value="1">1 - Faible</option>
                </select>
            </div>
            <div class="form-row">
                <label for="content">Commentaire</label>
                <textarea id="content" name="content" minlength="10" maxlength="2000" required></textarea>
            </div>
            <button class="btn-primary" type="submit">Envoyer mon commentaire</button>
        </form>

        <?php if (empty($articleComments)): ?>
            <p>Aucun commentaire publie pour le moment.</p>
        <?php else: ?>
            <ul class="comment-list">
                <?php foreach ($articleComments as $comment): ?>
                    <li class="comment-item">
                        <p>
                            <strong><?= htmlspecialchars((string) $comment['author_name']) ?></strong>
                            <span class="comment-rating">(<?= (int) $comment['rating'] ?>/5)</span>
                            <span class="comment-rating">Score: <?= (int) ($comment['vote_score'] ?? 0) ?></span>
                        </p>
                        <p><?= htmlspecialchars((string) $comment['content']) ?></p>
                        <p class="comment-date"><?= htmlspecialchars((string) date('d/m/Y H:i', strtotime((string) $comment['created_at']))) ?></p>

                        <form method="POST" action="<?= BASE_URL ?>/article-<?= (int) $article['id'] ?>-<?= (int) $article['category_id'] ?>.html#comments" class="inline-form">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                            <input type="hidden" name="form_type" value="article_vote">
                            <input type="hidden" name="comment_id" value="<?= (int) $comment['id'] ?>">
                            <button class="btn-sm" type="submit" name="vote" value="1">+1</button>
                            <button class="btn-sm" type="submit" name="vote" value="-1">-1</button>
                        </form>

                        <?php $replies = $articleReplies[(int) $comment['id']] ?? []; ?>
                        <?php if (!empty($replies)): ?>
                            <ul class="comment-list">
                                <?php foreach ($replies as $reply): ?>
                                    <li class="comment-item">
                                        <p><strong><?= htmlspecialchars((string) $reply['author_name']) ?></strong></p>
                                        <p><?= htmlspecialchars((string) $reply['content']) ?></p>
                                        <p class="comment-date"><?= htmlspecialchars((string) date('d/m/Y H:i', strtotime((string) $reply['created_at']))) ?></p>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <form method="POST" action="<?= BASE_URL ?>/article-<?= (int) $article['id'] ?>-<?= (int) $article['category_id'] ?>.html#comments" class="comment-form">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                            <input type="hidden" name="form_type" value="article_reply">
                            <input type="hidden" name="comment_id" value="<?= (int) $comment['id'] ?>">
                            <div class="form-row">
                                <label>Repondre</label>
                                <textarea name="content" minlength="4" maxlength="1200" required></textarea>
                            </div>
                            <?php if (!\App\Core\Session::isSubscriber()): ?>
                                <div class="form-row">
                                    <label>Nom</label>
                                    <input name="author_name" maxlength="120" required>
                                </div>
                            <?php endif; ?>
                            <button class="btn-outline" type="submit">Repondre</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</article>

<?php if (!empty($relatedArticles)): ?>
    <section class="section-space">
        <div class="container">
            <h2>Articles lies</h2>
            <div class="grid grid-3">
                <?php foreach ($relatedArticles as $related): ?>
                    <?php
                    $articleCard = $related;
                    $showMeta = false;
                    $titleTag = 'h3';
                    include ROOT . '/views/front/partials/article-card.php';
                    ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
