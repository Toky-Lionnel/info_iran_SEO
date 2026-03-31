<section class="section-space">
    <div class="container">
        <h1>Espace abonnes premium</h1>
        <p class="page-intro">
            Bienvenue <?= htmlspecialchars((string) (\App\Core\Session::get('subscriber_name') ?? 'abonne')) ?>.
            Cet espace regroupe les contenus reserves: notes strategiques, axes de veille et priorites de recherche.
        </p>
    </div>
</section>

<section class="section-space">
    <div class="container portal-grid">
        <article class="portal-card">
            <h2>Menu premium</h2>
            <ul class="portal-list">
                <li>Brief hebdomadaire securite regionale</li>
                <li>Tableau de risques geopolitiques 30/90 jours</li>
                <li>Veille energie et routes maritimes critiques</li>
                <li>Synthese diplomatique Europe - Golfe - Etats-Unis</li>
            </ul>
        </article>

        <article class="portal-card">
            <h2>Prochaine evolution</h2>
            <p>
                Cette section est prete pour ajouter du contenu reserve dans le back-office
                (dossiers PDF, notes flash, analyses premium par niveau d abonnement).
            </p>
            <p><a class="btn-outline" href="<?= BASE_URL ?>/compte/logout">Se deconnecter</a></p>
        </article>
    </div>
</section>
