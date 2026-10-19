# Environnement de développement de wp-carlo

Un site WordPress Bedrock jetable (Lando), où le plugin est monté **en direct** depuis ce dépôt : on modifie le code du plugin, on recharge la page, on voit le résultat.

## Prérequis

- Docker et [Lando](https://lando.dev) 3.x.
- Une licence ACF Pro (à demander au responsable du projet). Elle sert uniquement à Composer pour télécharger le plugin ; elle n'est **jamais** versionnée.

## Démarrage

```bash
cd dev
cp auth.json.example auth.json   # puis y renseigner la licence ACF Pro (fichier ignoré par git)
lando start
lando composer install
lando install
```

| Commande | Rôle |
| --- | --- |
| `lando start` | Démarre WordPress (PHP 8.3, MySQL 8), le serveur Vite et le proxy HTTPS. |
| `lando composer install` | Installe WordPress, ACF Pro, `puppets/library` et `idleberg/wordpress-vite-assets` (Bedrock). |
| `lando install` | Installe WordPress, active le thème `carlo-test`, ACF Pro et Carlo, crée des pages d'exemple. Relançable sans risque. |
| `lando browser <script>` | Lance un scénario de test dans Chromium headless (Playwright), ex. `lando browser tests/browser/editor.py`. |

Puis :

- Site : <https://carlo-dev.lndo.site>
- Admin : <https://carlo-dev.lndo.site/wp/wp-admin> (`admin` / `admin`, local uniquement)
- Serveur Vite : <https://front.carlo-dev.lndo.site>

## Ce qui est installé

```
dev/
├── .lando.yml              # services, variables d'environnement, montage du plugin
├── composer.json           # Bedrock : WordPress, ACF Pro, puppets/library
├── config/                 # config Bedrock (application.php, environments/)
├── bin/install.sh          # `lando install`
├── package.json, vite.config.js   # serveur Vite + Tailwind 4 (dev) et build (prod)
├── tests/browser/         # scénarios de test dans un navigateur (`lando browser`)
└── web/app/
    ├── plugins/wp-carlo/   # = le dépôt parent, monté en direct (rien à copier)
    └── themes/carlo-test/  # thème de test : structure.yml + styles de la library
```

Le thème `carlo-test` ne définit aucune section : il réutilise celles de `puppets/library` (`text`, `image`, `quote`). Son `structure.yml` couvre les trois cas à tester :

- `default` : une région, trois sections au choix, **3 blocs au maximum** (`_max`) ;
- `landing` : deux régions, une seule section possible dans chacune ;
- `project` : un type de contenu avec son propre template.

Dans l'éditeur de blocs, chaque région est un bloc verrouillé qui n'accepte que les sections citées dans `structure.yml`. Le modèle se choisit à la création (« Pages > Ajouter : Page templatisée ») et ne change plus ensuite. Les pages créées par `lando install` reçoivent leurs régions à la première ouverture dans l'éditeur.

## Tester le plugin

- **Modifier le plugin** : éditer les fichiers de ce dépôt (`inc/`, `templates/`…) et recharger. Aucun redémarrage.
- **Modifier `structure.yml`** : supprimer les JSON concernés dans `web/app/themes/carlo-test/acf/` puis recharger l'admin (ils se régénèrent). Sinon l'ancienne définition persiste.
- **Tester une section à soi** : créer `templates/sections/<section>/base.php` + `base.yml` dans le thème, puis la référencer par `!load` dans `structure.yml`.
- **Assets en développement** (`WP_ENV=development`) : le plugin charge `js/main.js` et `css/global.css` depuis le serveur Vite, y compris dans l'aperçu de l'éditeur de blocs. Dans l'inspecteur, `<html data-vite="dev">` confirme que ça passe par Vite.
- **Assets en production** : passer `WP_ENV` à `production` dans `.lando.yml`, puis `lando pnpm build` et `lando rebuild -y`. Le plugin lit alors `dist/.vite/manifest.json` (`<html data-vite="build">`). En production, `WP_DEBUG` est faux : l'admin ACF est masquée, comme sur un vrai site.
- **Tester dans un navigateur** : `lando browser tests/browser/editor.py` parcourt l'éditeur de blocs (régions, sections autorisées, `_max`, saisie, rendu front), affiche ✓/✗ et range ses captures dans `tests/browser/shots/`. Les pages qu'il crée sont supprimées à la fin. Pour écrire un autre scénario, partir de `tests/browser/wp.py` (connexion à l'admin, captures).
- **Logs PHP** : `web/app/debug.log`.
- **Autre version de WordPress** : `lando composer require roots/wordpress:6.8.*`.
- **Repartir de zéro** : `lando destroy -y` (supprime la base), puis `lando start`, `lando install`.

## Pourquoi ces choix

- **Bedrock** : le plugin suppose cette structure (`WP_HOME`, `WP_ENV`, thème sous `app/themes/…`). Un WordPress « classique » ne testerait pas les mêmes chemins.
- **Racine Vite = `web/`** : le plugin construit les URLs d'assets à partir du chemin du thème (`app/themes/<thème>/js/main.js`). Avec cette racine, le serveur de dev répond sous ces chemins, `/@vite/client` est à la racine (comme le plugin l'injecte) et les clés du manifest correspondent à ce que le plugin cherche. Aucun contournement n'est nécessaire.
- **Tailwind 4 via la library** : le thème importe `library.css` de `puppets/library`, comme un projet réel. Les sections ont donc leur design, sur le site comme dans l'aperçu de l'éditeur.
- **`web/.htaccess` versionné** : avec l'arborescence Bedrock, WP-CLI ne sait pas l'écrire seul, et sans lui les URLs propres (`/landing/`) donnent 404.
- **Pas de `.env`** : tout est dans `.lando.yml`. Aucun secret dans le dépôt hors de `auth.json`, ignoré.

## Dépannage

- **Notice `Undefined array key "ancre"`** : elle vient de `puppets/library`, qui ne tolère pas une donnée absente (la section `quote` provoque même une erreur fatale). Le plugin n'affiche donc pas une section entièrement vide (un message la remplace dans l'éditeur), mais une section remplie à moitié peut encore produire la notice.
- **Site ou aperçu sans style, ressources en 502** : le serveur Vite ne tourne pas (`lando logs -s front`). Après une modification de `package.json`, `lando rebuild -y` réinstalle les dépendances.
- **Police Poppins absente (texte en police système)** : `webfonts.css` de `puppets/library` pointe vers `src/css/webfonts/`, alors que les polices sont dans `src/webfonts/`. À corriger dans la library.
- **Rien ne s'affiche / le plugin semble inactif** : vérifier que le thème actif est `carlo-test`. Carlo sort silencieusement si le thème n'a pas de `structure.yml`.
- **Composer répond 401** : `auth.json` absent ou licence ACF Pro invalide.
- **Avertissement de certificat dans le navigateur** : faire confiance à l'autorité de certification de Lando (`lando setup` / documentation Lando).
- **`/@vite/client` ou `main.js` en erreur** : `lando logs -s front`.
