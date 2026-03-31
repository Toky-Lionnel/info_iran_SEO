<div class="login-container">
    <div class="login-card">
        <div class="login-card-header">
            <p class="login-kicker">Administration</p>
            <h1>Connexion</h1>
            <p class="login-subtitle">Backoffice Guerre en Iran</p>
        </div>

        <?php $flash = \App\Core\Session::getFlash(); ?>
        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars((string) $flash['type']) ?>" role="alert">
                <?= htmlspecialchars((string) $flash['msg']) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= ADMIN_PATH ?>/login" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">

            <div class="form-group">
                <label for="username">Identifiant</label>
                <input type="text" id="username" name="username" required autocomplete="username" placeholder="admin">
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="admin123">
            </div>

            <button type="submit" class="btn-login">Se connecter</button>
        </form>

        <p class="login-hint">Identifiants par defaut : <strong>admin</strong> / <strong>admin123</strong></p>
    </div>
</div>
