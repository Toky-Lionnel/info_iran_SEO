# Projet Iran - Mini Projet Web Design 2026

Site d informations sur la guerre en Iran (2024-2026) avec :
- FrontOffice public (articles, categories, detail)
- BackOffice administration (login + CRUD)
- Espace communaute (commentaires, debats, journaux epingles, contact)
- Comptes abonnes (profil complet editable, premium bloque/debloque selon statut)
- Gestion abonnes BO (filtres, recherche, points, statut, suppression)
- Exports/imports Excel BO (CSV compatible) avec journalisation
- Import abonnes CSV robuste (separateur `;` ou `,`, support BOM UTF-8)
- Carte interactive Leaflet + timeline interactive + stats dynamiques
- API REST interne (`/api/events`, `/api/timeline`, `/api/stats`, `/api/articles`, `/api/favorite`)
- Favoris "lire plus tard", notifications web, TTS article, analytics BO
- Journal securite + blocage IP suspecte + SEO auto + cache API/HTML
- API publique optimisee: `ETag` + `Cache-Control` court (304 possible)
- API privee et BO: headers anti-cache stricts (`no-store`)
- Base de donnees MySQL (schema + seed + indexes)
- SEO technique (title, meta, canonical, JSON-LD, robots, sitemap)
- Rewriting Apache via `.htaccess`

## Stack

- PHP 8.2+ (sans framework)
- MySQL 8+ (PDO prepared statements)
- Apache `mod_rewrite`
- XAMPP (mode recommande par les consignes)

## Installation XAMPP (recommandee)

1. Copier le dossier `iran` dans `htdocs`.
2. Creer la base avec MariaDB/MySQL en utilisant `SOURCE` (evite les erreurs de frappe en copier-coller) :
   - Ouvrir MariaDB, puis executer:
   - `SOURCE database/schema.sql;`
   - `SOURCE database/seed.sql;`
   - `SOURCE database/indexes.sql;`
3. Verifier Apache + MySQL demarres.
4. Ouvrir : `http://localhost/iran/`

## Backoffice

- URL: `http://localhost/iran/admin/login`
- Username: `admin`
- Password: `admin123`
- Espace communaute: `http://localhost/iran/admin/community`
- Carte events BO: `http://localhost/iran/admin/events`
- Timeline BO: `http://localhost/iran/admin/timeline`
- Stats BO: `http://localhost/iran/admin/stats`
- Securite BO: `http://localhost/iran/admin/security`
- Gestion abonnes: `http://localhost/iran/admin/subscribers`
- Reports Excel: `http://localhost/iran/admin/reports`

## Comptes abonnes (FO)

- Connexion: `http://localhost/iran/compte/login`
- Profil: `http://localhost/iran/compte/profil`
- Compte premium de demo:
  - Email: `nadia.premium@iran.local`
  - Password: `abonne123`
- Compte standard de demo:
  - Email: `youssef.free@iran.local`
  - Password: `lecteur123`

## Fichiers importants

- Entree app : `index.php`
- Routing : `core/Router.php`
- Config : `config/config.php`
- Rewriting : `.htaccess`
- Sitemap dynamique : `sitemap.php` (servi sur `/sitemap.xml`)
- Plan de tests complet : `TESTS_COMPLETS_SITE.md`
- Guide Postman complet : `docs/postman/POSTMAN_GUIDE_COMPLET.md`
- Environment Postman : `docs/postman/iran-api.postman_environment.json`

## Champs manuels a remplacer

Oui, il y a quelques champs a renseigner manuellement selon ton environnement:

1. `.env` (copie de `.env.example`)
- `APP_BASE_URL` (ex: `http://localhost/iran` en XAMPP, `http://localhost:8080` en Docker)
- `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`

2. Postman (`docs/postman/iran-api.postman_collection.json`)
- `{{base_url}}` : URL du projet (`http://localhost/iran` ou `http://localhost:8080`)
- `{{csrf_token}}` : token CSRF de ta session active

3. Signification de `REPLACE_WITH_SESSION_TOKEN`
- C est un placeholder de demo.
- Tu dois le remplacer par la vraie valeur CSRF de la session en cours.
- Tu peux recuperer ce token dans le HTML d une page (meta `csrf-token`) quand tu es connecte.

## Option Docker (livrable annexe)

Un mode Docker complet est fourni (`Dockerfile` + `docker-compose.yml`) avec variables d environnement deja configurees:
- `APP_BASE_URL=http://localhost:8080`
- `DB_HOST=db`
- `DB_PORT=3307`
- `DB_NAME=iran_war_db`
- `DB_USER=iran`
- `DB_PASS=iran123`

Execution:
1. `docker compose up -d --build`
2. Ouvrir `http://localhost:8080/`

Le mode de reference pour la correction reste XAMPP, mais la base et le routing sont compatibles dans les deux contextes.
