<?php
$paginationTotalPages = (int) ($paginationTotalPages ?? 1);
$paginationPage = (int) ($paginationPage ?? 1);
$paginationBaseUrl = (string) ($paginationBaseUrl ?? '');
$paginationQuery = $paginationQuery ?? [];
?>
<?php if ($paginationTotalPages > 1): ?>
    <nav class="pagination" aria-label="Pagination">
        <?php for ($p = 1; $p <= $paginationTotalPages; $p++): ?>
            <?php
            $query = $paginationQuery;
            $query['page'] = $p;
            $separator = str_contains($paginationBaseUrl, '?') ? '&' : '?';
            $url = $paginationBaseUrl . $separator . http_build_query($query);
            ?>
            <?php if ($p === $paginationPage): ?>
                <span class="active" aria-current="page"><?= $p ?></span>
            <?php else: ?>
                <a href="<?= htmlspecialchars($url) ?>"><?= $p ?></a>
            <?php endif; ?>
        <?php endfor; ?>
    </nav>
<?php endif; ?>
