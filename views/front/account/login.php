<section class="section-space">
    <div class="container portal-grid">
        <article class="portal-card">
            <h1>Connexion compte abonne</h1>
            <p class="page-intro">
                Connectez-vous pour acceder aux menus premium, commenter plus rapidement et participer aux debats.
            </p>
            <p>
                Pas encore de compte ?
                <a href="<?= BASE_URL ?>/compte/register">Creer un compte abonne</a>.
            </p>
        </article>

        <article class="portal-card">
            <form method="POST" action="<?= BASE_URL ?>/compte/login" class="review-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                <div class="form-row">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" maxlength="190" required>
                </div>
                <div class="form-row">
                    <label for="password">Mot de passe</label>
                    <input id="password" name="password" type="password" minlength="6" maxlength="120" required>
                </div>
                <button class="btn-primary" type="submit">Se connecter</button>
            </form>
        </article>
    </div>
</section>
