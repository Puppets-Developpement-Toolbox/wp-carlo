<?php
/**
 * Plugin Name: Carlo
 * Plugin URI: https://puppets.fr
 * Description: Carlo est le thème de base développé par Puppets développement
 * Version: 3.0
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

// Charge tous les fichiers PHP du dossier /inc du PLUGIN
foreach ( glob( __DIR__ . '/inc/*.php' ) as $file ) {
    include_once $file;
}
