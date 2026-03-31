# Plan De Tests Ultra-Complet (FO + BO + API + DB)

## 0. Objectif
Ce document sert de recette exhaustive pour valider:
- fonctionnalites historiques + nouvelles fonctionnalites
- logique metier
- securite
- rewriting
- SEO
- performance et cache
- coherence base de donnees

## 1. Preparation environnement
- [ ] Apache actif avec `mod_rewrite`, `mod_headers`, `mod_expires`, `mod_deflate`.
- [ ] PHP 8.2+ avec extensions: `pdo_mysql`, `mbstring`, `json`.
- [ ] MySQL/MariaDB actif.
- [ ] Base chargee dans cet ordre:
  - `SOURCE database/schema.sql;`
  - `SOURCE database/seed.sql;`
  - `SOURCE database/indexes.sql;`
- [ ] Relancer `SOURCE database/indexes.sql;` une 2e fois (doit rester OK).
- [ ] URL cible definie:
  - XAMPP: `http://localhost/iran`
  - Docker: `http://localhost:8080`

## 2. Verification SQL structure
Executer et valider:
- [ ] `SHOW TABLES;` -> tables metier presentes.
- [ ] `SHOW TABLES LIKE 'events';`
- [ ] `SHOW TABLES LIKE 'timeline_events';`
- [ ] `SHOW TABLES LIKE 'stats';`
- [ ] `SHOW TABLES LIKE 'article_favorites';`
- [ ] `SHOW TABLES LIKE 'notifications';`
- [ ] `SHOW TABLES LIKE 'analytics';`
- [ ] `SHOW TABLES LIKE 'security_logs';`
- [ ] `SHOW TABLES LIKE 'seo_analysis';`
- [ ] `SHOW TABLES LIKE 'cache';`

## 3. Verification SQL indexation
- [ ] `SHOW INDEX FROM articles;` -> index metier + optimisation presentes.
- [ ] `SHOW INDEX FROM subscribers;`
- [ ] `SHOW INDEX FROM events;` -> index `type/date`, `city`, `geo`, `event_date`.
- [ ] `SHOW INDEX FROM timeline_events;`
- [ ] `SHOW INDEX FROM stats;`
- [ ] `SHOW INDEX FROM security_logs;`
- [ ] `SHOW INDEX FROM cache;`

## 4. Integrite referentielle
- [ ] Insertion commentaire avec `article_id` inexistant -> echec FK.
- [ ] Suppression article -> `article_tags` supprimes (cascade).
- [ ] Suppression subscriber -> refs commentaires/avis replies en `NULL` (set null) selon tables.

## 5. Smoke tests HTTP globaux
- [ ] `GET /` -> 200.
- [ ] `GET /articles` -> 200.
- [ ] `GET /admin/login` -> 200.
- [ ] `GET /robots.txt` -> 200.
- [ ] `GET /sitemap.xml` -> 200 XML.
- [ ] `GET /route-inexistante` -> 404 personnalisee.

## 6. Rewriting et hardening URL
- [ ] URL article SEO: `/article-1-1.html` -> 200.
- [ ] URL categorie SEO: `/categorie-1-1.html` -> 200.
- [ ] URL debat slug: `/debat/faut-il-prioriser-un-cessez-le-feu-partiel` -> 200.
- [ ] Path traversal: `/../../config/config.php` -> 403.
- [ ] Encoded traversal `%2e%2e` -> bloque.
- [ ] Methodes `TRACE`/`TRACK` -> refusees.

## 7. FrontOffice navigation
- [ ] Header/menus desktop/mobile sans erreur JS.
- [ ] Dropdowns Rubriques + Communaute fonctionnent clavier/souris.
- [ ] Footer: liens vers pages FO + sitemap + robots.
- [ ] Pages FO sans erreur:
  - `/`
  - `/articles`
  - `/nouveautes`
  - `/debats`
  - `/journaux`
  - `/archives`
  - `/contact`
  - `/carte`
  - `/timeline`
  - `/statistiques`

