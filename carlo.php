<?php
/**
 * Plugin Name: Carlo
 * Plugin URI: https://puppets.fr
 * Description: Carlo est le thème de base développé par Puppets développement
 * Version: 5.0
 * Author: Puppets
 * Author URI: https://puppets.fr
 * Requires at least: 6.1
 * Tested up to: 6.1
 * Requires PHP: 8.0
 * Text Domain: carlo
 * Domain Path: /languages
 */

// Sécurité : bloque l'accès direct au fichier
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$theme_structure = get_stylesheet_directory() . "/structure.yml";
if(!file_exists($theme_structure)) {
  // you are not using a Carlo compatible theme
  return;
}


// Charge tous les fichiers PHP du dossier /inc du PLUGIN
foreach ( glob( __DIR__ . '/inc/*.php' ) as $file ) {
    include_once $file;
}

// Catalogue des composants (/?carlo-stories) : public, donc jamais chargé en production.
if ( ( defined( 'WP_ENV' ) ? WP_ENV : wp_get_environment_type() ) !== 'production' ) {
    include_once __DIR__ . '/stories/stories.php';
}
