<?php

/**
 * remove all attribute's key prefixed by _
 */
function carlo_structure_fields($definition)
{
    $fields = array_filter(
        $definition,
        function ($k) {
            return substr($k, 0, 1) !== "_";
        },
        ARRAY_FILTER_USE_KEY
    );
    return $fields;
}

// Champs saisis directement dans l'aperçu de l'éditeur (cf. carlo_field_values).
const CARLO_INLINE_TYPES = ['text_short', 'text', 'wysiwyg'];

/**
 * Champ d'une section, sous une forme commune au PHP (attributs, rendu) et à l'éditeur
 * (window.carloSections) : type, label, aide, options, sous-champs.
 */
function carlo_field($key, $definition)
{
    if (empty($definition)) {
        throw new Exception("le champ {$key} n'a pas de définition");
    }

    $type = is_string($definition)
        ? $definition
        : $definition['_type'] ?? (isset($definition['_id']) ? 'group' : 'container');
    $definition = (array) $definition;

    $type = match (true) {
        $type === 'select' => 'choices', // @deprecated
        $type === 'image' && !empty($definition['_multi']) => 'gallery',
        default => $type,
    };

    $field = [
        'type' => $type,
        'label' => html_entity_decode($definition['_label'] ?? str_replace('_', ' ', ucfirst($key))),
    ];

    if (isset($definition['_help'])) {
        $field['help'] = html_entity_decode($definition['_help']);
    }

    if ($type === 'group') {
        $field['fields'] = carlo_fields(carlo_structure_fields($definition));
    }

    if ($type === 'repeater') {
        $field['fields'] = carlo_fields($definition['_repeat']);
        $field['min'] = $definition['_min'] ?? $definition['_times'] ?? 0;
        $field['max'] = $definition['_max'] ?? $definition['_times'] ?? null;
        $field['add_button'] = $definition['_add_button'] ?? 'Ajouter un élément';
    }

    if ($type === 'choices') {
        $field['choices'] = $definition['_choices'];
        $field['multi'] = !empty($definition['_multi']);
    }

    if ($type === 'reference') {
        $field['post_types'] = (array) $definition['_ref_type'];
        $field['max'] = $definition['_max'] ?? null;
    }

    if ($type === 'container') {
        $field['items'] = array_values(array_map(fn($item) => carlo_block_name($item['_id']), carlo_container_items($definition)));
    }

    return $field;
}

function carlo_fields($definitions)
{
    $fields = [];
    foreach ($definitions as $key => $definition) {
        $fields[$key] = carlo_field($key, $definition);
    }
    return $fields;
}

/**
 * Sections que peut contenir un champ conteneur (liste de définitions ayant un _id).
 */
function carlo_container_items($definition)
{
    if (!is_array($definition) || isset($definition['_id']) || ($definition['_type'] ?? 'container') !== 'container') {
        return [];
    }
    return array_filter($definition['_items'] ?? $definition, fn($item) => is_array($item) && isset($item['_id']));
}

/**
 * Schéma des attributs du bloc : un attribut par champ de premier niveau. Le conteneur n'en est
 * pas un : ses sections sont les blocs enfants.
 */
function carlo_block_attributes($fields)
{
    $fields = array_filter($fields, fn($field) => $field['type'] !== 'container');
    return array_map(fn($field) => ['type' => match ($field['type']) {
        'text_short', 'text', 'wysiwyg', 'url', 'color', 'date', 'datetime', 'embed' => 'string',
        'boolean' => 'boolean',
        'number' => 'number',
        'image', 'file' => 'integer',
        'group' => 'object',
        'repeater', 'gallery', 'reference' => 'array',
        'choices' => $field['multi'] ? 'array' : 'string',
        // Types pas encore pris en charge par l'éditeur : valeur libre.
        default => ['string', 'number', 'boolean', 'array', 'object', 'null'],
    }], $fields);
}

/**
 * Données passées au template : chaque champ est présent (la library ne tolère pas les données
 * absentes) et mis en forme comme le faisait ACF.
 *
 * Avec $path (aperçu de l'éditeur), les textes sont remplacés par un marqueur {{carlo:chemin}}
 * que l'éditeur rend saisissable, et le conteneur par l'emplacement des blocs enfants : le rendu
 * ne dépend pas des textes, il n'est pas recalculé pendant la frappe.
 */
function carlo_field_values($fields, $values, $path = null)
{
    $data = [];
    foreach ($fields as $key => $field) {
        $value = $values[$key] ?? null;
        $marker = $path === null ? null : "{$path}{$key}";

        if ($marker !== null && in_array($field['type'], CARLO_INLINE_TYPES, true)) {
            $data[$key] = "{{carlo:{$marker}}}";
            continue;
        }

        $data[$key] = match ($field['type']) {
            'group' => carlo_field_values($field['fields'], (array) $value, $marker === null ? null : "{$marker}."),
            'repeater' => array_map(
                fn($i) => carlo_field_values($field['fields'], (array) ($value[$i] ?? []), $marker === null ? null : "{$marker}.{$i}."),
                // Aperçu : les éléments obligatoires (_min, _times) sont affichés, pour être remplis.
                $marker === null || count((array) $value) >= $field['min'] ? array_keys((array) $value) : range(0, $field['min'] - 1)
            ),
            'container' => $marker === null ? (array) $value : [['_id' => 'carlo:inner']],
            'reference' => array_values(array_filter(
                array_map('get_post', (array) $value),
                fn($post) => $post && $post->post_status === 'publish'
            )),
            'gallery' => (array) $value,
            'boolean' => (bool) $value,
            'text' => nl2br($value ?? ''),
            'wysiwyg' => wpautop($value ?? ''),
            default => $value ?? '',
        };
    }
    return $data;
}

