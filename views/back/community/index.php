<section class="dashboard">
    <h1>Communaute, premium et editorial</h1>

    <div class="stats-grid">
        <?php
        $statIcon = 'COM';
        $statValue = (string) ((int) ($counts['pending_comments'] ?? 0));
        $statLabel = 'Moderations en attente';
        include ROOT . '/views/back/partials/stat-card.php';

        $statIcon = 'MSG';
        $statValue = (string) ((int) ($counts['new_contacts'] ?? 0));
        $statLabel = 'Nouveaux messages';
        include ROOT . '/views/back/partials/stat-card.php';

        $statIcon = 'DEB';
        $statValue = (string) ((int) ($counts['open_debates'] ?? 0));
        $statLabel = 'Debats ouverts';
        include ROOT . '/views/back/partials/stat-card.php';

        $statIcon = 'SUB';
        $statValue = (string) count($subscribers);
        $statLabel = 'Abonnes visibles';
        include ROOT . '/views/back/partials/stat-card.php';
        ?>
    </div>

    <div class="stack-grid">
        <article class="admin-card">
            <h3>Ajouter un journal epingle</h3>
            <form method="POST" action="<?= ADMIN_PATH ?>/community/journals/create" class="card-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                <div class="form-group">
                    <label for="journal_title">Titre</label>
                    <input id="journal_title" name="journal_title" required maxlength="255">
                </div>
                <div class="form-group">
                    <label for="journal_summary">Resume</label>
                    <textarea id="journal_summary" name="journal_summary" required maxlength="1000"></textarea>
                </div>
                <div class="form-group">
                    <label for="journal_content">Contenu</label>
                    <textarea id="journal_content" name="journal_content" required></textarea>
                </div>
                <div class="form-group">
                    <label for="journal_pinned_order">Priorite (0 = plus haut)</label>
                    <input id="journal_pinned_order" name="journal_pinned_order" type="number" min="0" value="0">
                </div>
                <div class="form-group">
                    <label for="journal_status">Statut</label>
                    <select id="journal_status" name="journal_status">
                        <option value="published">Publie</option>
                        <option value="draft">Brouillon</option>
                    </select>
                </div>
                <button class="btn-save" type="submit">Publier journal</button>
            </form>
        </article>

        <article class="admin-card">
            <h3>Ajouter un debat</h3>
            <form method="POST" action="<?= ADMIN_PATH ?>/community/debates/create" class="card-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                <div class="form-group">
                    <label for="debate_title">Titre</label>
                    <input id="debate_title" name="debate_title" required maxlength="255">
                </div>
                <div class="form-group">
                    <label for="debate_summary">Resume</label>
                    <textarea id="debate_summary" name="debate_summary" required maxlength="1000"></textarea>
                </div>
                <div class="form-group">
                    <label for="debate_body">Contenu</label>
                    <textarea id="debate_body" name="debate_body" required></textarea>
                </div>
                <div class="form-group">
                    <label for="debate_status">Statut</label>
                    <select id="debate_status" name="debate_status">
                        <option value="open">Ouvert</option>
                        <option value="closed">Ferme</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="debate_is_pinned">Epingle en haut</label>
                    <select id="debate_is_pinned" name="debate_is_pinned">
                        <option value="1">Oui</option>
                        <option value="0" selected>Non</option>
                    </select>
                </div>
                <button class="btn-save" type="submit">Creer debat</button>
            </form>
        </article>
    </div>

    <section class="table-wrap">
        <h2>Commentaires a moderer</h2>
        <table class="admin-table">
            <thead>
            <tr>
                <th>Article</th>
                <th>Auteur</th>
                <th>Note</th>
                <th>Commentaire</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($pendingComments)): ?>
                <tr><td colspan="5">Aucun commentaire en attente.</td></tr>
            <?php else: ?>
                <?php foreach ($pendingComments as $comment): ?>
                    <tr>
                        <td>
                            <a href="<?= BASE_URL ?>/article-<?= (int) $comment['article_id'] ?>-<?= (int) $comment['category_id'] ?>.html" target="_blank" rel="noopener">
                                <?= htmlspecialchars((string) $comment['article_title']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars((string) $comment['author_name']) ?></td>
                        <td><?= (int) $comment['rating'] ?>/5</td>
                        <td><?= htmlspecialchars((string) $comment['content']) ?></td>
                        <td>
                            <form class="inline-form" method="POST" action="<?= ADMIN_PATH ?>/community/comments/approve/<?= (int) $comment['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                                <button class="btn-sm" type="submit">Approuver</button>
                            </form>
                            <form class="inline-form" method="POST" action="<?= ADMIN_PATH ?>/community/comments/reject/<?= (int) $comment['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                                <button class="btn-sm" type="submit">Rejeter</button>
                            </form>
                            <form class="inline-form" method="POST" action="<?= ADMIN_PATH ?>/community/comments/delete/<?= (int) $comment['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                                <button class="btn-sm" type="submit" data-confirm="Supprimer ce commentaire ?">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </section>

    <section class="table-wrap">
        <h2>Commentaires de debats a moderer</h2>
        <table class="admin-table">
            <thead>
            <tr>
                <th>Debat</th>
                <th>Auteur</th>
                <th>Commentaire</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($pendingDebateComments)): ?>
                <tr><td colspan="4">Aucun commentaire de debat en attente.</td></tr>
            <?php else: ?>
                <?php foreach ($pendingDebateComments as $comment): ?>
                    <tr>
                        <td>
                            <a href="<?= BASE_URL ?>/debat/<?= htmlspecialchars((string) $comment['debate_slug']) ?>" target="_blank" rel="noopener">
                                <?= htmlspecialchars((string) $comment['debate_title']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars((string) $comment['author_name']) ?></td>
                        <td><?= htmlspecialchars((string) $comment['content']) ?></td>
                        <td>
                            <form class="inline-form" method="POST" action="<?= ADMIN_PATH ?>/community/debate-comments/approve/<?= (int) $comment['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                                <button class="btn-sm" type="submit">Approuver</button>
                            </form>
                            <form class="inline-form" method="POST" action="<?= ADMIN_PATH ?>/community/debate-comments/reject/<?= (int) $comment['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                                <button class="btn-sm" type="submit">Rejeter</button>
                            </form>
                            <form class="inline-form" method="POST" action="<?= ADMIN_PATH ?>/community/debate-comments/delete/<?= (int) $comment['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                                <button class="btn-sm" type="submit" data-confirm="Supprimer ce commentaire ?">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </section>

    <section class="table-wrap">
        <h2>Avis utilisateurs a moderer</h2>
        <table class="admin-table">
            <thead>
            <tr>
                <th>Auteur</th>
                <th>Note</th>
                <th>Avis</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($pendingReviews)): ?>
                <tr><td colspan="4">Aucun avis en attente.</td></tr>
            <?php else: ?>
                <?php foreach ($pendingReviews as $review): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $review['author_name']) ?></td>
                        <td><?= (int) $review['rating'] ?>/5</td>
                        <td><?= htmlspecialchars((string) $review['comment']) ?></td>
                        <td>
                            <form class="inline-form" method="POST" action="<?= ADMIN_PATH ?>/community/reviews/approve/<?= (int) $review['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                                <button class="btn-sm" type="submit">Approuver</button>
                            </form>
                            <form class="inline-form" method="POST" action="<?= ADMIN_PATH ?>/community/reviews/reject/<?= (int) $review['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                                <button class="btn-sm" type="submit">Rejeter</button>
                            </form>
                            <form class="inline-form" method="POST" action="<?= ADMIN_PATH ?>/community/reviews/delete/<?= (int) $review['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                                <button class="btn-sm" type="submit" data-confirm="Supprimer cet avis ?">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </section>

    <section class="table-wrap">
        <h2>Abonnes</h2>
        <table class="admin-table">
            <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Plan</th>
                <th>Etat compte</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($subscribers as $subscriber): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $subscriber['full_name']) ?></td>
                    <td><?= htmlspecialchars((string) $subscriber['email']) ?></td>
                    <td><?= htmlspecialchars((string) $subscriber['plan']) ?></td>
                    <td><?= (int) $subscriber['is_active'] === 1 ? 'Actif' : 'Suspendu' ?></td>
                    <td>
                        <form class="inline-form" method="POST" action="<?= ADMIN_PATH ?>/community/subscribers/toggle/<?= (int) $subscriber['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                            <button class="btn-sm" type="submit">
                                <?= (int) $subscriber['is_subscribed'] === 1 ? 'Retirer premium' : 'Donner premium' ?>
                            </button>
                        </form>
                        <form class="inline-form" method="POST" action="<?= ADMIN_PATH ?>/community/subscribers/active/<?= (int) $subscriber['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                            <button class="btn-sm" type="submit">
                                <?= (int) $subscriber['is_active'] === 1 ? 'Suspendre' : 'Reactiver' ?>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="table-wrap">
        <h2>Messages de contact</h2>
        <table class="admin-table">
            <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Sujet</th>
                <th>Message</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($contacts as $contact): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $contact['full_name']) ?></td>
                    <td><?= htmlspecialchars((string) $contact['email']) ?></td>
                    <td><?= htmlspecialchars((string) $contact['subject']) ?></td>
                    <td><?= htmlspecialchars((string) $contact['message']) ?></td>
                    <td><?= htmlspecialchars((string) $contact['status']) ?></td>
                    <td>
                        <form class="inline-form" method="POST" action="<?= ADMIN_PATH ?>/community/contacts/read/<?= (int) $contact['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                            <button class="btn-sm" type="submit">Lu</button>
                        </form>
                        <form class="inline-form" method="POST" action="<?= ADMIN_PATH ?>/community/contacts/close/<?= (int) $contact['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                            <button class="btn-sm" type="submit">Cloturer</button>
                        </form>
                        <form class="inline-form" method="POST" action="<?= ADMIN_PATH ?>/community/contacts/delete/<?= (int) $contact['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                            <button class="btn-sm" type="submit" data-confirm="Supprimer ce message ?">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="table-wrap">
        <h2>Journaux epingles et debats recents</h2>
        <table class="admin-table">
            <thead>
            <tr>
                <th>Type</th>
                <th>Titre</th>
                <th>Meta</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($journals as $journal): ?>
                <tr>
                    <td>Journal</td>
                    <td><?= htmlspecialchars((string) $journal['title']) ?></td>
                    <td><?= htmlspecialchars((string) ($journal['published_at'] ?? $journal['created_at'] ?? '-')) ?></td>
                    <td>
                        <form class="inline-form" method="POST" action="<?= ADMIN_PATH ?>/community/journals/delete/<?= (int) $journal['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                            <button class="btn-sm" type="submit" data-confirm="Supprimer ce journal ?">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php foreach ($debates as $debate): ?>
                <tr>
                    <td>Debat</td>
                    <td><?= htmlspecialchars((string) $debate['title']) ?></td>
                    <td><?= htmlspecialchars((string) $debate['status']) ?> | <?= (int) $debate['comments_count'] ?> commentaires</td>
                    <td>
                        <form class="inline-form" method="POST" action="<?= ADMIN_PATH ?>/community/debates/delete/<?= (int) $debate['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                            <button class="btn-sm" type="submit" data-confirm="Supprimer ce debat ?">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</section>
