USE iran_war_db;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE data_exchange_logs;
TRUNCATE TABLE comment_votes;
TRUNCATE TABLE debate_comment_replies;
TRUNCATE TABLE article_comment_replies;
TRUNCATE TABLE analytics;
TRUNCATE TABLE notifications;
TRUNCATE TABLE article_favorites;
TRUNCATE TABLE seo_analysis;
TRUNCATE TABLE cache;
TRUNCATE TABLE security_logs;
TRUNCATE TABLE stats;
TRUNCATE TABLE timeline_events;
TRUNCATE TABLE events;
TRUNCATE TABLE site_reviews;
TRUNCATE TABLE contact_messages;
TRUNCATE TABLE debate_comments;
TRUNCATE TABLE debates;
TRUNCATE TABLE article_shares;
TRUNCATE TABLE article_comments;
TRUNCATE TABLE pinned_journals;
TRUNCATE TABLE subscribers;
TRUNCATE TABLE article_tags;
TRUNCATE TABLE tags;
TRUNCATE TABLE articles;
TRUNCATE TABLE admin_users;
TRUNCATE TABLE authors;
TRUNCATE TABLE categories;
SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO categories (id, slug, name, color) VALUES
    (1, 'chronologie', 'Chronologie', '#C62828'),
    (2, 'analyses', 'Analyses', '#1565C0'),
    (3, 'impacts', 'Impacts', '#EF6C00'),
    (4, 'diplomatie', 'Diplomatie', '#2E7D32');

INSERT INTO authors (id, slug, name, bio, avatar_url) VALUES
    (1, 'jean-marc-dupont', 'Jean-Marc Dupont', 'Correspondant Moyen-Orient.', '/iran/public/images/placeholder.webp'),
    (2, 'sophie-martin', 'Sophie Martin', 'Analyste geopolitique.', '/iran/public/images/placeholder.webp');

INSERT INTO tags (id, slug, name) VALUES
    (1, 'rising-lion', 'Rising Lion'),
    (2, 'midnight-hammer', 'Midnight Hammer'),
    (3, 'epic-fury', 'Epic Fury'),
    (4, 'ormuz', 'Detroit d Ormuz'),
    (5, 'natanz', 'Natanz'),
    (6, 'fordo', 'Fordo'),
    (7, 'diplomatie-europeenne', 'Diplomatie europeenne'),
    (8, 'crise-humanitaire', 'Crise humanitaire');