## 8. Front articles/categories
- [ ] Pagination articles (`?page=`) OK.
- [ ] Recherche `q` OK.
- [ ] Detail article incremente `views`.
- [ ] Commentaire article valide -> `pending`.
- [ ] Commentaire invalide -> message erreur.
- [ ] Votes commentaire (+1/-1) persistants.
- [ ] Reponses commentaires creees et affichees.
- [ ] Favori article: toggle + compteur.
- [ ] TTS bouton "Ecouter" fonctionnel.

## 9. Front compte abonne
- [ ] Register valide -> creation compte + session.
- [ ] Register email duplique -> erreur.
- [ ] Login valide -> profil.
- [ ] Login invalide -> erreur.
- [ ] Profil update -> persistance DB.
- [ ] Change password -> ancien mdp requis.
- [ ] Delete account -> confirmation `SUPPRIMER` obligatoire.
- [ ] Logout -> session fermee.

## 10. Front premium et notifications
- [ ] Abonne free sur `/abonnes` -> refuse.
- [ ] Premium actif sur `/abonnes` -> autorise.
- [ ] `GET /api/notifications` renvoie unread_count en session.
- [ ] "Tout marquer comme lu" met a jour unread_count.

## 11. Front carte interactive (critique)
- [ ] Carte chargee (Leaflet) sans erreur console.
- [ ] `GET /api/events` alimente markers.
- [ ] Filtres type/date fonctionnels.
- [ ] Clustering visible.
- [ ] Heatmap activable.
- [ ] Popup contient titre/type/ville/date/description.
- [ ] Coordonnees invalides event ne cassent pas la carte.

## 12. Front timeline interactive
- [ ] Scroll horizontal fluide (wheel + clavier).
- [ ] Filtres categorie/date OK.
- [ ] `GET /api/timeline` coherent.
- [ ] Cartes timeline animees/apparition correcte.

## 13. Front stats dynamiques
- [ ] Chart.js charge sans erreur.
- [ ] `GET /api/stats` retourne `series` coherent.
- [ ] Filtres date/type synchronisent tableau + graphe.

## 14. BackOffice auth et ACL
- [ ] Login admin `admin/admin123` OK.
- [ ] Logout admin OK.
- [ ] Route BO sans session -> redirection login.
- [ ] Meta robots `noindex,nofollow` present en BO.
- [ ] Brute force/rate limit actif apres echecs repetes.

## 15. BO dashboard et analytics
- [ ] KPI visibles.
- [ ] Filtres q/status/categorie/date/sort OK.
- [ ] Table dashboard coherent (vues, commentaires, partages).
- [ ] Bloc analytics (top pages, trafic) present.
- [ ] Bloc SEO faible present.

## 16. BO CRUD editoriaux
- [ ] Articles: create/edit/toggle/delete.
- [ ] Categories: create/edit/delete (avec protection si references).
- [ ] Admin users: create/delete/changement mdp.

## 17. BO gestion abonnes
- [ ] Liste abonnes + pagination + filtres.
- [ ] Edition profil (points, plan, active, newsletter).
- [ ] Changement mdp abonne.
- [ ] Suppression abonne.

## 18. BO communaute
- [ ] Moderation commentaires articles.
- [ ] Moderation commentaires debats.
- [ ] Moderation avis.
- [ ] Gestion messages contact.
- [ ] Journaux: create/delete.
- [ ] Debats: create/delete.

## 19. BO data modules
- [ ] Events BO: create/edit/delete.
- [ ] Map-picker BO: clic carte met a jour lat/lng.
- [ ] Timeline BO: create/edit/delete.
- [ ] Stats BO: upsert/edit/delete.
- [ ] Security BO: logs + IP suspectes + nettoyage cache expire.

## 20. Reports CSV (Excel)
- [ ] Export datasets: articles/subscribers/contacts/comments/reviews/events/timeline/stats/analytics/security/favorites/notifications.
- [ ] Fichiers export ouvrables dans Excel (UTF-8 BOM).
- [ ] Import subscribers accepte `;`, `,`, BOM UTF-8.
- [ ] Import refuse extension non CSV.
- [ ] Import refuse colonnes minimales manquantes.
- [ ] Import refuse fichiers > 5MB.
- [ ] `data_exchange_logs` alimente apres import/export.

