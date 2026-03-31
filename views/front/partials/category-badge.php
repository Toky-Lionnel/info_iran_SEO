<?php
$badgeColor = (string) ($badgeColor ?? '#C62828');
$badgeName = (string) ($badgeName ?? '');
$badgeLink = (string) ($badgeLink ?? '');
?>
<?php if ($badgeName !== ''): ?>
    <span class="badge" style="background:<?= htmlspecialchars($badgeColor) ?>">
        <?php if ($badgeLink !== ''): ?>
            <a href="<?= htmlspecialchars($badgeLink) ?>" style="color:#fff;text-decoration:none;">
                <?= htmlspecialchars($badgeName) ?>
            </a>
        <?php else: ?>
            <?= htmlspecialchars($badgeName) ?>
        <?php endif; ?>
    </span>
<?php endif; ?>