INSERT INTO articles (
    id, slug, title, excerpt, content, cover_image, cover_alt, category_id, author_id, status, views, reading_time, published_at
) VALUES
(
    1,
    'operation-rising-lion-israel-frappe-iran-13-juin-2025',
    'Operation Rising Lion : Israel frappe l Iran le 13 juin 2025',
    'Le 13 juin 2025, Israel lance Rising Lion contre des infrastructures iraniennes. Cette frappe ouvre la Guerre des Douze Jours.',
    '<h2>13 juin 2025</h2><p>L operation Rising Lion marque une escalade majeure entre Israel et l Iran. Des cibles militaires et nucleaires sont visees, avec des consequences immediates sur la securite regionale.</p><p>Cette offensive ouvre la Guerre des Douze Jours et reconfigure les rapports de force au Moyen-Orient.</p>',
    '/iran/public/images/placeholder.webp',
    'Carte des frappes israeliennes du 13 juin 2025',
    1,
    1,
    'published',
    18450,
    8,
    '2025-06-13 08:30:00'
),
(
    2,
    'operation-midnight-hammer-usa-fordo-natanz-ispahan',
    'Operation Midnight Hammer : les USA frappent Fordo, Natanz et Ispahan',
    'Dans la nuit du 22 au 23 juin 2025, les Etats-Unis visent Fordo, Natanz et Ispahan lors de l operation Midnight Hammer.',
    '<h2>Intervention americaine</h2><p>Washington entre directement dans la sequence militaire en frappant trois sites nucleaires majeurs. Les installations de Fordo, Natanz et Ispahan subissent des dommages lourds.</p><p>L operation amplifie la crise energetique et augmente le risque d affrontement regional durable.</p>',
    '/iran/public/images/placeholder.webp',
    'Sites nucleaires de Fordo Natanz et Ispahan',
    1,
    1,
    'published',
    17620,
    9,
    '2025-06-23 01:15:00'
),
(
    3,
    'guerre-des-douze-jours-bilan-13-24-juin-2025',
    'Guerre des Douze Jours : bilan d un conflit historique (13-24 juin 2025)',
    'Douze jours d offensives reciproques ont bouleverse la securite regionale et fait de nombreuses victimes civiles.',
    '<h2>Une guerre courte et intense</h2><p>Entre le 13 et le 24 juin 2025, la confrontation combine drones, missiles et frappes aeriennes. Les infrastructures strategiques et les zones proches de populations civiles sont fortement touchees.</p><p>Le bilan humain et geostrategique reste l un des plus marquants de la periode 2024-2026.</p>',
    '/iran/public/images/placeholder.webp',
    'Chronologie de la Guerre des Douze Jours',
    2,
    2,
    'published',
    15310,
    7,
    '2025-06-25 10:40:00'
),
(
    4,
    '28-fevrier-2026-epic-fury-debut-guerre-ouverte',
    '28 fevrier 2026 : l operation Epic Fury et le debut de la guerre ouverte',
    'Le 28 fevrier 2026, Epic Fury cote americain, Roaring Lion cote israelien et Promesse Honnete 4 cote iranien ouvrent la guerre ouverte.',
    '<h2>Point de bascule</h2><p>Le 28 fevrier 2026 fait passer le conflit vers une guerre declaree. Les operations simultanees changent d echelle et touchent plusieurs theatres militaires.</p><p>Les appels diplomatiques se multiplient mais la dynamique de riposte continue domine la sequence.</p>',
    '/iran/public/images/placeholder.webp',
    'Operations militaires du 28 fevrier 2026',
    1,
    1,
    'published',
    19120,
    8,
    '2026-02-28 09:00:00'
),
(
    5,
    'detroit-ormuz-menace-approvisionnement-energetique-mondial',
    'Detroit d Ormuz : la menace sur l approvisionnement energetique mondial',
    'La securite du detroit d Ormuz devient un facteur central de la crise energetique mondiale.',
    '<h2>Chokepoint strategique</h2><p>Le detroit d Ormuz concentre une part essentielle des flux petroliers et gaziers. Toute perturbation provoque des hausses de prix rapides sur les marches internationaux.</p><p>Les risques logistiques et assurantiels pesent directement sur les economies importatrices.</p>',
    '/iran/public/images/placeholder.webp',
    'Trafic maritime dans le detroit d Ormuz',
    3,
    2,
    'published',
    14005,
    6,
    '2026-03-01 12:20:00'
),
(
    6,
    'manifestations-en-iran-32000-morts-selon-trump',
    'Manifestations en Iran : 32 000 morts selon Trump, une repression sanglante',
    'Des declarations de Donald Trump evoquent 32 000 morts dans la repression des manifestations en Iran.',
    '<h2>Crise interne</h2><p>La guerre exterieure est accompagnee d une crise politique interieure en Iran. Les manifestations contre la degradation economique et securitaire se multiplient.</p><p>Le bilan des victimes reste dispute, mais les temoignages convergent sur une repression severe.</p>',
    '/iran/public/images/placeholder.webp',
    'Manifestations en Iran en 2026',
    1,
    1,
    'published',
    13370,
    6,
    '2026-03-03 16:00:00'
),
(
    7,
    'europe-face-a-crise-positions-macron-merz-ue',
    'L Europe face a la crise : positions de Macron, Merz et l UE',
    'La diplomatie europeenne tente de concilier desescalade, securite energetique et assistance humanitaire.',
    '<h2>Reponse europeenne</h2><p>Paris, Berlin et Bruxelles cherchent une ligne commune dans un contexte d escalade rapide. Les priorites varient entre mediation, sanctions et stabilisation des approvisionnements.</p><p>L UE renforce les dispositifs humanitaires et la coordination diplomatique region par region.</p>',
    '/iran/public/images/placeholder.webp',
    'Reunion diplomatique europeenne sur la crise iranienne',
    4,
    2,
    'published',
    11290,
    7,
    '2026-03-05 11:45:00'
),
(
    8,
    'programme-nucleaire-iranien-stuxnet-destruction-natanz',
    'Le programme nucleaire iranien : de Stuxnet a la destruction de Natanz',
    'Du sabotage cyber de Stuxnet aux frappes de 2025, le programme nucleaire iranien reste au coeur du conflit.',
    '<h2>Histoire longue</h2><p>Le dossier nucleaire iranien alterne cycles de negociation et phases de confrontation depuis plus d une decennie. Natanz est devenu une cible symbolique et strategique.</p><p>Les destructions de 2025 relancent le debat sur la proliferation et la verification internationale.</p>',
    '/iran/public/images/placeholder.webp',
    'Installation de Natanz apres les attaques',
    2,
    2,
    'published',
    12440,
    8,
    '2026-03-07 09:30:00'
),
(
    9,
    'south-pars-frappe-guerre-gaz-divise-trump-netanyahou',
    'South Pars frappe : la guerre du gaz qui divise Trump et Netanyahou',
    'Les attaques autour de South Pars accentuent la guerre du gaz et revelent des divergences tactiques entre allies.',
    '<h2>Pression sur le gaz</h2><p>South Pars est un point cle du systeme gazier regional. Les perturbations alimentent les tensions sur les prix et fragilisent les chaines de fourniture.</p><p>Les differences de posture entre responsables americains et israelienes sur l escalade compliquent la coordination politique.</p>',
    '/iran/public/images/placeholder.webp',
    'Complexe gazier de South Pars',
    3,
    1,
    'published',
    10875,
    7,
    '2026-03-09 14:10:00'
),
(
    10,
    'bilan-humanitaire-civils-patrimoine-unesco-crise-refugies',
    'Bilan humanitaire : civils, patrimoine UNESCO, crise des refugies',
    'Le conflit provoque des pertes civiles massives, une crise des refugies et des risques sur le patrimoine culturel.',
    '<h2>Urgence humanitaire</h2><p>Les populations civiles paient le prix principal de la guerre : deplacements forces, surcharge hospitaliere et insecurite alimentaire. Les agences internationales signalent une degradation continue des conditions de vie.</p><p>Le patrimoine historique est aussi menace, notamment dans des zones exposees aux bombardements.</p>',
    '/iran/public/images/placeholder.webp',
    'Aide humanitaire et deplacements de civils',
    3,
    2,
    'published',
    16540,
    9,
    '2026-03-10 18:25:00'
);

