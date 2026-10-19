<?php
/**
 * Configuration Bedrock de l'environnement de développement de wp-carlo.
 *
 * Pas de fichier .env : les valeurs (base de données, URLs, ASSET_BASE_URL)
 * viennent des variables d'environnement définies dans .lando.yml.
 */

use Roots\WPConfig\Config;
use function Env\env;

$root_dir = dirname(__DIR__);
$webroot_dir = $root_dir . '/web';

define('WP_ENV', env('WP_ENV') ?: 'production');

$env_config = __DIR__ . '/environments/' . WP_ENV . '.php';

if (file_exists($env_config)) {
    require_once $env_config;
}

// URLs
Config::define('WP_HOME', env('WP_HOME'));
Config::define('WP_SITEURL', env('WP_SITEURL'));

// Chemins
Config::define('CONTENT_DIR', '/app');
Config::define('WP_CONTENT_DIR', $webroot_dir . Config::get('CONTENT_DIR'));
Config::define('WP_CONTENT_URL', Config::get('WP_HOME') . Config::get('CONTENT_DIR'));

// Base de données
Config::define('DB_NAME', env('DB_NAME'));
Config::define('DB_USER', env('DB_USER'));
Config::define('DB_PASSWORD', env('DB_PASSWORD'));
Config::define('DB_HOST', env('DB_HOST') ?: 'localhost');
Config::define('DB_CHARSET', 'utf8mb4');
Config::define('DB_COLLATE', '');
$table_prefix = 'wp_';

// Réglages WordPress
Config::define('AUTOMATIC_UPDATER_DISABLED', true);
Config::define('DISALLOW_FILE_EDIT', true);
Config::define('DISALLOW_FILE_MODS', true);

// Base des assets Vite en développement : le plugin la lit dans $_ENV.
if (env('ASSET_BASE_URL')) {
    $_ENV['ASSET_BASE_URL'] = env('ASSET_BASE_URL');
}

if (!defined('ABSPATH')) {
    define('ABSPATH', $webroot_dir . '/wp/');
}

Config::apply();
