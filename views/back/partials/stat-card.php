<?php
$statIcon = (string) ($statIcon ?? '');
$statValue = (string) ($statValue ?? '');
$statLabel = (string) ($statLabel ?? '');
?>
<div class="stat-card-admin">
    <div class="stat-icon"><?= htmlspecialchars($statIcon) ?></div>
    <div class="stat-value"><?= htmlspecialchars($statValue) ?></div>
    <div class="stat-label"><?= htmlspecialchars($statLabel) ?></div>
</div>