INSERT INTO articles (
    id, slug, title, excerpt, content, cover_image, cover_alt, category_id, author_id, status, views, reading_time, published_at
) VALUES
(
    11,
    'sanctions-financieres-et-resilience-economie-iranienne-2026',
    'Sanctions financieres et resilience de l economie iranienne en 2026',
    'Entre pression monetaire, inflation et adaptation interne, l economie iranienne montre des points de fragilite mais aussi de resilience.',
    '<h2>Pression externe et ajustements internes</h2><p>Le renforcement des sanctions bancaires et energetiques fragilise les circuits de paiement internationaux et les capacites d importation. La volatilite du change alimente une inflation durable sur les produits essentiels.</p><p>En parallele, des mecanismes de contournement et des partenariats regionaux permettent de maintenir une partie des flux commerciaux, au prix de couts logistiques plus eleves.</p><h3>Effets sociaux</h3><p>La hausse des prix et la baisse du pouvoir d achat accentuent les tensions sociales. Les depenses publiques prioritaires se concentrent sur la stabilite energetique et alimentaire.</p>',
    '/iran/public/images/placeholder.webp',
    'Marche urbain en Iran sous pression inflationniste',
    3,
    2,
    'published',
    9870,
    8,
    '2026-03-11 09:20:00'
),
(
    12,
    'role-des-reseaux-proxies-regionaux-dans-escalade',
    'Le role des reseaux proxies regionaux dans la dynamique d escalade',
    'Les groupes allies regionaux modifient le rythme et la geographie de la confrontation, en ouvrant des fronts indirects.',
    '<h2>Conflit multi-theatres</h2><p>La confrontation ne se limite pas aux frappes directes entre Etats. Les reseaux allies locaux servent de relais tactiques pour la pression militaire, la dissuasion et la guerre d usure.</p><p>Cette configuration rend la desescalade plus complexe, car plusieurs centres de decision peuvent produire des incidents a fort impact politique.</p><h3>Impact strategique</h3><p>Les Etats cherchent a garder une plausibilite diplomatique tout en conservant une capacite d action indirecte, ce qui entretient un risque de malentendu permanent.</p>',
    '/iran/public/images/placeholder.webp',
    'Carte regionale des zones d influence et de tension',
    2,
    1,
    'published',
    10430,
    7,
    '2026-03-12 14:05:00'
),
(
    13,
    'cyberconflit-iran-israel-infrastructures-critiques',
    'Cyberconflit Iran Israel : les infrastructures critiques en premiere ligne',
    'Au-dela des frappes conventionnelles, le cyberconflit vise energie, transport, telecoms et systemes industriels strategiques.',
    '<h2>Une guerre de l ombre permanente</h2><p>Les operations cyber touchent les chaines de commande, la maintenance industrielle et la resilience des reseaux. Les effets peuvent etre immediats ou differes, avec des interruptions de service difficiles a attribuer publiquement.</p><p>Les Etats combinent defense active, redondance technique et contre-mesures informationnelles pour limiter l impact sur les populations.</p><h3>Risque systemique</h3><p>Quand le cyber et le militaire conventionnel se superposent, la marge d erreur diminue fortement et les risques de reaction disproportionnee augmentent.</p>',
    '/iran/public/images/placeholder.webp',
    'Centre de supervision d infrastructures critiques',
    2,
    2,
    'published',
    11110,
    8,
    '2026-03-13 08:50:00'
),
(
    14,
    'scenarios-2026-2027-desescalade-cessez-le-feu-conflit-long',
    'Scenarios 2026-2027 : desescalade, cessez-le-feu ou conflit long',
    'Trois trajectoires se dessinent pour la suite: gel des fronts, cessez-le-feu negocie ou maintien d une confrontation prolongee.',
    '<h2>Scenario 1: desescalade controlee</h2><p>Des canaux indirects permettent de reduire la frequence des frappes et de stabiliser les lignes rouges.</p><h2>Scenario 2: cessez-le-feu partiel</h2><p>Un accord limite protege certaines infrastructures et facilite l aide humanitaire, sans regler les causes profondes.</p><h2>Scenario 3: conflit long</h2><p>Les cycles de riposte se poursuivent avec cout humain et economique croissant. La prevention d un incident majeur devient l objectif central des mediations internationales.</p>',
    '/iran/public/images/placeholder.webp',
    'Table de negociation internationale sur la securite regionale',
    4,
    1,
    'published',
    11980,
    8,
    '2026-03-14 17:35:00'
);

