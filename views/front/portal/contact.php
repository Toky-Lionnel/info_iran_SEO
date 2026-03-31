<section class="section-space">
    <div class="container portal-grid">
        <article class="portal-card">
            <h1>Contact redaction</h1>
            <p class="page-intro">
                Signaler une erreur, proposer une correction, suggerer un debat ou demander un partenariat media.
            </p>
            <p class="page-intro">
                Nous priorisons les messages argumentes, sources et respectueux.
            </p>
        </article>

        <article class="portal-card">
            <h2>Envoyer un message</h2>
            <form method="POST" action="<?= BASE_URL ?>/contact" class="review-form">
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
                    <label for="subject">Sujet</label>
                    <input id="subject" name="subject" minlength="5" maxlength="160" required>
                </div>
                <div class="form-row">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" minlength="20" maxlength="4000" required></textarea>
                </div>
                <button class="btn-primary" type="submit">Envoyer</button>
            </form>
        </article>
    </div>
</section>
