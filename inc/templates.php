<?php

/**
 * Modèles Carlo d'un type de contenu, indexés par slug ("default" : modèle par défaut).
 */
function carlo_templates($post_type)
{
    if ($post_type === 'page') {
        return carlo_structure('templates') ?? [];
    }
    $type = (carlo_structure('types') ?? [])[$post_type] ?? [];
    return array_filter(
        ['default' => $type['template'] ?? $type['templates']['default'] ?? null]
        + ($type['templates'] ?? [])
    );
}

/**
 * Modèle Carlo d'un contenu. Il est choisi à la création et ne change plus ensuite.
 */
function carlo_post_template($post)
{
    $post = get_post($post);
    if (!$post) {
        return null;
    }
    $slug = (int) get_option('page_for_posts') === $post->ID
        ? 'archive'
        : (get_page_template_slug($post) ?: 'default');
    return carlo_templates($post->post_type)[$slug] ?? null;
}

/**
 * Sections autorisées (indexées par _id) et nombre maximum de blocs d'une région.
 * Une région est toujours dynamique : toutes les sections qu'elle cite sont proposées.
 */
function carlo_region($definition)
{
    $sections = [];
    $max = null;
    foreach (isset($definition['_id']) ? [$definition] : (array) $definition as $item) {
        if (isset($item['_blocs'])) {
            $max = $item['_max'] ?? $max;
            $item = $item['_blocs'];
        }
        foreach (isset($item['_id']) ? [$item] : (array) $item as $section) {
            if (isset($section['_id'])) {
                $sections[$section['_id']] = $section;
            }
        }
    }
    return ['sections' => $sections, 'max' => $max];
}

/**
 * Remet le contenu en conformité avec le modèle : une région par région déclarée, dans
 * l'ordre de structure.yml. Couvre les contenus neufs, ceux créés hors de l'éditeur et les
 * régions ajoutées au modèle après coup. Rien n'est supprimé : les régions retirées du
 * modèle et le contenu hors région restent en fin de contenu.
 */
function carlo_template_content($content, $template)
{
    $found = [];
    $others = [];
    foreach (parse_blocks($content) as $block) {
        $region = $block['blockName'] === 'carlo/region' ? ($block['attrs']['region'] ?? '') : null;
        if ($region !== null && !isset($found[$region])) {
            $found[$region] = $block;
        } elseif ($block['blockName'] || trim($block['innerHTML'])) {
            $others[] = $block;
        }
    }

    $blocks = [];
    foreach (array_keys(carlo_structure_fields($template)) as $region) {
        $blocks[] = $found[$region] ?? [
            'blockName' => 'carlo/region',
            'attrs' => ['region' => $region, 'lock' => ['move' => true, 'remove' => true]],
            'innerBlocks' => [],
            'innerHTML' => '',
            'innerContent' => [],
        ];
        unset($found[$region]);
    }

    return serialize_blocks([...$blocks, ...array_values($found), ...$others]);
}

// Le modèle se choisit à la création, via les entrées « Ajouter : … » du menu
// (post-new.php?carlo_template=…), puis il est figé. Le brouillon reçoit d'emblée ses régions :
// pour un nouveau contenu, l'éditeur part de default_content, pas de l'API REST.
add_action('load-post-new.php', function () {
    add_filter('default_content', function ($content, $post) {
        $slug = sanitize_key(wp_unslash($_GET['carlo_template'] ?? ''));
        if (isset(carlo_templates($post->post_type)[$slug])) {
            update_post_meta($post->ID, '_wp_page_template', $slug);
        }
        $template = carlo_post_template($post);
        return $template ? carlo_template_content($content, $template) : $content;
    }, 10, 2);
});

add_action('admin_menu', function () {
    foreach (['page', ...array_keys(carlo_structure('types') ?? [])] as $post_type) {
        $post_type_object = get_post_type_object($post_type);
        if (!$post_type_object) {
            continue;
        }
        foreach (carlo_templates($post_type) as $slug => $template) {
            if (in_array($slug, ['default', 'archive'], true)) {
                continue;
            }
            add_submenu_page(
                "edit.php?post_type={$post_type}",
                $template['_label'],
                "Ajouter : {$template['_label']}",
                $post_type_object->cap->create_posts,
                "post-new.php?post_type={$post_type}&carlo_template={$slug}"
            );
        }
    }
});

// WordPress revalide le modèle à chaque enregistrement et remet "default" s'il ne le connaît
// pas. On ne lui déclare donc que le modèle du contenu : l'enregistrement passe et tout autre
// modèle est refusé. Sans contenu (édition rapide), aucun : le sélecteur n'y apparaît pas.
add_action('init', function () {
    foreach (['page', ...array_keys(carlo_structure('types') ?? [])] as $post_type) {
        add_filter("theme_{$post_type}_templates", function ($templates, $theme, $post) use ($post_type) {
            $slug = $post ? get_page_template_slug($post) : '';
            $template = carlo_templates($post_type)[$slug] ?? null;
            return $template ? $templates + [$slug => $template['_label']] : $templates;
        }, 10, 3);
    }
});

// Le contenu envoyé à l'éditeur contient toujours les régions du modèle.
add_action('rest_api_init', function () {
    foreach (['page', ...array_keys(carlo_structure('types') ?? [])] as $post_type) {
        add_filter("rest_prepare_{$post_type}", function ($response, $post, $request) {
            $template = carlo_post_template($post);
            if ($template && $request['context'] === 'edit' && isset($response->data['content']['raw'])) {
                $response->data['content']['raw'] = carlo_template_content($response->data['content']['raw'], $template);
            }
            return $response;
        }, 10, 3);
    }
});

// Dans l'éditeur : pas de sélecteur de modèle, et des régions qui ne peuvent être ni ajoutées,
// ni supprimées, ni déplacées, ni déverrouillées. templateLock "all" ne convient pas :
// WordPress jugerait invalide toute région remplie.
add_filter('block_editor_settings_all', function ($settings, $context) {
    if (!empty($context->post) && carlo_post_template($context->post)) {
        $settings['availableTemplates'] = [];
        $settings['templateLock'] = 'insert';
        $settings['canLockBlocks'] = false;
    }
    return $settings;
}, 10, 2);


// Add template on page's list
add_filter('manage_pages_columns', 'carlo_add_template_column');
add_action('manage_pages_custom_column', 'carlo_template_column_value', 10, 2);

function carlo_add_template_column($cols) {
  $cols = [
    ...array_slice($cols, 0, 2),
    'template' => __('Template'),
    ...array_slice($cols, 2),
  ];
  return $cols;
}

function carlo_template_column_value($column_name, $post_id) {
  if($column_name !== 'template') return;
  echo esc_html(carlo_post_template($post_id)['_label'] ?? '');
}