INSERT INTO article_tags (article_id, tag_id) VALUES
    (1, 1), (1, 5),
    (2, 2), (2, 5), (2, 6),
    (3, 1), (3, 2),
    (4, 3),
    (5, 4),
    (6, 8),
    (7, 7),
    (8, 5),
    (9, 4),
    (10, 8),
    (11, 4), (11, 8),
    (12, 1), (12, 3), (12, 7),
    (13, 5), (13, 6),
    (14, 7), (14, 8);

INSERT INTO admin_users (id, username, password_hash, email, role, last_login) VALUES
    (1, 'admin', '$2y$10$HRfHi43RszWt8Sh.UM62ruDB33Ng4eynwYJ8RtvVPgdeQqayMbPxy', 'admin@iran-war.local', 'admin', NULL);

INSERT INTO subscribers (
    id, full_name, email, password_hash, phone, country, city, interest_area, bio,
    newsletter_optin, points, avatar_url, plan, is_subscribed, is_active, last_login
) VALUES
    (
        1,
        'Nadia Rahimi',
        'nadia.premium@iran.local',
        '$2y$10$Kw6TmeattwRPy5lA/pLyOOcjT/x2k.l88hL783LnRQX8K3ZSDCi.u',
        '+33 6 12 34 56 78',
        'France',
        'Lyon',
        'diplomatie',
        'Lectrice premium orientee diplomatie et analyse des institutions.',
        1,
        140,
        '/iran/public/images/placeholder.webp',
        'premium',
        1,
        1,
        '2026-03-16 18:35:00'
    ),
    (
        2,
        'Youssef Karim',
        'youssef.free@iran.local',
        '$2y$10$uuqoESFPEE9Zk/srGu6kv.euGqBMx.3ARBqxmn2pbt9n1aa940QmG',
        '+212 6 20 30 40 50',
        'Maroc',
        'Casablanca',
        'energie',
        'Etudiant en politiques energetiques, interesse par Ormuz et South Pars.',
        1,
        55,
        '/iran/public/images/placeholder.webp',
        'free',
        0,
        1,
        '2026-03-15 09:20:00'
    ),
    (
        3,
        'Leila Haddad',
        'leila.subscriber@iran.local',
        '$2y$10$M4HGJkqdQ78D4hDgu1guSOkeJx8SFaZaQ99EZvLKuXvY3Z7S..M3u',
        '+216 22 44 88 11',
        'Tunisie',
        'Tunis',
        'humanitaire',
        'Contributrice reguliere sur les sujets humanitaires et les deplacements forces.',
        0,
        88,
        '/iran/public/images/placeholder.webp',
        'premium',
        1,
        1,
        NULL
    );

