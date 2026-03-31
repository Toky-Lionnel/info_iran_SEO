<section class="dashboard">
    <h1>Carte interactive - Gestion des evenements</h1>
    <p>Ajoutez, filtrez et modifiez les points geolocalises affiches sur la carte FO.</p>

    <div class="stack-grid">
        <article class="admin-card">
            <h3>Filtres</h3>
            <form method="GET" action="<?= ADMIN_PATH ?>/events" class="filter-form filter-form-wide">
                <label for="event_type_filter">Type</label>
                <select id="event_type_filter" name="type">
                    <option value="">Tous</option>
                    <?php foreach ($allowedTypes as $type): ?>
                        <option value="<?= htmlspecialchars($type) ?>" <?= (($filters['type'] ?? '') === $type) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucfirst($type)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="event_from_filter">Du</label>
                <input id="event_from_filter" type="date" name="date_from" value="<?= htmlspecialchars((string) ($filters['date_from'] ?? '')) ?>">

                <label for="event_to_filter">Au</label>
                <input id="event_to_filter" type="date" name="date_to" value="<?= htmlspecialchars((string) ($filters['date_to'] ?? '')) ?>">
                <button class="btn-sm" type="submit">Filtrer</button>
            </form>
        </article>

        <article class="admin-card">
            <h3>Ajouter un evenement</h3>
            <form method="POST" action="<?= ADMIN_PATH ?>/events/create" class="card-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                <div class="form-group">
                    <label for="event_title">Titre</label>
                    <input id="event_title" name="title" maxlength="255" required>
                </div>
                <div class="form-group">
                    <label for="event_description">Description</label>
                    <textarea id="event_description" name="description" rows="4" maxlength="5000" required></textarea>
                </div>
                <div class="form-group">
                    <label for="event_type">Type</label>
                    <select id="event_type" name="type" required>
                        <?php foreach ($allowedTypes as $type): ?>
                            <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars(ucfirst($type)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="event_city">Ville</label>
                    <input id="event_city" name="city" maxlength="120" required>
                </div>
                <div class="form-group">
                    <label for="event_date">Date evenement</label>
                    <input id="event_date" name="event_date" placeholder="YYYY-MM-DD HH:MM:SS" required>
                </div>

                <div class="form-group map-picker-grid">
                    <div>
                        <label for="event_latitude">Latitude</label>
                        <input id="event_latitude" name="latitude" required>
                    </div>
                    <div>
                        <label for="event_longitude">Longitude</label>
                        <input id="event_longitude" name="longitude" required>
                    </div>
                </div>
                <div class="admin-map" id="admin-event-map-create" data-lat-input="event_latitude" data-lng-input="event_longitude"></div>

                <button class="btn-save" type="submit">Creer evenement</button>
            </form>
        </article>
    </div>

    <section class="table-wrap">
        <h2>Liste des evenements</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Type</th>
                    <th>Ville</th>
                    <th>Date</th>
                    <th>Coordonnees</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($events)): ?>
                    <tr><td colspan="7">Aucun evenement.</td></tr>
                <?php else: ?>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?= (int) $event['id'] ?></td>
                            <td><?= htmlspecialchars((string) $event['title']) ?></td>
                            <td><?= htmlspecialchars((string) $event['type']) ?></td>
                            <td><?= htmlspecialchars((string) $event['city']) ?></td>
                            <td><?= htmlspecialchars((string) $event['event_date']) ?></td>
                            <td><?= htmlspecialchars((string) $event['latitude']) ?>, <?= htmlspecialchars((string) $event['longitude']) ?></td>
                            <td>
                                <a class="btn-sm" href="<?= ADMIN_PATH ?>/events/edit/<?= (int) $event['id'] ?>">Editer</a>
                                <form method="POST" action="<?= ADMIN_PATH ?>/events/delete/<?= (int) $event['id'] ?>" class="inline-form">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                                    <button type="submit" class="btn-sm" data-confirm="Supprimer cet evenement ?">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</section>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="anonymous">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer crossorigin="anonymous"></script>
<script src="<?= BASE_URL ?>/public/js/back/map-picker.js" defer></script>
