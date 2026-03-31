# Guide Postman Complet - Iran Platform API

## 1) Objectif
Ce guide permet de tester l API interne (`/api/*`) de maniere reproductible:
- endpoints publics
- endpoints prives (session abonne)
- CSRF
- gestion des erreurs
- cache HTTP (ETag/304)

Collection fournie:
- `docs/postman/iran-api.postman_collection.json`

Environment recommande:
- `docs/postman/iran-api.postman_environment.json`

## 2) Pre-requis
1. Application disponible (`http://localhost/iran` ou `http://localhost:8080`).
2. Base importee:
   - `schema.sql`
   - `seed.sql`
   - `indexes.sql`
3. Compte abonne de test disponible:
   - `nadia.premium@iran.local / abonne123`

## 3) Variables Postman a renseigner
- `base_url`
  - XAMPP: `http://localhost/iran`
  - Docker: `http://localhost:8080`
- `csrf_token`
  - Valeur de session a recuperer depuis une page HTML (meta `csrf-token` ou input `csrf_token`).
- `article_id`
  - Exemple: `1`
- `notification_id`
  - Optionnel (laisser vide pour marquer toutes les notifications lues).

## 4) Signification de REPLACE_WITH_SESSION_TOKEN
`REPLACE_WITH_SESSION_TOKEN` est un placeholder de documentation.
Il faut le remplacer par un vrai token CSRF de la session courante.

## 5) Workflow de test recommande (ordre)
1. Configurer `base_url`.
2. Ouvrir une page front (`GET {{base_url}}/compte/login`) et extraire `csrf_token`.
3. Se connecter en abonne (via navigateur, ou via requete form-data Postman sur `/compte/login`).
4. Lancer les endpoints prives:
   - `POST /api/favorite`
   - `GET /api/notifications`
   - `POST /api/notifications/read`
5. Lancer les endpoints publics:
   - `GET /api/events`
   - `GET /api/timeline`
   - `GET /api/stats`
   - `GET /api/articles`
6. Tester les erreurs attendues (401/403/405/422).

## 6) Endpoints et attentes

### GET /api/events
- Query: `type`, `date_from`, `date_to`
- Attendu:
  - HTTP 200
  - JSON `{ ok: true, count, data[] }`
  - header `ETag`

### GET /api/timeline
- Query: `category`, `date_from`, `date_to`
- Attendu: HTTP 200 + `data[]` triee

### GET /api/stats
- Query: `type`, `date_from`, `date_to`
- Attendu: HTTP 200 + `data[]` + `series.labels/pertes/deplacements/sanctions`

### GET /api/articles
- Query: `limit` (1..50), `q`
- Attendu: HTTP 200 + `data[]` + champs `reading_time`

### POST /api/favorite
- Prerequis: session abonne + CSRF
- Body: `csrf_token`, `article_id`
- Attendu:
  - HTTP 200
  - JSON `{ ok: true, favorite: bool, count: number }`
- Erreurs:
  - 401 sans session
  - 403 sans CSRF
  - 422 article invalide

### GET /api/notifications
- Prerequis: session abonne
- Attendu: HTTP 200 + `{ unread_count, data[] }`
- Erreur: 401 sans session

### POST /api/notifications/read
- Prerequis: session abonne + CSRF
- Body: `csrf_token`, `id` (optionnel)
- Attendu: HTTP 200 + `unread_count` mis a jour
- Erreurs: 401 / 403

### POST /api/analytics
- Body: `page`, `duration`
- Attendu: HTTP 200 `{ ok: true }`
- Erreur: 422 payload invalide

## 7) Tests de hardening conseilles dans Postman
- `POST /api/favorite` sans CSRF -> 403
- `POST /api/favorite` sans session -> 401
- `GET /api/notifications` sans session -> 401
- methode invalide (ex: `DELETE /api/events`) -> 405
- payload invalide (`article_id=0`) -> 422

## 8) Test cache HTTP (ETag)
1. Faire `GET /api/events`.
2. Copier la valeur `ETag` de la reponse.
3. Rejouer `GET /api/events` avec header `If-None-Match: <etag>`.
4. Attendu: HTTP `304` si contenu inchange.

## 9) Erreurs frequentes
- `403 Invalid CSRF token`
  - Le token ne correspond pas a la session actuelle.
- `401 Authentication required`
  - Pas de cookie de session abonne.
- `422 Invalid payload`
  - champ manquant/invalide (ex: `article_id`).

## 10) Checklist de recette Postman
- [ ] Variables configurees (`base_url`, `csrf_token`)
- [ ] Endpoints publics OK
- [ ] Endpoints prives OK apres login
- [ ] Erreurs attendues 401/403/405/422 verifiees
- [ ] Test ETag/304 execute
- [ ] Captures ecran des reponses conservees