INSERT INTO pinned_journals (id, slug, title, summary, content, pinned_order, status, published_at) VALUES
    (
        1,
        'journal-strategique-semaine-11-2026',
        'Journal strategique - Semaine 11 2026',
        'Synthese des signaux faibles: posture militaire, risques energetiques et dynamique diplomatique.',
        '<h2>Points cle de la semaine</h2><p>La tension regionale reste elevee, avec des signaux contradictoires entre posture de dissuasion et messages d ouverture diplomatique.</p><p>Le suivi combine des indicateurs militaires, economiques et humanitaires pour maintenir une lecture transversale de la crise.</p>',
        0,
        'published',
        '2026-03-15 08:00:00'
    ),
    (
        2,
        'journal-humanitaire-et-resilience-civile',
        'Journal humanitaire et resilience civile',
        'Etat des systemes de sante, des flux de deplacement et des capacites d assistance locale.',
        '<h2>Resilience locale</h2><p>Les structures civiles s adaptent avec des moyens limites. Les corridors d aide restent une priorite operationnelle.</p>',
        1,
        'published',
        '2026-03-16 09:10:00'
    );

INSERT INTO debates (id, slug, title, summary, body, status, is_pinned) VALUES
    (
        1,
        'faut-il-prioriser-un-cessez-le-feu-partiel',
        'Faut-il prioriser un cessez-le-feu partiel ?',
        'Debat sur les avantages et limites d un accord limite centre sur la protection des civils.',
        '<h2>Question centrale</h2><p>Un cessez-le-feu partiel peut reduire rapidement les pertes civiles, mais risque de geler des tensions non resolues.</p><p>Ce debat examine les options realistes de mise en oeuvre et les garanties minimales.</p>',
        'open',
        1
    ),
    (
        2,
        'quelle-doctrine-energetique-pour-l-europe',
        'Quelle doctrine energetique pour l Europe face a la crise ?',
        'Debat sur la securisation des approvisionnements et la reduction des vulnerabilites.',
        '<h2>Hypothese de travail</h2><p>La reconfiguration des flux energetiques impose une coordination renforcee entre sobriete, stockage et diversification.</p>',
        'open',
        0
    );

INSERT INTO article_comments (id, article_id, subscriber_id, author_name, author_email, content, rating, status, created_at) VALUES
    (1, 1, 1, 'Nadia Rahimi', 'nadia.premium@iran.local', 'Analyse solide et bien structuree sur les enchainements tactiques.', 5, 'approved', '2026-03-16 13:40:00'),
    (2, 5, 2, 'Youssef Karim', 'youssef.free@iran.local', 'Tres utile pour comprendre les repercussions economiques globales.', 4, 'approved', '2026-03-16 14:10:00'),
    (3, 8, NULL, 'Lecteur Independant', 'lecteur@example.com', 'Merci pour le rappel historique, le contexte est plus clair.', 4, 'pending', '2026-03-16 14:35:00');

INSERT INTO debate_comments (id, debate_id, subscriber_id, author_name, content, status, created_at) VALUES
    (1, 1, 1, 'Nadia Rahimi', 'Un cadre progressif en plusieurs phases parait le plus realiste.', 'approved', '2026-03-16 15:00:00'),
    (2, 1, NULL, 'Observateur FO', 'Il faut aussi prevoir des mecanismes de verification independants.', 'pending', '2026-03-16 15:20:00');

INSERT INTO contact_messages (id, full_name, email, subject, message, status, created_at) VALUES
    (1, 'Meriem Said', 'meriem@example.com', 'Correction factuelle', 'Bonjour, je propose une correction sur la chronologie du 24 juin 2025.', 'new', '2026-03-16 15:45:00'),
    (2, 'Collectif Etudiant', 'collectif@example.com', 'Demande d interview', 'Nous souhaitons un echange redactionnel pour un atelier pedagogique.', 'read', '2026-03-16 16:05:00');

