<?php
$breadcrumbItems = $breadcrumbItems ?? [];
?>
<?php if ($breadcrumbItems !== []): ?>
    <nav aria-label="Fil d Ariane" class="breadcrumb">
        <ol itemscope itemtype="https://schema.org/BreadcrumbList">
            <?php foreach ($breadcrumbItems as $index => $item): ?>
                <?php
                $position = $index + 1;
                $name = (string) ($item['name'] ?? '');
                $url = (string) ($item['url'] ?? '');
                ?>
                <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                    <?php if ($url !== ''): ?>
                        <a itemprop="item" href="<?= htmlspecialchars($url) ?>"><span itemprop="name"><?= htmlspecialchars($name) ?></span></a>
                    <?php else: ?>
                        <span itemprop="name"><?= htmlspecialchars($name) ?></span>
                    <?php endif; ?>
                    <meta itemprop="position" content="<?= $position ?>">
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>
<?php endif; ?>
