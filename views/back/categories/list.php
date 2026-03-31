<section class="dashboard">
    <div class="toolbar">
        <h1>Categories</h1>
        <a href="<?= ADMIN_PATH ?>/categories/create" class="btn-save">Nouvelle categorie</a>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Slug</th>
                <th>Couleur</th>
                <th>Articles</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($categories as $cat): ?>
            <tr>
                <td><?= (int) $cat['id'] ?></td>
                <td><?= htmlspecialchars((string) $cat['name']) ?></td>
                <td><?= htmlspecialchars((string) $cat['slug']) ?></td>
                <td><span class="badge" style="background:<?= htmlspecialchars((string) $cat['color']) ?>"><?= htmlspecialchars((string) $cat['color']) ?></span></td>
                <td><?= (int) $cat['article_count'] ?></td>
                <td>
                    <a class="btn-sm" href="<?= ADMIN_PATH ?>/categories/edit/<?= (int) $cat['id'] ?>">Editer</a>
                    <form method="POST" action="<?= ADMIN_PATH ?>/categories/delete/<?= (int) $cat['id'] ?>" style="display:inline-block;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                        <button class="btn-sm" type="submit" data-confirm="Supprimer cette categorie ?">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
