#!/usr/bin/env bash
# Installe WordPress dans l'environnement Lando et prépare un jeu de test pour Carlo.
# Lancé par `lando install`. Idempotent : on peut le relancer sans dupliquer le contenu.
set -euo pipefail

if ! wp core is-installed 2>/dev/null; then
  wp core install \
    --url="$WP_HOME" \
    --title="Carlo dev" \
    --admin_user=admin \
    --admin_password=admin \
    --admin_email=admin@example.test \
    --skip-email
fi

# Le thème d'abord : Carlo ne se charge que si le thème actif a un structure.yml.
wp theme activate carlo-test
wp plugin activate advanced-custom-fields-pro wp-carlo
wp rewrite structure '/%postname%/'

# Crée le contenu s'il n'existe pas, et affiche son ID.
# Usage : ensure_post <type> <slug> <titre> [template de page]
ensure_post() {
  local type=$1 slug=$2 title=$3 template=${4:-} id
  id=$(wp post list --post_type="$type" --name="$slug" --post_status=any --field=ID)
  if [ -z "$id" ]; then
    id=$(wp post create --porcelain \
      --post_type="$type" --post_name="$slug" --post_title="$title" --post_status=publish \
      ${template:+--meta_input="{\"_wp_page_template\":\"$template\"}"})
  fi
  echo "$id"
}

home_id=$(ensure_post page accueil "Accueil" default)
ensure_post page landing "Landing" landing > /dev/null
ensure_post project projet-test "Projet de test" > /dev/null

wp option update show_on_front page
wp option update page_on_front "$home_id"

echo
echo "Prêt : $WP_HOME  (admin / admin, wp-admin : $WP_HOME/wp/wp-admin)"
