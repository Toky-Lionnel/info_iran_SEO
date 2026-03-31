<section class="section-space">
    <div class="container">
        <h1>Debats publics</h1>
        <p class="page-intro">
            Espace de discussion sur les scenarios de sortie de crise, la diplomatie regionale et les impacts globaux.
        </p>
        <p class="page-intro">
            Les commentaires de debat passent en moderation pour maintenir un niveau professionnel.
        </p>

        <div class="portal-grid">
            <?php foreach ($debates as $debate): ?>
                <article class="portal-card">
                    <h2>
                        <a href="<?= BASE_URL ?>/debat/<?= htmlspecialchars((string) $debate['slug']) ?>">
                            <?= htmlspecialchars((string) $debate['title']) ?>
                        </a>
                    </h2>
                    <p><?= htmlspecialchars((string) $debate['summary']) ?></p>
                    <p>
                        <span class="badge" style="background:<?= (string) $debate['status'] === 'open' ? '#2E7D32' : '#546E7A' ?>">
                            <?= htmlspecialchars((string) $debate['status']) ?>
                        </span>
                        <span><?= (int) $debate['comments_count'] ?> commentaires</span>
                    </p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
