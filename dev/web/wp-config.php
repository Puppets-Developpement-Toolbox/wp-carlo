<?php
/**
 * Bedrock : la configuration réelle vit à la racine du projet.
 * L'autoloader doit être chargé avant config/application.php.
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/application.php';

require_once ABSPATH . 'wp-settings.php';
