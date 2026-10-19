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

function carlo_acf_fields($key, $definition, $parent_key)
{
    if (empty($definition)) {
        throw new Exception(
            "le champ {$key} de {$parent_key} n'a pas de définition"
        );
    }

    if (is_array($definition) && !isset($definition["_type"])) {
        $type = isset($definition["_id"]) ? "group" : "container";
    } else {
        $type = is_string($definition) ? $definition : $definition["_type"];
    }

    $acf_type = match ($type) {
        "text_short" => "text",
        "group" => "group",
        "container" => "flexible_content",
        "repeater" => "repeater",
        "wysiwyg" => "wysiwyg",
        "image" => "image",
        "url" => "url",
        "color" => "color_picker",
        "text" => "textarea",
        "number" => "number",
        "choices" => "select",
        "select" => "select", // @deprecated
        "form" => "select",
        "reference" => "relationship",
        "boolean" => "true_false",
        "taxonomy" => "taxonomy",
        "date" => "date_picker",
        "link" => "link",
        "embed" => "oembed",
        "file" => "file",
        "datetime" => "date_time_picker"
    };

    if (isset($definition["_label"])) {
        $label = $definition["_label"];
        unset($definition["_label"]);
    } else {
        $label = str_replace("_", " ", ucfirst($key));
    }

    if($type === 'image' && isset($definition['_multi'])) {
      $acf_type = 'gallery';
    }

    $acf = [
        "key" => "{$parent_key}_{$key}",
        "label" => $label,
        "name" => $key,
        "type" => $acf_type,
        "required" =>
            !empty($definition["_required"]) && $definition["_required"]
                ? 1
                : 0,
    ];

    if (isset($definition["_help"])) {
        $acf["instructions"] = $definition["_help"];
    }

    if (in_array($type, ["image", "file"])) {
        $acf["return_format"] = "id";
    }

    if ($type === "date") {
        $acf["return_format"] = "Y-m-d";
    }

    if (isset($definition["_help"])) {
        $acf["instructions"] = $definition["_help"];
    }

    if ($type === "group") {
        $definition = carlo_structure_fields($definition);
        $acf["sub_fields"] = array_map(
            "carlo_acf_fields",
            array_keys($definition),
            $definition,
            array_fill(0, count($definition), "{$parent_key}_{$key}")
        );
        $acf["layout"] = "block";
    }

    if ($type === "container") {

        $acf["display"] = "block";
        $acf["layouts"] = array_map(function ($block) use ($parent_key, $key) {
            if(is_null($block)) {
                //dump($parent_key, $key, $block);
                return;
            }

            return carlo_acf_fields(
                $block['_id'],
                $block,
                "{$parent_key}_{$key}"
            );
        }, $definition['_items'] ?? $definition);

        $acf["button_label"] =
            $definition["_add_button"] ?? "Ajouter une sous-section";
        $acf["min"] = $definition["_min"] ?? null;
        $acf["max"] = $definition["_max"] ?? null;

    }

    if ($type === "repeater") {
        $acf["sub_fields"] = array_map(
            "carlo_acf_fields",
            array_keys($definition["_repeat"]),
            $definition["_repeat"],
            array_fill(0, count($definition["_repeat"]), "{$parent_key}_{$key}")
        );
        $acf["layout"] = "block";

        foreach ($acf["sub_fields"] as $k => $v) {
            $acf["sub_fields"][$k]["parent_repeater"] = "{$parent_key}_{$key}";
        }

        $acf["button_label"] =
            $definition["_add_button"] ?? "Ajouter un élément";
        $acf["min"] = $definition["_min"] ?? null;
        $acf["max"] = $definition["_max"] ?? null;
        if (
            !isset($acf["min"]) &&
            !isset($acf["max"]) &&
            isset($definition["_times"])
        ) {
            $acf["min"] = $acf["max"] = $definition["_times"];
        }
    }

    if ($type === "select" || $type === "choices") {
        $acf["choices"] = $definition["_choices"];
        $acf["value"] = $definition['_value']?? false;
        if (!empty($definition["_multi"])) {
            $acf["type"] = "checkbox";
        }
    }

    if ($type === "form") {
        /** @var NF_Database_Models_Form[] */
        $forms = WPCF7_ContactForm::find();
        foreach ($forms as $form) {
            $acf["choices"][$form->id()] = $form->title();
        }
    }

    if ($type === "reference") {

        $acf["post_type"] = (array) $definition["_ref_type"];
        $acf["return_format"] = "object";
        if (isset($definition["_ref_max"])) {
            throw new Exception(
                "Les référence doivent utiliser la clé _max et non _ref_max"
            );
        }
        if (isset($definition["_max"])) {
            $acf["max"] = $definition["_max"];
        }
    }

    if ($type === "taxonomy") {
        $acf["taxonomy"] = $definition["_taxo"];
        $acf["return_format"] = "object";
        $acf["field_type"] = "multi_select";
    }

    if ($type === "text") {
        $acf["new_lines"] = "br";
    }

    if ($type === "link") {
        $acf["return_format"] = $definition["_return_url"] ? "url" : "array";
    }

    if ($type === "wysiwyg") {
        $acf["media_upload"] = 0;
    }

    return $acf;
}

