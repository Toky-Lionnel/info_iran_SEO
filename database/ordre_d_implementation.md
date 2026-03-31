Ordre d implementation (obligatoire) :

1. `schema.sql`
2. `seed.sql`
3. `indexes.sql`

Pourquoi :
- `schema.sql` cree la base, les tables et les cles etrangeres.
- `seed.sql` insere les donnees (il depend des tables deja creees).
- `indexes.sql` ajoute les index d optimisation apres insertion des donnees.

Execution conseillee (MariaDB/MySQL) :
- `SOURCE database/schema.sql;`
- `SOURCE database/seed.sql;`
- `SOURCE database/indexes.sql;`

Erreurs frequentes :
- `ERROR 1064 ... near 'REATE TABLE'` : faute de frappe (`CREATE` mal tape).
- `ERROR 1005 ... errno: 150` : une table parente manque (ex: `categories`), donc FK invalide.

Note Docker :
- `init.sql` embarque deja schema + seed + indexes dans le bon ordre.