## 21. API publique
- [ ] `GET /api/events` 200 + JSON valide.
- [ ] `GET /api/timeline` 200 + JSON valide.
- [ ] `GET /api/stats` 200 + JSON valide.
- [ ] `GET /api/articles` 200 + JSON valide.
- [ ] Filtre invalide ne provoque pas 500.
- [ ] Methodes interdites (ex POST sur endpoint GET) -> 405.

## 22. API privee + CSRF
- [ ] `POST /api/favorite` sans session -> 401.
- [ ] `POST /api/favorite` sans CSRF -> 403.
- [ ] `POST /api/favorite` avec CSRF valide -> 200.
- [ ] `GET /api/notifications` sans session -> 401.
- [ ] `POST /api/notifications/read` sans CSRF -> 403.
- [ ] `POST /api/notifications/read` CSRF valide -> 200.

## 23. Cache HTTP et perf API
- [ ] `GET /api/events` retourne `ETag`.
- [ ] Requete avec `If-None-Match` correspondant -> 304.
- [ ] `GET /api/articles` expose `Cache-Control: public, max-age=...`.
- [ ] Endpoints prives exposent `Cache-Control: no-store...`.
- [ ] `GET /admin/login` non cache.

## 24. Securite applicative
- [ ] SQLi login (`' OR 1=1 --`) ne bypass pas auth.
- [ ] XSS commentaire/contact/avis non execute au rendu.
- [ ] CSRF protection effective sur POST sensibles.
- [ ] Session admin/subscriber isolees.
- [ ] Fichiers sensibles (`.env`, `.sql`) inaccessibles via HTTP.

## 25. SEO technique
- [ ] 1 seul `h1` par page FO.
- [ ] `title` unique/coherent par page.
- [ ] `meta description` presente.
- [ ] `canonical` present.
- [ ] Open Graph present.
- [ ] JSON-LD present home + detail article.
- [ ] `robots.txt` accessible.
- [ ] `sitemap.xml` accessible et coherent.

## 26. Accessibilite
- [ ] Navigation clavier complete (menus + formulaires).
- [ ] Focus visible.
- [ ] Labels associes aux champs.
- [ ] Contrastes acceptables FO/BO.

## 27. Responsive et fluidite UI
- [ ] Mobile <= 768px: menus/formulaires/cartes utilisables.
- [ ] Desktop >= 1280px: layout stable.
- [ ] Pas de blocage UI pendant filtres map/timeline/stats.
- [ ] Aucun JS bloquant evident en console.

## 28. Tests de charge (optionnel mais recommande)
- [ ] 100 requetes `GET /api/events` sans erreurs 5xx.
- [ ] 100 requetes `GET /articles` sans erreurs 5xx.
- [ ] Temps moyen stable sous charge legere.

## 29. Coherence cross-modules
- [ ] Publication article -> seo_analysis mis a jour.
- [ ] Publication article -> notification(s) creees.
- [ ] Vote/reponse commentaire -> notifications + score cohérents.
- [ ] Suppression entite -> coherences FK preservees.

## 30. Non-regression ancienne + nouvelle fonctionnalite
- [ ] Anciennes pages historiques OK.
- [ ] Nouvelles pages data (`/carte`, `/timeline`, `/statistiques`) OK.
- [ ] Ancien flux editorial non casse par nouveaux modules.
- [ ] Ancien BO non casse par nouveaux menus/data.

## 31. Recette Postman (obligatoire)
- [ ] Importer collection: `iran-api.postman_collection.json`.
- [ ] Importer environment: `iran-api.postman_environment.json`.
- [ ] Renseigner `base_url`.
- [ ] Remplacer `REPLACE_WITH_SESSION_TOKEN` par token reel.
- [ ] Tester endpoints publics puis prives.
- [ ] Capturer 401/403/422 attendus.

## 32. Preuves a livrer
- [ ] Captures FO desktop/mobile.
- [ ] Captures BO principales pages.
- [ ] Captures Postman (success + erreurs attendues).
- [ ] Resultats SQL (SHOW TABLES / SHOW INDEX / COUNT).
- [ ] Captures headers cache/rewrite (ETag, no-store, 304).
