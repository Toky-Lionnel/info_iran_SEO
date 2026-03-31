<section class="dashboard">
    <h1>Timeline interactive - Gestion BO</h1>

    <div class="stack-grid">
        <article class="admin-card">
            <h3>Filtres</h3>
            <form method="GET" action="<?= ADMIN_PATH ?>/timeline" class="filter-form filter-form-wide">
                <label for="timeline_category_filter">Categorie</label>
                <select id="timeline_category_filter" name="category">
                    <option value="">Toutes</option>
                    <?php foreach ($allowedCategories as $category): ?>
                        <option value="<?= htmlspecialchars($category) ?>" <?= (($filters['category'] ?? '') === $category) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucfirst($category)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label for="timeline_from_filter">Du</label>
                <input id="timeline_from_filter" type="date" name="date_from" value="<?= htmlspecialchars((string) ($filters['date_from'] ?? '')) ?>">
                <label for="timeline_to_filter">Au</label>
                <input id="timeline_to_filter" type="date" name="date_to" value="<?= htmlspecialchars((string) ($filters['date_to'] ?? '')) ?>">
                <button class="btn-sm" type="submit">Filtrer</button>
            </form>
        </article>

        <article class="admin-card">
            <h3>Ajouter un element</h3>
            <form method="POST" action="<?= ADMIN_PATH ?>/timeline/create" class="card-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                <div class="form-group">
                    <label for="timeline_title">Titre</label>
                    <input id="timeline_title" name="title" maxlength="255" required>
                </div>
                <div class="form-group">
                    <label for="timeline_description">Description</label>
                    <textarea id="timeline_description" name="description" rows="4" maxlength="5000" required></textarea>
                </div>
                <div class="form-group">
                    <label for="timeline_category">Categorie</label>
                    <select id="timeline_category" name="category" required>
                        <?php foreach ($allowedCategories as $category): ?>
                            <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars(ucfirst($category)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="timeline_event_date">Date</label>
                    <input id="timeline_event_date" type="date" name="event_date" required>
                </div>
                <button class="btn-save" type="submit">Ajouter</button>
            </form>
        </article>
    </div>

    <section class="table-wrap">
        <h2>Elements timeline</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Categorie</th>
                    <th>Titre</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($timelineEvents)): ?>
                    <tr><td colspan="6">Aucun element timeline.</td></tr>
                <?php else: ?>
                    <?php foreach ($timelineEvents as $item): ?>
                        <tr>
                            <td><?= (int) $item['id'] ?></td>
                            <td><?= htmlspecialchars((string) $item['event_date']) ?></td>
                            <td><?= htmlspecialchars((string) $item['category']) ?></td>
                            <td><?= htmlspecialchars((string) $item['title']) ?></td>
                            <td><?= htmlspecialchars((string) mb_substr((string) $item['description'], 0, 140)) ?></td>
                            <td>
                                <a class="btn-sm" href="<?= ADMIN_PATH ?>/timeline/edit/<?= (int) $item['id'] ?>">Editer</a>
                                <form method="POST" action="<?= ADMIN_PATH ?>/timeline/delete/<?= (int) $item['id'] ?>" class="inline-form">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                                    <button class="btn-sm" type="submit" data-confirm="Supprimer cet element timeline ?">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</section>