INSERT INTO site_reviews (id, subscriber_id, author_name, rating, comment, status, created_at) VALUES
    (1, 1, 'Nadia Rahimi', 5, 'Plateforme claire, angles editoriaux utiles et navigation professionnelle.', 'approved', '2026-03-16 16:20:00'),
    (2, NULL, 'Lecteur Public', 4, 'Bonne synthese generale, j aimerais encore plus de cartes comparatives.', 'approved', '2026-03-16 16:30:00');

INSERT INTO article_shares (id, article_id, channel, shared_at) VALUES
    (1, 1, 'x', '2026-03-16 17:00:00'),
    (2, 5, 'linkedin', '2026-03-16 17:05:00'),
    (3, 10, 'facebook', '2026-03-16 17:10:00');

INSERT INTO data_exchange_logs (id, admin_user_id, exchange_type, dataset, file_name, rows_count, status, notes, created_at) VALUES
    (1, 1, 'export', 'articles', 'articles-report-2026-03-16.csv', 14, 'success', 'Export hebdomadaire du tableau de bord.', '2026-03-16 18:40:00'),
    (2, 1, 'import', 'subscribers', 'subscribers-batch-2026-03-16.csv', 3, 'success', 'Import initial des abonnes de demonstration.', '2026-03-16 18:45:00');

INSERT INTO events (id, title, description, type, latitude, longitude, city, event_date, created_at) VALUES
    (1, 'Frappes sur Natanz', 'Serie de frappes ciblant des infrastructures strategiques.', 'bombardement', 33.7243, 51.7258, 'Natanz', '2025-06-13 06:40:00', '2025-06-13 07:00:00'),
    (2, 'Manifestation a Teheran', 'Rassemblement civil contre l escalade militaire.', 'manifestation', 35.6892, 51.3890, 'Teheran', '2026-03-03 17:15:00', '2026-03-03 17:30:00'),
    (3, 'Canal diplomatique Oman', 'Reouverture d un canal diplomatique indirect.', 'diplomatique', 35.3219, 46.9862, 'Kermanshah', '2026-03-08 10:20:00', '2026-03-08 10:45:00'),
    (4, 'Renforcement militaire au sud', 'Deploiement d unites et controle des axes.', 'militaire', 29.5918, 52.5837, 'Shiraz', '2026-02-28 09:40:00', '2026-02-28 10:05:00'),
    (5, 'Declaration politique nationale', 'Annonce officielle sur la strategie nationale.', 'politique', 32.6546, 51.6680, 'Ispahan', '2026-03-05 13:00:00', '2026-03-05 13:20:00');

INSERT INTO timeline_events (id, title, description, category, event_date, created_at) VALUES
    (1, 'Montee des tensions regionales', 'Hausse des incidents et menaces croisees.', 'politique', '2024-11-10', '2024-11-10 09:00:00'),
    (2, 'Rising Lion', 'Debut de la sequence militaire des douze jours.', 'militaire', '2025-06-13', '2025-06-13 08:00:00'),
    (3, 'Midnight Hammer', 'Intervention americaine sur des sites nucleaires.', 'militaire', '2025-06-23', '2025-06-23 02:00:00'),
    (4, 'Cycle diplomatique UE', 'Initiatives de mediation et messages de desescalade.', 'diplomatique', '2025-12-02', '2025-12-02 10:00:00'),
    (5, 'Epic Fury et guerre ouverte', 'Point de bascule du 28 fevrier 2026.', 'militaire', '2026-02-28', '2026-02-28 09:00:00'),
    (6, 'Nouvelles negociations indirectes', 'Tentatives de stabilisation des lignes rouges.', 'diplomatique', '2026-03-12', '2026-03-12 16:00:00');

INSERT INTO stats (id, type, value, stat_date, created_at) VALUES
    (1, 'pertes', 11500, '2025-06-30', '2025-06-30 20:00:00'),
    (2, 'deplacements', 280000, '2025-06-30', '2025-06-30 20:00:00'),
    (3, 'sanctions', 42, '2025-06-30', '2025-06-30 20:00:00'),
    (4, 'pertes', 18700, '2026-03-15', '2026-03-15 21:00:00'),
    (5, 'deplacements', 430000, '2026-03-15', '2026-03-15 21:00:00'),
    (6, 'sanctions', 58, '2026-03-15', '2026-03-15 21:00:00');