/**
 * Nom du bloc d'une section : sections/text:base → acf/carlo-sections-text.
 * Le préfixe acf/ est imposé par ACF.
 */
function carlo_block_name($id)
{
    return 'acf/' . carlo_block_slug($id);
}

function carlo_block_slug($id)
{
    return 'carlo-' . trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(preg_replace('/:base$/', '', $id))), '-');
}

/**
 * Enregistre un bloc ACF par section proposée dans une région.
 */
function carlo_register_section_blocks()
{
    $sections = [];
    foreach (['page', ...array_keys(carlo_structure('types') ?? [])] as $post_type) {
        foreach (carlo_templates($post_type) as $template) {
            foreach (carlo_structure_fields($template) as $region) {
                $sections += carlo_region($region)['sections'];
            }
        }
    }
    foreach ($sections as $id => $definition) {
        carlo_register_section_block($id, $definition);
    }
}

function carlo_register_section_block($id, $definition)
{
    [, $name, $variant] = carlo_explode_id($id);
    $slug = carlo_block_slug($id);
    $title = $definition['_label'] ?? ucfirst(str_replace('_', ' ', $variant === 'base' ? $name : "{$name} {$variant}"));
    $fields = carlo_structure_fields($definition);

    acf_register_block_type([
        'name' => $slug,
        'title' => $title,
        'category' => 'design',
        // ancestor et non parent : parent l'emporterait sur les allowedBlocks de la région.
        'ancestor' => ['carlo/region'],
        'supports' => ['align' => false],
        'render_callback' => function ($block, $content, $is_preview) use ($id, $title, $fields) {
            $data = get_fields() ?: [];
            $filled = false;
            array_walk_recursive($data, function ($value) use (&$filled) {
                $filled = $filled || !empty($value);
            });
            // Section pas encore remplie : la library ne tolère pas les données absentes.
            if ($fields && !$filled) {
                if ($is_preview) {
                    echo '<p class="carlo-placeholder">' . esc_html("{$title} : à remplir dans le panneau Réglages.") . '</p>';
                }
                return;
            }
            carlo_render($id, $data);
        },
    ]);

    acf_add_local_field_group([
        'key' => "group_{$slug}",
        'title' => $title,
        'fields' => array_map(
            'carlo_acf_fields',
            array_keys($fields),
            $fields,
            array_fill(0, count($fields), "field_{$slug}")
        ),
        'location' => [[['param' => 'block', 'operator' => '==', 'value' => "acf/{$slug}"]]],
    ]);
}
