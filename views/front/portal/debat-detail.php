<section class="section-space">
    <div class="container">
        <h1><?= htmlspecialchars((string) $debate['title']) ?></h1>
        <p class="page-intro"><?= htmlspecialchars((string) $debate['summary']) ?></p>
        <div class="portal-card portal-content">
            <?= $debate['body'] ?>
        </div>
    </div>
</section>

<section class="section-space" id="debate-comments">
    <div class="container portal-grid">
        <article class="portal-card">
            <h2>Participer au debat</h2>
            <form method="POST" action="<?= BASE_URL ?>/debat/<?= htmlspecialchars((string) $debate['slug']) ?>#debate-comments" class="review-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                <input type="hidden" name="form_type" value="debate_comment">
                <div class="form-row">
                    <label for="author_name">Nom</label>
                    <input id="author_name" name="author_name" maxlength="100"
                           value="<?= htmlspecialchars((string) (\App\Core\Session::get('subscriber_name') ?? '')) ?>"
                           <?= \App\Core\Session::isSubscriber() ? 'readonly' : 'required' ?>>
                </div>
                <div class="form-row">
                    <label for="content">Commentaire</label>
                    <textarea id="content" name="content" minlength="8" maxlength="2000" required></textarea>
                </div>
                <button class="btn-primary" type="submit">Envoyer</button>
            </form>
        </article>

        <article class="portal-card">
            <h2>Derniers commentaires</h2>
            <?php if (empty($debateComments)): ?>
                <p>Aucun commentaire publie.</p>
            <?php else: ?>
                <ul class="review-list">
                    <?php foreach ($debateComments as $comment): ?>
                        <li>
                            <strong><?= htmlspecialchars((string) $comment['author_name']) ?></strong>
                            <span class="comment-rating">Score: <?= (int) ($comment['vote_score'] ?? 0) ?></span>
                            <p><?= htmlspecialchars((string) $comment['content']) ?></p>
                            <p class="comment-date"><?= htmlspecialchars((string) date('d/m/Y H:i', strtotime((string) $comment['created_at']))) ?></p>

                            <form method="POST" action="<?= BASE_URL ?>/debat/<?= htmlspecialchars((string) $debate['slug']) ?>#debate-comments" class="inline-form">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                                <input type="hidden" name="form_type" value="debate_vote">
                                <input type="hidden" name="comment_id" value="<?= (int) $comment['id'] ?>">
                                <button class="btn-sm" type="submit" name="vote" value="1">+1</button>
                                <button class="btn-sm" type="submit" name="vote" value="-1">-1</button>
                            </form>

                            <?php $replies = $debateReplies[(int) $comment['id']] ?? []; ?>
                            <?php if (!empty($replies)): ?>
                                <ul class="review-list">
                                    <?php foreach ($replies as $reply): ?>
                                        <li>
                                            <strong><?= htmlspecialchars((string) $reply['author_name']) ?></strong>
                                            <p><?= htmlspecialchars((string) $reply['content']) ?></p>
                                            <p class="comment-date"><?= htmlspecialchars((string) date('d/m/Y H:i', strtotime((string) $reply['created_at']))) ?></p>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>

                            <form method="POST" action="<?= BASE_URL ?>/debat/<?= htmlspecialchars((string) $debate['slug']) ?>#debate-comments" class="review-form">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                                <input type="hidden" name="form_type" value="debate_reply">
                                <input type="hidden" name="comment_id" value="<?= (int) $comment['id'] ?>">
                                <div class="form-row">
                                    <label>Repondre</label>
                                    <textarea name="content" minlength="4" maxlength="1200" required></textarea>
                                </div>
                                <?php if (!\App\Core\Session::isSubscriber()): ?>
                                    <div class="form-row">
                                        <label>Nom</label>
                                        <input name="author_name" maxlength="100" required>
                                    </div>
                                <?php endif; ?>
                                <button class="btn-outline" type="submit">Repondre</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </article>
    </div>
</section>
