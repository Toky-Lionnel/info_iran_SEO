<?php
$pageTitle = (string) ($seo['title'] ?? APP_NAME);
$pageDesc = (string) ($seo['description'] ?? APP_DESC);
$canonical = (string) ($seo['canonical'] ?? BASE_URL . '/');
$ogType = (string) ($seo['og_type'] ?? 'website');
$ogImage = (string) ($seo['og_image'] ?? (BASE_URL . '/public/images/og-default.webp'));
$extraCss = $extraCss ?? [];
$navCategories = $navCategories ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars(mb_substr($pageDesc, 0, 160)) ?>">
    <meta name="csrf-token" content="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
    <meta name="base-url" content="<?= htmlspecialchars(BASE_URL) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars(mb_substr($pageDesc, 0, 200)) ?>">
    <meta property="og:type" content="<?= htmlspecialchars($ogType) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonical) ?>">
    <?php if ($ogImage !== ''): ?>
        <meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>">
    <?php endif; ?>
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:Segoe UI,system-ui,-apple-system,sans-serif;background:#0a0a0a;color:#e8e8e8;line-height:1.6}
        .navbar{background:#111;border-bottom:1px solid #2a2a2a;padding:1rem 1.25rem;display:flex;justify-content:space-between;align-items:center}
        .navbar-brand{color:#C62828;font-weight:800;text-decoration:none;font-size:1.2rem}
    </style>
    <link rel="preload" href="<?= BASE_URL ?>/public/css/front/variables.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="<?= BASE_URL ?>/public/css/front/variables.css"></noscript>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/front/reset.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/front/layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/front/components.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/front/main.css">
    <?php foreach ($extraCss as $css): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/front/<?= htmlspecialchars($css) ?>">
    <?php endforeach; ?>
    <script type="application/ld+json">
    <?= json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => APP_NAME,
        'url' => BASE_URL,
        'description' => APP_DESC,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
    </script>
    <?php if (!empty($schemaOrg)): ?>
        <?= $schemaOrg ?>
    <?php endif; ?>
</head>
<body>
<header>
    <nav class="navbar" role="navigation" aria-label="Navigation principale">
        <a href="<?= BASE_URL ?>/" class="navbar-brand">Guerre en Iran</a>
        <button class="nav-toggle" aria-expanded="false" aria-controls="nav-menu" aria-label="Ouvrir le menu">Menu</button>
        <ul class="nav-menu" id="nav-menu" role="list">
            <li><a href="<?= BASE_URL ?>/" <?= ($currentPage ?? '') === 'home' ? 'aria-current="page"' : '' ?>>Accueil</a></li>
            <li><a href="<?= BASE_URL ?>/articles" <?= ($currentPage ?? '') === 'articles' ? 'aria-current="page"' : '' ?>>Articles</a></li>
            <li class="nav-dropdown">
                <button class="nav-dropdown-toggle" type="button" aria-expanded="false">Rubriques</button>
                <ul class="dropdown-menu" role="list">
                    <?php foreach ($navCategories as $cat): ?>
                        <li><a href="<?= BASE_URL ?>/categorie-<?= (int) $cat['id'] ?>-1.html"><?= htmlspecialchars((string) $cat['name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </li>

            <!-- <li class="nav-dropdown">
                <button class="nav-dropdown-toggle" type="button" aria-expanded="false">Communaute</button>
                <ul class="dropdown-menu" role="list">
                    <li><a href="<?= BASE_URL ?>/nouveautes" <?= ($currentPage ?? '') === 'nouveautes' ? 'aria-current="page"' : '' ?>>Nouveautes</a></li>
                    <li><a href="<?= BASE_URL ?>/debats" <?= ($currentPage ?? '') === 'debats' ? 'aria-current="page"' : '' ?>>Debats</a></li>
                    <li><   a href="<?= BASE_URL ?>/journaux" <?= ($currentPage ?? '') === 'journaux' ? 'aria-current="page"' : '' ?>>Journaux epingles</a></li>
                    <li><a href="<?= BASE_URL ?>/archives" <?= ($currentPage ?? '') === 'archives' ? 'aria-current="page"' : '' ?>>Archives</a></li>
                    <li><a href="<?= BASE_URL ?>/carte" <?= ($currentPage ?? '') === 'map' ? 'aria-current="page"' : '' ?>>Carte</a></li>
                    <li><a href="<?= BASE_URL ?>/timeline" <?= ($currentPage ?? '') === 'timeline' ? 'aria-current="page"' : '' ?>>Timeline</a></li>
                    <li><a href="<?= BASE_URL ?>/statistiques" <?= ($currentPage ?? '') === 'stats' ? 'aria-current="page"' : '' ?>>Stats</a></li>
                    <li><a href="<?= BASE_URL ?>/contact" <?= ($currentPage ?? '') === 'contact' ? 'aria-current="page"' : '' ?>>Contact</a></li>
                </ul>
            </li> -->

            <!-- <li>
                <a href="<?= BASE_URL ?>/abonnes" <?= ($currentPage ?? '') === 'abonnes' ? 'aria-current="page"' : '' ?>>
                    <?= \App\Core\Session::isPremiumSubscriber() ? 'Menu abonne premium' : 'Menu abonne (verrouille)' ?>
                </a>
            </li>

            <?php if (\App\Core\Session::isSubscriber()): ?>
                <li>
                    <a href="<?= BASE_URL ?>/compte/profil" <?= ($currentPage ?? '') === 'account' ? 'aria-current="page"' : '' ?>>
                        Mon profil
                        <span class="notification-badge" hidden>0</span>
                    </a>
                </li>
                <li><a href="<?= BASE_URL ?>/compte/logout">Deconnexion compte</a></li>
            <?php else: ?>
                <li><a href="<?= BASE_URL ?>/compte/login" <?= ($currentPage ?? '') === 'account' ? 'aria-current="page"' : '' ?>>Compte abonne</a></li>
            <?php endif; ?> -->

            <li><a class="nav-admin-link" href="<?= ADMIN_PATH ?>">Back office</a></li>
        </ul>
    </nav>
    <?php $frontFlash = \App\Core\Session::getFlash(); ?>
    <?php if ($frontFlash): ?>
        <div class="container front-alert front-alert-<?= htmlspecialchars((string) $frontFlash['type']) ?>" role="alert">
            <?= htmlspecialchars((string) $frontFlash['msg']) ?>
        </div>
    <?php endif; ?>
</header>
<main id="main-content">
