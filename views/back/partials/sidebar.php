<aside class="sidebar" role="navigation" aria-label="Navigation administration">
    <div class="sidebar-brand">
        <span>Admin Iran</span>
        <a href="<?= BASE_URL ?>/" target="_blank" rel="noopener" title="Voir le site">Front</a>
    </div>
    <nav>
        <ul>
            <li class="sidebar-group">
                <button class="sidebar-group-toggle" type="button" aria-expanded="true">Editorial</button>
                <ul class="sidebar-submenu open">
                    <li><a href="<?= ADMIN_PATH ?>" class="<?= ($adminPage ?? '') === 'dashboard' ? 'active' : '' ?>">Dashboard</a></li>
                    <li><a href="<?= ADMIN_PATH ?>/articles" class="<?= ($adminPage ?? '') === 'articles' ? 'active' : '' ?>">Articles</a></li>
                    <li><a href="<?= ADMIN_PATH ?>/categories" class="<?= ($adminPage ?? '') === 'categories' ? 'active' : '' ?>">Categories</a></li>
                </ul>
            </li>

            <li class="sidebar-group">
                <button class="sidebar-group-toggle" type="button" aria-expanded="true">Utilisateurs</button>
                <ul class="sidebar-submenu open">
                    <li><a href="<?= ADMIN_PATH ?>/users" class="<?= ($adminPage ?? '') === 'users' ? 'active' : '' ?>">Admins</a></li>
                    <li><a href="<?= ADMIN_PATH ?>/subscribers" class="<?= ($adminPage ?? '') === 'subscribers' ? 'active' : '' ?>">Abonnes</a></li>
                    <li><a href="<?= ADMIN_PATH ?>/community" class="<?= ($adminPage ?? '') === 'community' ? 'active' : '' ?>">Communaute</a></li>
                </ul>
            </li>

            <li class="sidebar-group">
                <button class="sidebar-group-toggle" type="button" aria-expanded="true">Data</button>
                <ul class="sidebar-submenu open">
                    <li><a href="<?= ADMIN_PATH ?>/events" class="<?= ($adminPage ?? '') === 'events' ? 'active' : '' ?>">Carte events</a></li>
                    <li><a href="<?= ADMIN_PATH ?>/timeline" class="<?= ($adminPage ?? '') === 'timeline' ? 'active' : '' ?>">Timeline</a></li>
                    <li><a href="<?= ADMIN_PATH ?>/stats" class="<?= ($adminPage ?? '') === 'stats' ? 'active' : '' ?>">Stats</a></li>
                    <li><a href="<?= ADMIN_PATH ?>/security" class="<?= ($adminPage ?? '') === 'security' ? 'active' : '' ?>">Securite</a></li>
                    <li><a href="<?= ADMIN_PATH ?>/reports" class="<?= ($adminPage ?? '') === 'reports' ? 'active' : '' ?>">Reports Excel</a></li>
                </ul>
            </li>

            <li class="sidebar-bottom"><a href="<?= ADMIN_PATH ?>/logout">Deconnexion</a></li>
        </ul>
    </nav>
</aside>
