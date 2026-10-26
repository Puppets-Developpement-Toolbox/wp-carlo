<?php

/**
 * Migration des sections du POC ACF (blocs acf/carlo-*) vers les blocs natifs carlo/* :
 * wp carlo migrate [--dry-run]. Chaque contenu modifié garde une révision de l'ancienne version.
 */
if (!defined('WP_CLI') || !WP_CLI) {
    return;
}

/**
 * Valeurs d'une section à partir des données d'un bloc ACF, stockées à plat :
 * groupe « quote » → quote_author, répéteur « items » → items (nombre), items_0_title…
 */
function carlo_acf_values($fields, $data, $prefix = '')
{
    $values = [];
    foreach ($fields as $key => $field) {
        $name = $prefix . $key;
        $value = $data[$name] ?? null;
        $value = match ($field['type']) {
            'group' => is_array($value)
                ? carlo_acf_values($field['fields'], $value)
                : carlo_acf_values($field['fields'], $data, "{$name}_"),
            'repeater' => is_array($value)
                ? array_map(fn($row) => carlo_acf_values($field['fields'], $row), array_values($value))
                : array_map(fn($i) => carlo_acf_values($field['fields'], $data, "{$name}_{$i}_"), $value ? range(0, $value - 1) : []),
            'boolean' => $value === null ? null : (bool) $value,
            'image', 'file' => is_numeric($value) ? (int) $value : null,
            'reference' => $value ? array_map('intval', (array) $value) : null,
            // Les blocs du POC ACF n'avaient pas de conteneur.
            'container' => null,
            'number' => is_numeric($value) ? $value + 0 : null,
            default => $value,
        };
        if ($value !== null && $value !== '' && $value !== []) {
            $values[$key] = $value;
        }
    }
    return $values;
}

WP_CLI::add_command('carlo migrate', function ($args, $assoc_args) {
    $sections = [];
    foreach (carlo_sections() as $id => $definition) {
        $sections['acf/carlo-' . carlo_block_slug($id)] = [carlo_block_name($id), carlo_fields(carlo_structure_fields($definition))];
    }

    $convert = function ($blocks) use (&$convert, $sections) {
        foreach ($blocks as &$block) {
            if (isset($sections[$block['blockName']])) {
                [$name, $fields] = $sections[$block['blockName']];
                $block['blockName'] = $name;
                $block['attrs'] = carlo_acf_values($fields, $block['attrs']['data'] ?? []);
            }
            $block['innerBlocks'] = $convert($block['innerBlocks']);
        }
        return $blocks;
    };

    // Sans utilisateur, WordPress filtrerait le HTML des champs wysiwyg.
    kses_remove_filters();

    $post_types = ['page', ...array_keys(carlo_structure('types') ?? [])];
    $count = 0;
    foreach (get_posts(['post_type' => $post_types, 'post_status' => 'any', 'numberposts' => -1]) as $post) {
        if (!str_contains($post->post_content, '<!-- wp:acf/carlo-')) {
            continue;
        }
        $content = serialize_blocks($convert(parse_blocks($post->post_content)));
        if (!isset($assoc_args['dry-run'])) {
            wp_update_post(['ID' => $post->ID, 'post_content' => wp_slash($content)]);
        }
        WP_CLI::log("#{$post->ID} {$post->post_title}");
        $count++;
    }

    WP_CLI::success(isset($assoc_args['dry-run']) ? "{$count} contenu(s) à migrer." : "{$count} contenu(s) migré(s).");
});
