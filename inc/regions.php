<?php

/**
 * Enregistre un script ou une feuille de style de assets/, versionné par sa date de modification.
 */
function carlo_editor_asset($handle, $file, $deps = [])
{
    $src = plugins_url("assets/{$file}", dirname(__DIR__) . '/carlo.php');
    $version = filemtime(dirname(__DIR__) . "/assets/{$file}");
    str_ends_with($file, '.js')
        ? wp_register_script($handle, $src, $deps, $version, true)
        : wp_register_style($handle, $src, $deps, $version);
}

/**
 * Bloc « région » : conteneur des sections d'une région, sans champ propre.
 * Il n'enregistre que le nom de la région : les sections autorisées et le maximum sont
 * relus dans structure.yml à chaque ouverture de l'éditeur.
 */
add_action('init', function () {
    carlo_editor_asset('carlo-region', 'region.js', ['wp-blocks', 'wp-block-editor', 'wp-element', 'wp-data']);
    carlo_editor_asset('carlo-region', 'region.css');

    register_block_type('carlo/region', [
        'api_version' => 3,
        'title' => 'Région',
        'category' => 'design',
        'attributes' => ['region' => ['type' => 'string']],
        'supports' => ['inserter' => false, 'html' => false, 'reusable' => false],
        'editor_script_handles' => ['carlo-region'],
        'editor_style_handles' => ['carlo-region'],
        'render_callback' => fn($attributes, $content) => $content,
    ]);
});

add_action('enqueue_block_editor_assets', function () {
    $labels = carlo_structure('regions') ?? [];
    $regions = [];
    foreach (carlo_structure_fields(carlo_post_template(get_post()) ?? []) as $region => $definition) {
        $config = carlo_region($definition);
        $regions[$region] = [
            'label' => $labels[$region] ?? $region,
            'allowedBlocks' => array_map('carlo_block_name', array_keys($config['sections'])),
            'max' => $config['max'],
        ];
    }
    wp_add_inline_script('carlo-region', 'window.carloRegions = ' . wp_json_encode((object) $regions) . ';', 'before');
});
