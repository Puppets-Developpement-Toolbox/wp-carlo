<?php

function carlo_acf_template_blocs($template, $definition)
{
    $definition = carlo_structure_fields($definition);
    $i = 0;
    $regions = [];
    foreach ($definition as $region => $region_blocks) {
        $label = carlo_structure("regions");
        $fields = [];
        foreach ((array) $region_blocks as $i => $block) {
            if (isset($block["_id"])) {
                $fields[] = carlo_acf_fields(
                    $i,
                    $block,
                    "{$template}_{$region}"
                );
            } else {
                $max = null;
                if (isset($block["_blocs"]) && is_array($block["_blocs"])) {
                    $max = $block["_max"] ?? null;
                    $block = $block["_blocs"];
                }
                $fields[] = [
                    "key" => "{$template}_{$region}_{$i}",
                    "name" => $i,
                    "type" => "flexible_content",
                    "label" => "Blocs de " . $label[$region],
                    "display" => "block",
                    "layouts" => array_map(function ($block) use (
                        $template,
                        $region
                    ) {
                        return carlo_acf_fields(
                            $block["_id"],
                            $block,
                            "{$template}_{$region}"
                        );
                    }, $block),
                    "button_label" => "Ajouter un bloc",
                    "max" => $max,
                ];
            }
        }

        $regions[] = [
            "key" => "{$template}_{$region}",
            "label" => $label[$region],
            "name" => $region,
            "layout" => "block",
            "type" => "group",
            "sub_fields" => $fields,
        ];
    }
//dump($template);
    if ($template === "archive") {
        $location = [
            [
                [
                    "param" => "page_type",
                    "operator" => "==",
                    "value" => "posts_page",
                ],
            ],
        ];
    } elseif (str_starts_with($template, "type_")) {
        $location = [
            [
                [
                    "param" => "post_type",
                    "operator" => "==",
                    "value" => str_replace("type_", "", $template),
                ],
            ],
        ];
    } else {
        $location = [
            [
                [
                    "param" => "post_type",
                    "operator" => "==",
                    "value" => "page",
                ],
                [
                    "param" => "page_template",
                    "operator" => "==",
                    "value" => $template,
                ],
                [
                    "param" => "page_type",
                    "operator" => "!=",
                    "value" => "posts_page",
                ],
            ],
        ];
    }


    acf_add_local_field_group([
        "key" => $template,
        "title" => "Layout {$template}",
        "layout" => "seamless",
        "active" => true,
        "fields" => $regions,
        "location" => $location,
        "menu_order" => $i++,
        "hide_on_screen" => [
            "the_content",
            "discussion",
            "comments",
            "revisions",
            "slug",
            "author",
            "format",
            "featured_image",
            "categories",
            "tags",
            "send-trackbacks",
        ],
        "show_in_rest" => true,
    ]);

    //dump($template, $regions);
}


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
  $templates = array_flip(get_page_templates());
  echo $templates[get_page_template_slug() ?: 'default'];
}
