<?php

/**
 * Catalogue des composants (équivalent Storybook), chargé hors production uniquement (cf. carlo.php).
 *
 * - /?carlo-stories : catalogue, avec &id=<élément>&story=<exemple> pour en afficher un ;
 * - /?carlo-stories=frame&id=<élément>&story=<exemple> : rendu seul d'un exemple (iframe du catalogue).
 *
 * Les éléments sont découverts par l'introspection de la library (carlo_get_available_sections() /
 * carlo_get_available_components()). Les exemples sont rédigés dans <variante>.stories.yml, à côté du
 * template, dans un des dossiers de recherche du driver (thème, wp-carlo, library).
 */

use Symfony\Component\Yaml\Yaml;

/**
 * Driver des exemples : une image peut y être une dimension (1200x800), rendue par une image de
 * remplacement locale.
 */
class CarloStoriesDriver extends CarloWpDriver
{
    public function img(string $key, string $default_size, array $source_sizes, $mobile_key, array $mobile_source_sizes, array $imgAttrs)
    {
        $value = is_numeric($key) ? $key : $this->get($key);
        if (is_string($value) && preg_match('/^(\d+)x(\d+)$/', $value, $size)) {
            [, $width, $height] = $size;
            $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}"><rect width="100%" height="100%" fill="#d6d6d6"/><text x="50%" y="50%" fill="#555" font-family="sans-serif" font-size="32" text-anchor="middle" dominant-baseline="middle">{$width} × {$height}</text></svg>
SVG;
            $attrs = '';
            foreach ($imgAttrs as $name => $attr) {
                $attrs .= ' ' . esc_attr($name) . '="' . esc_attr($attr) . '"';
            }
            return '<img src="data:image/svg+xml,' . rawurlencode($svg) . "\" width=\"{$width}\" height=\"{$height}\" alt=\"\"{$attrs}>";
        }
        return parent::img($key, $default_size, $source_sizes, $mobile_key, $mobile_source_sizes, $imgAttrs);
    }
}

/**
 * Éléments du catalogue, groupés (sections, composants) et indexés par id d'introspection.
 */
function carlo_stories_elements()
{
    static $elements;
    if (isset($elements)) {
        return $elements;
    }

    $elements = [];
    foreach (['sections' => carlo_get_available_sections(true), 'components' => carlo_get_available_components(true)] as $group => $items) {
        foreach ($items as $id => $metadata) {
            [$type, $name, $variant] = carlo_explode_id($id);
            $element = [
                'id' => $id,
                'name' => $name,
                'variant' => $variant,
                'title' => carlo_section_title($id, $metadata),
                'fields' => [],
                'stories' => [],
                'stories_file' => "templates/{$type}/{$name}/{$variant}.stories.yml",
                'errors' => [],
            ];
            // Une définition invalide ne doit pas empêcher d'afficher le reste du catalogue.
            try {
                $element['fields'] = carlo_fields(carlo_structure_fields($metadata));
            } catch (Throwable $e) {
                $element['errors'][] = "Champs : {$e->getMessage()}";
            }
            try {
                $element['stories'] = carlo_stories_load($id);
            } catch (Throwable $e) {
                $element['errors'][] = "Exemples : {$e->getMessage()}";
            }
            $elements[$group][$id] = $element;
        }
    }
    return $elements;
}

function carlo_stories_find($id)
{
    foreach (carlo_stories_elements() as $items) {
        if (isset($items[$id])) {
            return $items[$id];
        }
    }
    return null;
}

/**
 * Exemples d'un élément : le premier <variante>.stories.yml trouvé dans les dossiers de recherche.
 * Pas de repli sur base.stories.yml pour une variante : ce seraient les exemples d'un autre rendu.
 */
function carlo_stories_load($id)
{
    [$type, $name, $variant, $namespace] = carlo_explode_id($id);
    $paths = ["{$type}/{$name}/{$variant}.stories.yml"];
    if ($variant === 'base') {
        $paths[] = "{$type}/{$name}.stories.yml";
    }
    foreach (carlo_driver()->getSearchPaths($namespace) as $dir) {
        foreach ($paths as $path) {
            if (file_exists("{$dir}/{$path}")) {
                return array_filter((array) Yaml::parseFile("{$dir}/{$path}"), 'is_array');
            }
        }
    }
    return [];
}

/**
 * Données d'un exemple : mêmes mises en forme que sur le site (carlo_field_values), et sections d'un
 * conteneur décrites par leur _id, comme les blocs enfants (cf. carlo_section_data).
 */
function carlo_stories_data($fields, $values)
{
    foreach ($fields as $key => $field) {
        if ($field['type'] === 'container') {
            $values[$key] = array_map(function ($item) {
                [$type, $name, $variant, $namespace] = carlo_explode_id($item['_id']);
                $fields = carlo_fields(carlo_structure_fields(carlo_structure($type, $name, $variant, $namespace) ?? []));
                return ['_id' => $item['_id']] + carlo_stories_data($fields, $item);
            }, (array) ($values[$key] ?? []));
        }
    }
    // Les valeurs hors structure (composant sans .yml, arguments passés par un parent) sont transmises telles quelles.
    return carlo_field_values($fields, $values) + array_diff_key($values, ['_label' => true]);
}

/**
 * Champs à plat pour le tableau du catalogue : chemin (quote.author, cards.*.title) => champ.
 */
function carlo_stories_field_rows($fields, $path = '')
{
    $rows = [];
    foreach ($fields as $key => $field) {
        $rows[$path . $key] = $field;
        if (isset($field['fields'])) {
            $rows += carlo_stories_field_rows($field['fields'], $path . $key . ($field['type'] === 'repeater' ? '.*.' : '.'));
        }
    }
    return $rows;
}

/**
 * URL du catalogue ou d'un rendu seul.
 */
function carlo_stories_url($args = [])
{
    return add_query_arg(array_map('rawurlencode', $args), home_url('/?carlo-stories'));
}

function carlo_stories_frame($element, $story)
{
    // Driver des exemples, avec les namespaces enregistrés par le thème.
    $driver = new CarloStoriesDriver();
    foreach (carlo_driver()->getNamespaces() as $namespace => $path) {
        $driver->registerNamespace($namespace, $path);
    }
    $driver->register(get_stylesheet_directory() . '/structure.yml');
    carlo_driver($driver);

    show_admin_bar(false);
    include __DIR__ . '/templates/frame.php';
}

// Accès depuis l'admin : Apparence > Catalogue. Une URL complète en slug sert directement de lien.
add_action('admin_menu', function () {
    add_submenu_page('themes.php', 'Catalogue des composants', 'Catalogue', 'edit_theme_options', carlo_stories_url());
});

add_action('template_redirect', function () {
    if (!isset($_GET['carlo-stories'])) {
        return;
    }
    header('X-Robots-Tag: noindex, nofollow');

    // Seuls les éléments découverts peuvent être rendus : pas de template arbitraire depuis l'URL.
    $element = carlo_stories_find(wp_unslash($_GET['id'] ?? ''));
    $story_key = wp_unslash($_GET['story'] ?? '');

    if ($_GET['carlo-stories'] === 'frame') {
        if (!$element) {
            status_header(404);
            exit('Élément inconnu.');
        }
        // Sans exemple demandé (ou inconnu) : le premier, comme dans le catalogue.
        carlo_stories_frame($element, $element['stories'][$story_key] ?? reset($element['stories']) ?: []);
    } else {
        include __DIR__ . '/templates/catalogue.php';
    }
    exit;
});
