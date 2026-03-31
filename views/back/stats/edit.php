<section class="dashboard">
    <h1>Modifier statistique</h1>
    <p><a class="btn-sm" href="<?= ADMIN_PATH ?>/stats">Retour a la liste</a></p>

    <article class="admin-card">
        <form method="POST" action="<?= ADMIN_PATH ?>/stats/edit/<?= (int) $stat['id'] ?>" class="card-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
            <div class="form-group">
                <label for="stats_type">Type</label>
                <select id="stats_type" name="type" required>
                    <?php foreach ($allowedTypes as $type): ?>
                        <option value="<?= htmlspecialchars($type) ?>" <?= ((string) $stat['type'] === $type) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucfirst($type)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="stats_value">Valeur</label>
                <input id="stats_value" type="number" min="0" step="1" name="value" required value="<?= (int) $stat['value'] ?>">
            </div>
            <div class="form-group">
                <label for="stats_date">Date</label>
                <input id="stats_date" type="date" name="stat_date" required value="<?= htmlspecialchars((string) $stat['stat_date']) ?>">
            </div>
            <button class="btn-save" type="submit">Enregistrer</button>
        </form>
    </article>
</section>
