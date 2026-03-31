<?php
$articleCard = $articleCard ?? [];
$showMeta = (bool) ($showMeta ?? true);
$titleTag = (string) ($titleTag ?? 'h3');
?>
<article class="article-card" role="listitem">
    <a href="<?= BASE_URL ?>/article-<?= (int) $articleCard['id'] ?>-<?= (int) $articleCard['category_id'] ?>.html">
        <img src="<?= htmlspecialchars(\App\Core\Helpers::resolveArticleCover($articleCard)) ?>"
             alt="<?= htmlspecialchars((string) ($articleCard['cover_alt'] ?: $articleCard['title'])) ?>"
             loading="lazy"
             width="400"
             height="225">
        <div class="card-body">
            <?php
            $badgeColor = (string) ($articleCard['cat_color'] ?? '#C62828');
            $badgeName = (string) ($articleCard['cat_name'] ?? '');
            $badgeLink = BASE_URL . '/categorie-' . (int) $articleCard['category_id'] . '-1.html';
            include ROOT . '/views/front/partials/category-badge.php';
            ?>
            <<?= $titleTag ?>><?= htmlspecialchars((string) $articleCard['title']) ?></<?= $titleTag ?>>
            <p><?= htmlspecialchars((string) $articleCard['excerpt']) ?></p>
            <?php if ($showMeta): ?>
                <footer class="card-meta">
                    <?php if (!empty($articleCard['published_at'])): ?>
                        <time datetime="<?= htmlspecialchars((string) $articleCard['published_at']) ?>">
                            <?= date('d/m/Y', strtotime((string) $articleCard['published_at'])) ?>
                        </time>
                    <?php endif; ?>
                    <?php if (!empty($articleCard['author_name'])): ?>
                        <span>Par <?= htmlspecialchars((string) $articleCard['author_name']) ?></span>
                    <?php elseif (isset($articleCard['views'])): ?>
                        <span><?= (int) $articleCard['views'] ?> lectures</span>
                    <?php endif; ?>
                </footer>
            <?php endif; ?>
        </div>
    </a>
</article>
