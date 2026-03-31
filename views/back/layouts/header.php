<?php
$extraCss = $extraCss ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars((string) ($seo['title'] ?? 'Administration')) ?> - Iran Admin</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/back/back-style.css">

</head>
<body class="admin-body">
<?php if (\App\Core\Session::isAdmin()): ?>
    <div class="admin-layout">
        <?php include ROOT . '/views/back/partials/sidebar.php'; ?>
        <div class="admin-main">
            <?php $flash = \App\Core\Session::getFlash(); ?>
            <?php if ($flash): ?>
                <div class="alert alert-<?= htmlspecialchars((string) $flash['type']) ?>" role="alert">
                    <?= htmlspecialchars((string) $flash['msg']) ?>
                </div>
            <?php endif; ?>
<?php else: ?>
    <div class="auth-layout">
<?php endif; ?>
