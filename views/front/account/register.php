<section class="section-space">
    <div class="container portal-grid">
        <article class="portal-card">
            <h1>Creer un compte abonne</h1>
            <p class="page-intro">
                Le compte standard est gratuit et permet de suivre vos interactions.
                Le mode premium est active ensuite par l administration.
            </p>
            <p>
                Deja inscrit ?
                <a href="<?= BASE_URL ?>/compte/login">Connexion compte abonne</a>.
            </p>
        </article>

        <article class="portal-card">
            <form method="POST" action="<?= BASE_URL ?>/compte/register" class="review-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                <div class="form-row">
                    <label for="full_name">Nom complet</label>
                    <input id="full_name" name="full_name" minlength="3" maxlength="120" required>
                </div>
                <div class="form-row">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" maxlength="190" required>
                </div>
                <div class="form-row">
                    <label for="phone">Telephone</label>
                    <input id="phone" name="phone" maxlength="30" placeholder="+33 6 00 00 00 00">
                </div>
                <div class="form-row">
                    <label for="country">Pays</label>
                    <input id="country" name="country" maxlength="80">
                </div>
                <div class="form-row">
                    <label for="city">Ville</label>
                    <input id="city" name="city" maxlength="120">
                </div>
                <div class="form-row">
                    <label for="interest_area">Interet principal</label>
                    <select id="interest_area" name="interest_area">
                        <option value="geopolitique">Geopolitique</option>
                        <option value="diplomatie">Diplomatie</option>
                        <option value="humanitaire">Humanitaire</option>
                        <option value="energie">Energie</option>
                        <option value="cyber">Cyber</option>
                        <option value="media">Media</option>
                    </select>
                </div>
                <div class="form-row">
                    <label for="bio">Bio courte</label>
                    <textarea id="bio" name="bio" maxlength="1000" placeholder="Votre angle d'interet sur le conflit..."></textarea>
                </div>
                <div class="form-row">
                    <label for="password">Mot de passe</label>
                    <input id="password" name="password" type="password" minlength="8" maxlength="120" required>
                </div>
                <div class="form-row">
                    <label for="password_confirm">Confirmer</label>
                    <input id="password_confirm" name="password_confirm" type="password" minlength="8" maxlength="120" required>
                </div>
                <div class="form-row">
                    <label>
                        <input type="checkbox" name="newsletter_optin" value="1" checked>
                        Recevoir les alertes et nouveautes redactionnelles
                    </label>
                </div>
                <button class="btn-primary" type="submit">Creer mon compte</button>
            </form>
        </article>
    </div>
</section>
