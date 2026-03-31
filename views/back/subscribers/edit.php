<section class="dashboard">
    <div class="toolbar">
        <h1>Modifier un abonne</h1>
        <a class="btn-sm" href="<?= ADMIN_PATH ?>/subscribers">Retour liste</a>
    </div>

    <div class="stack-grid">
        <article class="admin-card">
            <h3>Profil et abonnement</h3>
            <form method="POST" action="<?= ADMIN_PATH ?>/subscribers/edit/<?= (int) $subscriber['id'] ?>" class="card-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                <input type="hidden" name="action" value="update">

                <div class="form-group">
                    <label for="full_name">Nom complet</label>
                    <input id="full_name" name="full_name" required minlength="3" maxlength="120" value="<?= htmlspecialchars((string) $subscriber['full_name']) ?>">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input value="<?= htmlspecialchars((string) $subscriber['email']) ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="phone">Telephone</label>
                    <input id="phone" name="phone" maxlength="30" value="<?= htmlspecialchars((string) ($subscriber['phone'] ?? '')) ?>">
                </div>

                <div class="form-group">
                    <label for="country">Pays</label>
                    <input id="country" name="country" maxlength="80" value="<?= htmlspecialchars((string) ($subscriber['country'] ?? '')) ?>">
                </div>

                <div class="form-group">
                    <label for="city">Ville</label>
                    <input id="city" name="city" maxlength="120" value="<?= htmlspecialchars((string) ($subscriber['city'] ?? '')) ?>">
                </div>

                <div class="form-group">
                    <label for="interest_area">Interet</label>
                    <input id="interest_area" name="interest_area" maxlength="120" value="<?= htmlspecialchars((string) ($subscriber['interest_area'] ?? 'geopolitique')) ?>">
                </div>

                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" maxlength="1000"><?= htmlspecialchars((string) ($subscriber['bio'] ?? '')) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="points">Points</label>
                    <input id="points" name="points" type="number" min="0" max="1000000" value="<?= (int) ($subscriber['points'] ?? 0) ?>">
                </div>

                <div class="form-group">
                    <label for="plan">Plan</label>
                    <select id="plan" name="plan">
                        <option value="free" <?= (($subscriber['plan'] ?? 'free') === 'free') ? 'selected' : '' ?>>free</option>
                        <option value="premium" <?= (($subscriber['plan'] ?? 'free') === 'premium') ? 'selected' : '' ?>>premium</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_subscribed" value="1" <?= ((int) ($subscriber['is_subscribed'] ?? 0) === 1) ? 'checked' : '' ?>>
                        Premium actif
                    </label>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_active" value="1" <?= ((int) ($subscriber['is_active'] ?? 0) === 1) ? 'checked' : '' ?>>
                        Compte actif
                    </label>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="newsletter_optin" value="1" <?= ((int) ($subscriber['newsletter_optin'] ?? 1) === 1) ? 'checked' : '' ?>>
                        Newsletter active
                    </label>
                </div>

                <button class="btn-save" type="submit">Enregistrer</button>
            </form>
        </article>

        <article class="admin-card">
            <h3>Securite compte</h3>
            <form method="POST" action="<?= ADMIN_PATH ?>/subscribers/edit/<?= (int) $subscriber['id'] ?>" class="card-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                <input type="hidden" name="action" value="password">
                <div class="form-group">
                    <label for="new_password">Nouveau mot de passe</label>
                    <input id="new_password" name="new_password" type="password" minlength="8" maxlength="120" required>
                </div>
                <div class="form-group">
                    <label for="new_password_confirm">Confirmation</label>
                    <input id="new_password_confirm" name="new_password_confirm" type="password" minlength="8" maxlength="120" required>
                </div>
                <button class="btn-save" type="submit">Changer le mot de passe</button>
            </form>

            <hr>

            <p><strong>Infos techniques</strong></p>
            <p>ID: <?= (int) $subscriber['id'] ?></p>
            <p>Derniere connexion: <?= htmlspecialchars((string) ($subscriber['last_login'] ?? '-')) ?></p>
            <p>Cree le: <?= htmlspecialchars((string) ($subscriber['created_at'] ?? '-')) ?></p>
            <p>Mis a jour le: <?= htmlspecialchars((string) ($subscriber['updated_at'] ?? '-')) ?></p>
        </article>
    </div>
</section>
