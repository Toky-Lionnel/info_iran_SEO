<section class="dashboard">
    <h1>Modifier element timeline</h1>
    <p><a class="btn-sm" href="<?= ADMIN_PATH ?>/timeline">Retour a la liste</a></p>

    <article class="admin-card">
        <form method="POST" action="<?= ADMIN_PATH ?>/timeline/edit/<?= (int) $timelineEvent['id'] ?>" class="card-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
            <div class="form-group">
                <label for="timeline_title">Titre</label>
                <input id="timeline_title" name="title" maxlength="255" required value="<?= htmlspecialchars((string) $timelineEvent['title']) ?>">
            </div>
            <div class="form-group">
                <label for="timeline_description">Description</label>
                <textarea id="timeline_description" name="description" rows="5" maxlength="5000" required><?= htmlspecialchars((string) $timelineEvent['description']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="timeline_category">Categorie</label>
                <select id="timeline_category" name="category" required>
                    <?php foreach ($allowedCategories as $category): ?>
                        <option value="<?= htmlspecialchars($category) ?>" <?= ((string) $timelineEvent['category'] === $category) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucfirst($category)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="timeline_event_date">Date</label>
                <input id="timeline_event_date" type="date" name="event_date" required value="<?= htmlspecialchars((string) $timelineEvent['event_date']) ?>">
            </div>
            <button class="btn-save" type="submit">Enregistrer</button>
        </form>
    </article>
</section>