INSERT INTO article_favorites (id, user_id, article_id, created_at) VALUES
    (1, 1, 4, '2026-03-16 19:00:00'),
    (2, 1, 10, '2026-03-16 19:02:00'),
    (3, 2, 5, '2026-03-16 19:03:00'),
    (4, 3, 8, '2026-03-16 19:04:00');

INSERT INTO notifications (id, user_id, type, message, is_read, created_at) VALUES
    (1, 1, 'article', 'Nouveau dossier strategique disponible.', 0, '2026-03-16 19:10:00'),
    (2, 1, 'commentaire', 'Une reponse a ete publiee sous votre commentaire.', 0, '2026-03-16 19:12:00'),
    (3, 2, 'debat', 'Nouveau debat: doctrine energetique europeenne.', 1, '2026-03-16 19:15:00'),
    (4, 3, 'article', 'Mise a jour humanitaire de la redaction.', 0, '2026-03-16 19:20:00');

INSERT INTO analytics (id, page, user_id, duration, created_at) VALUES
    (1, '/articles', 1, 145, '2026-03-16 19:30:00'),
    (2, '/article-4-1.html', 1, 322, '2026-03-16 19:34:00'),
    (3, '/debats', 2, 96, '2026-03-16 19:40:00'),
    (4, '/abonnes', 1, 420, '2026-03-16 19:45:00'),
    (5, '/archives', NULL, 88, '2026-03-16 19:47:00');

INSERT INTO security_logs (id, ip, action, status, created_at) VALUES
    (1, '127.0.0.1', 'admin_login', 'success', '2026-03-16 20:00:00'),
    (2, '127.0.0.1', 'subscriber_login', 'success', '2026-03-16 20:02:00'),
    (3, '203.0.113.18', 'admin_login', 'failed', '2026-03-16 20:04:00'),
    (4, '203.0.113.18', 'admin_login', 'blocked', '2026-03-16 20:05:00');

INSERT INTO seo_analysis (id, article_id, title_score, keyword_score, readability_score, created_at, updated_at) VALUES
    (1, 1, 82, 76, 71, '2026-03-16 20:10:00', '2026-03-16 20:10:00'),
    (2, 4, 88, 81, 74, '2026-03-16 20:11:00', '2026-03-16 20:11:00'),
    (3, 10, 85, 79, 77, '2026-03-16 20:12:00', '2026-03-16 20:12:00');

INSERT INTO cache (id, key_name, content, created_at, expires_at) VALUES
    (1, 'api.events.default', '{"ok":true,"data":[]}', '2026-03-16 20:20:00', '2026-03-16 20:30:00'),
    (2, 'api.timeline.default', '{"ok":true,"data":[]}', '2026-03-16 20:21:00', '2026-03-16 20:31:00');

INSERT INTO article_comment_replies (id, comment_id, subscriber_id, author_name, content, status, created_at) VALUES
    (1, 1, 3, 'Leila Haddad', 'Merci pour votre retour, la partie diplomatique sera enrichie.', 'approved', '2026-03-16 20:40:00');

INSERT INTO debate_comment_replies (id, comment_id, subscriber_id, author_name, content, status, created_at) VALUES
    (1, 1, 2, 'Youssef Karim', 'D accord sur l approche progressive, avec des garanties energetiques.', 'approved', '2026-03-16 20:45:00');

INSERT INTO comment_votes (id, comment_type, comment_id, subscriber_id, vote, created_at) VALUES
    (1, 'article', 1, 1, 1, '2026-03-16 20:50:00'),
    (2, 'article', 1, 2, 1, '2026-03-16 20:51:00'),
    (3, 'debat', 1, 3, -1, '2026-03-16 20:52:00');

UPDATE articles
SET content = CONCAT(
    content,
    '<h3>Lecture complementaire</h3>',
    '<p>Cette analyse est mise a jour pour integrer les dimensions diplomatiques, economiques et humanitaires qui influencent la suite du conflit.</p>',
    '<p>La redaction recoupe plusieurs angles afin de proposer une lecture plus robuste, utile aux etudiants, professionnels et lecteurs non specialistes.</p>'
)
WHERE id BETWEEN 1 AND 14;
