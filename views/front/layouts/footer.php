</main>
<footer>
    <div class="container">
        <p><strong><?= htmlspecialchars(APP_NAME) ?></strong></p>
        <p>Couverture editoriale 2024-2026 : chronologie, diplomatie, impacts energetiques et humanitaires.</p>
        <p>
            <a href="<?= BASE_URL ?>/nouveautes">Nouveautes</a> |
            <a href="<?= BASE_URL ?>/debats">Debats</a> |
            <a href="<?= BASE_URL ?>/journaux">Journaux epingles</a> |
            <a href="<?= BASE_URL ?>/archives">Archives</a> |
            <a href="<?= BASE_URL ?>/carte">Carte</a> |
            <a href="<?= BASE_URL ?>/timeline">Timeline</a> |
            <a href="<?= BASE_URL ?>/statistiques">Stats</a> |
            <a href="<?= BASE_URL ?>/contact">Contact</a>
        </p>
        <p>
            <a href="<?= BASE_URL ?>/sitemap.xml">Sitemap XML</a> |
            <a href="<?= BASE_URL ?>/robots.txt">Robots.txt</a> |
            <a href="<?= ADMIN_PATH ?>">Back office</a>
        </p>
    </div>
</footer>
<!-- <?php
$extraJs = $extraJs ?? [];
$baseScripts = ['nav.js', 'lazyload.js', 'analytics.js'];
if (\App\Core\Session::isSubscriber()) {
    $baseScripts[] = 'notifications.js';
}
$scripts = array_values(array_unique(array_merge($baseScripts, $extraJs)));
foreach ($scripts as $jsFile):
    ?>
    <script src="<?= BASE_URL ?>/public/js/front/<?= htmlspecialchars($jsFile) ?>" defer></script>
<?php endforeach; ?> -->


<script src="<?= BASE_URL ?>/public/js/front/front.js" defer></script>
</body>
</html>
