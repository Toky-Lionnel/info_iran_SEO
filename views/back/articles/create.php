<?php
$errorFor = static function (string $field) use ($errors): string {
    return isset($errors[$field]) ? '<p class="field-error">' . htmlspecialchars((string) $errors[$field]) . '</p>' : '';
};
?>
<section class="dashboard">
    <h1>Creer un article</h1>
    <form method="POST" action="<?= ADMIN_PATH ?>/articles/create" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">

        <div class="form-group">
            <label for="title">Titre *</label>
            <input type="text" id="title" name="title" maxlength="255" required value="<?= htmlspecialchars((string) ($formData['title'] ?? '')) ?>">
            <?= $errorFor('title') ?>
        </div>

        <div class="form-group">
            <label for="slug">Slug (auto si vide)</label>
            <input type="text" id="slug" name="slug" maxlength="200" value="<?= htmlspecialchars((string) ($formData['slug'] ?? '')) ?>">
            <?= $errorFor('slug') ?>
        </div>

        <div class="form-group">
            <label for="excerpt">Excerpt * (max 300)</label>
            <textarea id="excerpt" name="excerpt" maxlength="300" required><?= htmlspecialchars((string) ($formData['excerpt'] ?? '')) ?></textarea>
            <div id="excerpt-counter"></div>
            <?= $errorFor('excerpt') ?>
        </div>

        <div class="form-group">
            <label for="content">Contenu HTML *</label>
            <textarea id="content" name="content" required><?= htmlspecialchars((string) ($formData['content'] ?? '')) ?></textarea>
            <?= $errorFor('content') ?>
        </div>

        <div class="form-group">
            <label for="category_id">Categorie *</label>
            <select id="category_id" name="category_id" required>
                <option value="">Choisir...</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int) $cat['id'] ?>" <?= ((string) $cat['id'] === (string) ($formData['category_id'] ?? '')) ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) $cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?= $errorFor('category_id') ?>
        </div>

        <div class="form-group">
            <label for="author_id">Auteur *</label>
            <select id="author_id" name="author_id" required>
                <?php foreach ($authors as $author): ?>
                    <option value="<?= (int) $author['id'] ?>" <?= ((string) $author['id'] === (string) ($formData['author_id'] ?? '1')) ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) $author['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?= $errorFor('author_id') ?>
        </div>

        <div class="form-group">
            <label for="status">Statut *</label>
            <select id="status" name="status" required>
                <option value="draft" <?= ($formData['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>draft</option>
                <option value="published" <?= ($formData['status'] ?? '') === 'published' ? 'selected' : '' ?>>published</option>
                <option value="archived" <?= ($formData['status'] ?? '') === 'archived' ? 'selected' : '' ?>>archived</option>
            </select>
            <?= $errorFor('status') ?>
        </div>

        <div class="form-group">
            <label for="cover_image">Image de couverture (URL)</label>
            <input type="text" id="cover_image" name="cover_image" value="<?= htmlspecialchars((string) ($formData['cover_image'] ?? '')) ?>">
            <?= $errorFor('cover_image') ?>
        </div>

        <div class="form-group">
            <label for="cover_alt">Texte alt *</label>
            <input type="text" id="cover_alt" name="cover_alt" required value="<?= htmlspecialchars((string) ($formData['cover_alt'] ?? '')) ?>">
            <?= $errorFor('cover_alt') ?>
        </div>

        <div class="form-group">
            <label for="tags_input">Tags (separes par des virgules)</label>
            <input type="text" id="tags_input" name="tags_input" list="known-tags" value="<?= htmlspecialchars((string) ($formData['tags_input'] ?? '')) ?>" placeholder="ex: Ormuz, Energie, Diplomatie">
            <datalist id="known-tags">
                <?php foreach (($tags ?? []) as $tag): ?>
                    <option value="<?= htmlspecialchars((string) $tag['name']) ?>"></option>
                <?php endforeach; ?>
            </datalist>
            <?= $errorFor('tags_input') ?>
        </div>

        <button type="submit" class="btn-save">Enregistrer</button>
    </form>
</section>
