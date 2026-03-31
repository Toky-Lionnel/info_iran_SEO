<section class="section-space">
    <div class="container">
        <h1>Mon profil abonne</h1>
        <p class="page-intro">
            Mettez a jour vos informations, securisez votre compte et gerez votre abonnement.
        </p>

        <div class="portal-grid">
            <article class="portal-card">
                <h2>Informations personnelles</h2>
                <form method="POST" action="<?= BASE_URL ?>/compte/profil" class="review-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                    <input type="hidden" name="profile_action" value="update_profile">

                    <div class="form-row">
                        <label for="full_name">Nom complet</label>
                        <input id="full_name" name="full_name" required minlength="3" maxlength="120" value="<?= htmlspecialchars((string) ($subscriber['full_name'] ?? '')) ?>">
                    </div>

                    <div class="form-row">
                        <label>Email</label>
                        <input value="<?= htmlspecialchars((string) ($subscriber['email'] ?? '')) ?>" disabled>
                    </div>

                    <div class="form-row">
                        <label for="phone">Telephone</label>
                        <input id="phone" name="phone" maxlength="30" value="<?= htmlspecialchars((string) ($subscriber['phone'] ?? '')) ?>">
                    </div>

                    <div class="form-row">
                        <label for="country">Pays</label>
                        <input id="country" name="country" maxlength="80" value="<?= htmlspecialchars((string) ($subscriber['country'] ?? '')) ?>">
                    </div>

                    <div class="form-row">
                        <label for="city">Ville</label>
                        <input id="city" name="city" maxlength="120" value="<?= htmlspecialchars((string) ($subscriber['city'] ?? '')) ?>">
                    </div>

                    <div class="form-row">
                        <label for="interest_area">Interet principal</label>
                        <select id="interest_area" name="interest_area">
                            <?php
                            $currentInterest = (string) ($subscriber['interest_area'] ?? 'geopolitique');
                            $interestOptions = ['geopolitique', 'diplomatie', 'humanitaire', 'energie', 'cyber', 'media'];
                            foreach ($interestOptions as $interest):
                            ?>
                                <option value="<?= htmlspecialchars($interest) ?>" <?= $currentInterest === $interest ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(ucfirst($interest)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <label for="avatar_url">Avatar (URL)</label>
                        <input id="avatar_url" name="avatar_url" maxlength="255" value="<?= htmlspecialchars((string) ($subscriber['avatar_url'] ?? '')) ?>">
                    </div>

                    <div class="form-row">
                        <label for="bio">Bio courte</label>
                        <textarea id="bio" name="bio" maxlength="1000"><?= htmlspecialchars((string) ($subscriber['bio'] ?? '')) ?></textarea>
                    </div>

                    <div class="form-row">
                        <label>
                            <input type="checkbox" name="newsletter_optin" value="1" <?= ((int) ($subscriber['newsletter_optin'] ?? 1)) === 1 ? 'checked' : '' ?>>
                            Recevoir les nouveautes et alertes
                        </label>
                    </div>

                    <button class="btn-primary" type="submit">Mettre a jour mon profil</button>
                </form>
            </article>

            <article class="portal-card">
                <h2>Statut du compte</h2>
                <ul class="profile-stats">
                    <li><strong>Plan:</strong> <?= htmlspecialchars((string) ($subscriber['plan'] ?? 'free')) ?></li>
                    <li><strong>Premium:</strong> <?= ((int) ($subscriber['is_subscribed'] ?? 0) === 1) ? 'Oui' : 'Non' ?></li>
                    <li><strong>Compte actif:</strong> <?= ((int) ($subscriber['is_active'] ?? 0) === 1) ? 'Oui' : 'Non' ?></li>
                    <li><strong>Points:</strong> <?= (int) ($subscriber['points'] ?? 0) ?></li>
                    <li><strong>Derniere connexion:</strong> <?= htmlspecialchars((string) ($subscriber['last_login'] ?? '-')) ?></li>
                    <li><strong>Cree le:</strong> <?= htmlspecialchars((string) ($subscriber['created_at'] ?? '-')) ?></li>
                </ul>

                <h2>Changer le mot de passe</h2>
                <form method="POST" action="<?= BASE_URL ?>/compte/profil" class="review-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                    <input type="hidden" name="profile_action" value="change_password">
                    <div class="form-row">
                        <label for="current_password">Mot de passe actuel</label>
                        <input id="current_password" name="current_password" type="password" minlength="6" maxlength="120" required>
                    </div>
                    <div class="form-row">
                        <label for="new_password">Nouveau mot de passe</label>
                        <input id="new_password" name="new_password" type="password" minlength="8" maxlength="120" required>
                    </div>
                    <div class="form-row">
                        <label for="new_password_confirm">Confirmation</label>
                        <input id="new_password_confirm" name="new_password_confirm" type="password" minlength="8" maxlength="120" required>
                    </div>
                    <button class="btn-primary" type="submit">Changer le mot de passe</button>
                </form>

                <h2>Suppression du compte</h2>
                <form method="POST" action="<?= BASE_URL ?>/compte/profil" class="review-form danger-zone">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                    <input type="hidden" name="profile_action" value="delete_account">
                    <div class="form-row">
                        <label for="delete_confirm">Tapez SUPPRIMER pour confirmer</label>
                        <input id="delete_confirm" name="delete_confirm" maxlength="20" required>
                    </div>
                    <button class="btn-secondary danger" type="submit" data-confirm="Confirmer la suppression definitive du compte ?">Supprimer mon compte</button>
                </form>
            </article>

            <article class="portal-card">
                <h2>
                    Notifications
                    <span class="notification-badge" <?= ((int) ($unreadNotifications ?? 0) <= 0) ? 'hidden' : '' ?>>
                        <?= (int) ($unreadNotifications ?? 0) ?>
                    </span>
                </h2>
                <p>Recevez les alertes article/commentaire/debat et marquez-les comme lues.</p>
                <p>
                    <button type="button" class="btn-outline" id="mark-all-notifications">Tout marquer comme lu</button>
                </p>
                <ul class="portal-list" id="notification-list">
                    <?php if (empty($notifications)): ?>
                        <li>Aucune notification.</li>
                    <?php else: ?>
                        <?php foreach ($notifications as $notification): ?>
                            <li class="<?= ((int) ($notification['is_read'] ?? 0) === 0) ? 'notification-item unread' : 'notification-item' ?>">
                                <strong><?= htmlspecialchars((string) $notification['type']) ?></strong>
                                <p><?= htmlspecialchars((string) $notification['message']) ?></p>
                                <small><?= htmlspecialchars((string) $notification['created_at']) ?></small>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </article>

            <article class="portal-card">
                <h2>Lire plus tard</h2>
                <p>Vos articles favoris sauvegardes depuis le bouton "Sauvegarder article".</p>
                <?php if (empty($userFavorites)): ?>
                    <p>Aucun article sauvegarde pour le moment.</p>
                <?php else: ?>
                    <ul class="portal-list">
                        <?php foreach ($userFavorites as $favorite): ?>
                            <li>
                                <strong><?= htmlspecialchars((string) $favorite['title']) ?></strong>
                                <p><?= htmlspecialchars((string) $favorite['excerpt']) ?></p>
                                <p>
                                    <a href="<?= BASE_URL ?>/article-<?= (int) $favorite['article_id'] ?>-<?= (int) $favorite['category_id'] ?>.html">Lire l article</a>
                                </p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </article>
        </div>
    </div>
</section>
