<section class="dashboard">
    <h1>Securite avancee</h1>
    <p>Journal des connexions, echecs, blocages IP suspects et hygiene du cache.</p>

    <div class="stack-grid">
        <article class="admin-card">
            <h3>IPs suspectes (120 min)</h3>
            <?php if (empty($suspiciousIps)): ?>
                <p>Aucune IP suspecte detectee recemment.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                    <tr>
                        <th>IP</th>
                        <th>Echecs</th>
                        <th>Derniere tentative</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($suspiciousIps as $ipRow): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $ipRow['ip']) ?></td>
                            <td><?= (int) $ipRow['failed_count'] ?></td>
                            <td><?= htmlspecialchars((string) $ipRow['last_seen']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </article>

        <article class="admin-card">
            <h3>Maintenance cache</h3>
            <p>Nettoie les entrees expirees du cache HTML/API stocke en base.</p>
            <form method="POST" action="<?= ADMIN_PATH ?>/security/clear-cache">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                <button class="btn-save" type="submit">Nettoyer le cache expire</button>
            </form>
        </article>
    </div>

    <section class="table-wrap">
        <h2>Security logs</h2>
        <table class="admin-table">
            <thead>
            <tr>
                <th>Date</th>
                <th>IP</th>
                <th>Action</th>
                <th>Statut</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($recentLogs)): ?>
                <tr><td colspan="4">Aucune entree securite.</td></tr>
            <?php else: ?>
                <?php foreach ($recentLogs as $log): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $log['created_at']) ?></td>
                        <td><?= htmlspecialchars((string) $log['ip']) ?></td>
                        <td><?= htmlspecialchars((string) $log['action']) ?></td>
                        <td><?= htmlspecialchars((string) $log['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </section>
</section>
