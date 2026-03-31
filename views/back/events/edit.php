<section class="dashboard">
    <h1>Modifier un evenement</h1>
    <p><a class="btn-sm" href="<?= ADMIN_PATH ?>/events">Retour a la liste</a></p>

    <article class="admin-card">
        <form method="POST" action="<?= ADMIN_PATH ?>/events/edit/<?= (int) $event['id'] ?>" class="card-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
            <div class="form-group">
                <label for="event_title">Titre</label>
                <input id="event_title" name="title" maxlength="255" required value="<?= htmlspecialchars((string) $event['title']) ?>">
            </div>
            <div class="form-group">
                <label for="event_description">Description</label>
                <textarea id="event_description" name="description" rows="4" maxlength="5000" required><?= htmlspecialchars((string) $event['description']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="event_type">Type</label>
                <select id="event_type" name="type" required>
                    <?php foreach ($allowedTypes as $type): ?>
                        <option value="<?= htmlspecialchars($type) ?>" <?= ((string) $event['type'] === $type) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucfirst($type)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="event_city">Ville</label>
                <input id="event_city" name="city" maxlength="120" required value="<?= htmlspecialchars((string) $event['city']) ?>">
            </div>
            <div class="form-group">
                <label for="event_date">Date evenement</label>
                <input id="event_date" name="event_date" required value="<?= htmlspecialchars((string) $event['event_date']) ?>">
            </div>

            <div class="form-group map-picker-grid">
                <div>
                    <label for="event_latitude">Latitude</label>
                    <input id="event_latitude" name="latitude" required value="<?= htmlspecialchars((string) $event['latitude']) ?>">
                </div>
                <div>
                    <label for="event_longitude">Longitude</label>
                    <input id="event_longitude" name="longitude" required value="<?= htmlspecialchars((string) $event['longitude']) ?>">
                </div>
            </div>
            <div
                class="admin-map"
                id="admin-event-map-edit"
                data-lat-input="event_latitude"
                data-lng-input="event_longitude"
                data-lat="<?= htmlspecialchars((string) $event['latitude']) ?>"
                data-lng="<?= htmlspecialchars((string) $event['longitude']) ?>"
            ></div>

            <button class="btn-save" type="submit">Enregistrer</button>
        </form>
    </article>
</section>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="anonymous">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer crossorigin="anonymous"></script>
<script src="<?= BASE_URL ?>/public/js/back/map-picker.js" defer></script>