/**
 * Données d'une section à partir de son bloc : attributs, et blocs enfants pour le conteneur.
 */
function carlo_section_data($id, $attributes, $inner_blocks = [], $preview = false)
{
    $fields = carlo_fields(carlo_structure_fields(carlo_sections()[$id]));
    foreach ($fields as $key => $field) {
        if ($field['type'] === 'container' && !$preview) {
            $attributes[$key] = [];
            foreach ($inner_blocks as $inner) {
                $inner_id = carlo_section_id($inner->name);
                $attributes[$key][] = ['_id' => $inner_id] + carlo_section_data($inner_id, $inner->attributes, $inner->inner_blocks);
            }
        }
    }
    return carlo_field_values($fields, $attributes, $preview ? '' : null);
}

/**
 * Nom du bloc d'une section : sections/text:base → carlo/sections-text.
 */
function carlo_block_name($id)
{
    return 'carlo/' . carlo_block_slug($id);
}

function carlo_block_slug($id)
{
    return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(preg_replace('/:base$/', '', $id))), '-');
}

/**
 * _id de la section d'un bloc : carlo/sections-text → sections/text:base.
 */
function carlo_section_id($block_name)
{
    static $ids;
    $ids ??= array_combine(array_map('carlo_block_name', array_keys(carlo_sections())), array_keys(carlo_sections()));
    return $ids[$block_name] ?? null;
}

/**
 * Sections proposées par une région ou par le conteneur d'une autre section, indexées par _id.
 */
function carlo_sections()
{
    static $sections;
    if (isset($sections)) {
        return $sections;
    }

    $sections = [];
    $add = function ($section) use (&$add, &$sections) {
        if (isset($sections[$section['_id']])) {
            return;
        }
        $sections[$section['_id']] = $section;
        foreach (carlo_structure_fields($section) as $definition) {
            array_map($add, carlo_container_items($definition));
        }
    };
    foreach (['page', ...array_keys(carlo_structure('types') ?? [])] as $post_type) {
        foreach (carlo_templates($post_type) as $template) {
            foreach (carlo_structure_fields($template) as $region) {
                array_map($add, carlo_region($region)['sections']);
            }
        }
    }
    return $sections;
}

function carlo_section_title($id, $definition)
{
    [, $name, $variant] = carlo_explode_id($id);
    return $definition['_label'] ?? ucfirst(str_replace('_', ' ', $variant === 'base' ? $name : "{$name} {$variant}"));
}

/**
 * Aperçu d'une section dans l'éditeur (cf. assets/sections.js), et non rendu du site.
 */
function carlo_is_editor_preview()
{
    return wp_is_serving_rest_request() && str_contains($_SERVER['REQUEST_URI'] ?? '', 'block-renderer');
}

/**
 * Un bloc dynamique par section : ses attributs sont les champs de la section, son rendu est
 * le template Carlo de la section.
 */
function carlo_register_section_blocks()
{
    carlo_editor_asset('carlo-sections', 'sections.js', [
        'wp-blocks', 'wp-block-editor', 'wp-components', 'wp-compose', 'wp-element', 'wp-data', 'wp-core-data',
        'wp-api-fetch', 'wp-url', 'wp-escape-html', 'wp-html-entities',
    ]);
    carlo_editor_asset('carlo-sections', 'sections.css');

    foreach (carlo_sections() as $id => $definition) {
        $fields = carlo_fields(carlo_structure_fields($definition));

        register_block_type(carlo_block_name($id), [
            'api_version' => 3,
            'title' => carlo_section_title($id, $definition),
            'category' => 'design',
            // ancestor et non parent : parent l'emporterait sur les allowedBlocks de la région.
            // Une section imbriquée a elle aussi une région pour ancêtre.
            'ancestor' => ['carlo/region'],
            'attributes' => carlo_block_attributes($fields),
            // Les templates Carlo n'appliquent pas de classe CSS saisie dans l'éditeur.
            'supports' => ['html' => false, 'className' => false, 'customClassName' => false],
            'editor_script_handles' => ['carlo-sections'],
            'render_callback' => function ($attributes, $content, $block) use ($id, $fields) {
                $preview = carlo_is_editor_preview();
                $filled = count($block->inner_blocks) > 0;
                array_walk_recursive($attributes, function ($value) use (&$filled) {
                    $filled = $filled || !empty($value);
                });
                // Section pas encore remplie : rien sur le site.
                if ($fields && !$filled && !$preview) {
                    return '';
                }
                ob_start();
                carlo_render($id, carlo_section_data($id, $attributes, $block->inner_blocks, $preview));
                return ob_get_clean();
            },
        ]);
    }
}

add_action('enqueue_block_editor_assets', function () {
    $sections = [];
    foreach (carlo_sections() as $id => $definition) {
        $sections[carlo_block_name($id)] = [
            'title' => carlo_section_title($id, $definition),
            'fields' => (object) carlo_fields(carlo_structure_fields($definition)),
        ];
    }
    wp_add_inline_script('carlo-sections', 'window.carloSections = ' . wp_json_encode((object) $sections) . ';', 'before');
    // Le formulaire est dans le panneau Réglages, hors de l'iframe du contenu.
    wp_enqueue_style('carlo-sections');
    // Réglages TinyMCE du site (dont les filtres tiny_mce_before_init) pour les champs wysiwyg.
    wp_enqueue_editor();
});

// Repère des contenus Carlo dans l'éditeur, pour élargir le panneau Réglages (cf. sections.css).
add_filter('admin_body_class', function ($classes) {
    $screen = get_current_screen();
    return $screen && $screen->is_block_editor() && carlo_post_template(get_post()) ? "{$classes} carlo-editor" : $classes;
});
