<?php if (\App\Core\Session::isAdmin()): ?>
        </div>
    </div>
<?php else: ?>
    </div>
<?php endif; ?>

<script src="<?= BASE_URL ?>/public/js/back/slug.js" defer></script>
<script src="<?= BASE_URL ?>/public/js/back/counter.js" defer></script>
<script src="<?= BASE_URL ?>/public/js/back/confirm.js" defer></script>
<script src="<?= BASE_URL ?>/public/js/back/admin.js" defer></script>
<script src="<?= BASE_URL ?>/public/js/back/editor.js" defer></script>
</body>
</html>
