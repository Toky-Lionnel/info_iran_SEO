<?php
$errorFor = static function (string $field) use ($errors): string {
    return isset($errors[$field]) ? '<p class="field-error">' . htmlspecialchars((string) $errors[$field]) . '</p>' : '';
};
$action = $isEdit
    ? ADMIN_PATH . '/categories/edit/' . (int) $category['id']
    : ADMIN_PATH . '/categories/create';
?>
<section class="dashboard">
    <h1><?= $isEdit ? 'Modifier categorie' : 'Creer categorie' ?></h1>

    <form method="POST" action="<?= $action ?>" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">

        <div class="form-group">
            <label for="name">Nom *</label>
            <input type="text" id="name" name="name" required maxlength="150" value="<?= htmlspecialchars((string) ($formData['name'] ?? '')) ?>">
            <?= $errorFor('name') ?>
        </div>

        <div class="form-group">
            <label for="slug">Slug (auto si vide)</label>
            <input type="text" id="slug" name="slug" maxlength="100" value="<?= htmlspecialchars((string) ($formData['slug'] ?? '')) ?>">
            <?= $errorFor('slug') ?>
        </div>

        <div class="form-group">
            <label for="color">Couleur *</label>
            <input type="color" id="color" name="color" required value="<?= htmlspecialchars((string) ($formData['color'] ?? '#C62828')) ?>">
            <?= $errorFor('color') ?>
        </div>

        <button type="submit" class="btn-save"><?= $isEdit ? 'Sauvegarder' : 'Creer' ?></button>
    </form>
</section>
