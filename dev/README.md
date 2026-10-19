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
├── package.json, vite.config.js   # serveur Vite (dev) et build (prod)
└── web/app/
    ├── plugins/wp-carlo/   # = le dépôt parent, monté en direct (rien à copier)
    └── themes/carlo-test/  # thème de test : structure.yml + assets minimaux
```

Le thème `carlo-test` ne définit aucune section : il réutilise celles de `puppets/library` (`text`, `image`, `quote`). Son `structure.yml` couvre les trois cas à tester :

- `default` : liste **dynamique** (le contributeur choisit les blocs) ;
- `landing` : listes **statiques** (blocs figés), sur deux régions ;
- `project` : un type de contenu avec son propre template.

## Tester le plugin

- **Modifier le plugin** : éditer les fichiers de ce dépôt (`inc/`, `templates/`…) et recharger. Aucun redémarrage.
- **Modifier `structure.yml`** : supprimer les JSON concernés dans `web/app/themes/carlo-test/acf/` puis recharger l'admin (ils se régénèrent). Sinon l'ancienne définition persiste.
- **Tester une section à soi** : créer `templates/sections/<section>/base.php` + `base.yml` dans le thème, puis la référencer par `!load` dans `structure.yml`.
- **Assets en développement** (`WP_ENV=development`) : le plugin charge `js/main.js` et `css/global.css` depuis le serveur Vite. Dans l'inspecteur, `<html data-vite="dev">` confirme que ça passe par Vite.
- **Assets en production** : passer `WP_ENV` à `production` dans `.lando.yml`, puis `lando pnpm build` et `lando rebuild -y`. Le plugin lit alors `dist/.vite/manifest.json` (`<html data-vite="build">`). En production, `WP_DEBUG` est faux : l'admin ACF est masquée, comme sur un vrai site.
- **Logs PHP** : `web/app/debug.log`.
- **Autre version de WordPress** : `lando composer require roots/wordpress:6.8.*`.
- **Repartir de zéro** : `lando destroy -y` (supprime la base), puis `lando start`, `lando install`.

## Pourquoi ces choix

- **Bedrock** : le plugin suppose cette structure (`WP_HOME`, `WP_ENV`, thème sous `app/themes/…`). Un WordPress « classique » ne testerait pas les mêmes chemins.
- **Racine Vite = `web/`** : le plugin construit les URLs d'assets à partir du chemin du thème (`app/themes/<thème>/js/main.js`). Avec cette racine, le serveur de dev répond sous ces chemins, `/@vite/client` est à la racine (comme le plugin l'injecte) et les clés du manifest correspondent à ce que le plugin cherche. Aucun contournement n'est nécessaire.
- **Pas de Tailwind** : les blocs de la library s'affichent donc sans leur design. Pour tester le rendu visuel, ajouter Tailwind 4 au thème de test (voir le thème d'un projet réel).
- **`web/.htaccess` versionné** : avec l'arborescence Bedrock, WP-CLI ne sait pas l'écrire seul, et sans lui les URLs propres (`/landing/`) donnent 404.
- **Pas de `.env`** : tout est dans `.lando.yml`. Aucun secret dans le dépôt hors de `auth.json`, ignoré.

## Dépannage

- **Notice `Undefined array key "ancre"` sur les pages d'exemple** : normal tant que les blocs n'ont jamais été remplis dans l'admin. Elle vient de `puppets/library` (qui ne tolère pas une donnée absente), pas du plugin. Remplir le bloc la fait disparaître. La section `quote` va plus loin : vide, elle provoque une erreur fatale. C'est pourquoi elle n'est proposée que dans la liste dynamique de `default` (rendue seulement si on l'ajoute), jamais dans une liste statique.
- **Rien ne s'affiche / le plugin semble inactif** : vérifier que le thème actif est `carlo-test`. Carlo sort silencieusement si le thème n'a pas de `structure.yml`.
- **Composer répond 401** : `auth.json` absent ou licence ACF Pro invalide.
- **Avertissement de certificat dans le navigateur** : faire confiance à l'autorité de certification de Lando (`lando setup` / documentation Lando).
- **`/@vite/client` ou `main.js` en erreur** : `lando logs -s front`.
