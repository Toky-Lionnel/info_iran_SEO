<section class="dashboard">
    <h1>Exports / Imports Excel (CSV compatible)</h1>
    <p>Cette section genere des fichiers CSV ouvrables directement dans Excel et permet d'importer des abonnes.</p>

    <div class="stack-grid">
        <article class="admin-card">
            <h3>Exporter des rapports</h3>
            <div class="report-actions">
                <a class="btn-sm" href="<?= ADMIN_PATH ?>/reports/export/articles">Exporter articles</a>
                <a class="btn-sm" href="<?= ADMIN_PATH ?>/reports/export/subscribers">Exporter abonnes</a>
                <a class="btn-sm" href="<?= ADMIN_PATH ?>/reports/export/contacts">Exporter contacts</a>
                <a class="btn-sm" href="<?= ADMIN_PATH ?>/reports/export/comments">Exporter commentaires</a>
                <a class="btn-sm" href="<?= ADMIN_PATH ?>/reports/export/reviews">Exporter avis</a>
                <a class="btn-sm" href="<?= ADMIN_PATH ?>/reports/export/events">Exporter events</a>
                <a class="btn-sm" href="<?= ADMIN_PATH ?>/reports/export/timeline">Exporter timeline</a>
                <a class="btn-sm" href="<?= ADMIN_PATH ?>/reports/export/stats">Exporter stats</a>
                <a class="btn-sm" href="<?= ADMIN_PATH ?>/reports/export/analytics">Exporter analytics</a>
                <a class="btn-sm" href="<?= ADMIN_PATH ?>/reports/export/security">Exporter security logs</a>
                <a class="btn-sm" href="<?= ADMIN_PATH ?>/reports/export/favorites">Exporter favoris</a>
                <a class="btn-sm" href="<?= ADMIN_PATH ?>/reports/export/notifications">Exporter notifications</a>
            </div>
        </article>

        <article class="admin-card">
            <h3>Importer des abonnes</h3>
            <p>
                Format CSV attendu (separateur <code>;</code> ou <code>,</code>) avec au minimum:
                <code>full_name;email;password</code>.
            </p>
            <p>
                Colonnes optionnelles: <code>phone;country;city;interest_area;bio;newsletter_optin;points;avatar_url;plan;is_subscribed;is_active</code>
            </p>
            <p>Taille maximale autorisee: <strong>5 MB</strong>.</p>
            <form method="POST" action="<?= ADMIN_PATH ?>/reports/import/subscribers" enctype="multipart/form-data" class="card-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                <div class="form-group">
                    <label for="subscribers_file">Fichier CSV</label>
                    <input id="subscribers_file" name="subscribers_file" type="file" accept=".csv,text/csv" required>
                </div>
                <button class="btn-save" type="submit">Importer les abonnes</button>
            </form>
        </article>
    </div>

    <section class="table-wrap">
        <h2>Journal des operations</h2>
        <table class="admin-table">
            <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Dataset</th>
                <th>Fichier</th>
                <th>Lignes</th>
                <th>Statut</th>
                <th>Admin</th>
                <th>Notes</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($recentLogs)): ?>
                <tr><td colspan="8">Aucune operation enregistree.</td></tr>
            <?php else: ?>
                <?php foreach ($recentLogs as $log): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $log['created_at']) ?></td>
                        <td><?= htmlspecialchars((string) $log['exchange_type']) ?></td>
                        <td><?= htmlspecialchars((string) $log['dataset']) ?></td>
                        <td><?= htmlspecialchars((string) ($log['file_name'] ?? '-')) ?></td>
                        <td><?= (int) ($log['rows_count'] ?? 0) ?></td>
                        <td><?= htmlspecialchars((string) ($log['status'] ?? '-')) ?></td>
                        <td><?= htmlspecialchars((string) ($log['admin_username'] ?? '-')) ?></td>
                        <td><?= htmlspecialchars((string) ($log['notes'] ?? '-')) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </section>
</section>
