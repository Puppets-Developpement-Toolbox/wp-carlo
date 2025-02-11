<?php

class CarloWpDriver extends carlo\BaseDriver implements carlo\DriverInterface
{
    public function loadData(array $structure) {}

    public function img(
        string $key,
        string $default_size,
        array $source_sizes,
        $mobile_key, // string|null
        array $mobile_source_sizes,
        array $imgAttrs
    ) {
        // si la clé est numérique on la traite comme un id d'attachment
        // sinon on la traite comme une clé carlo
        $id = is_numeric($key) ? $key : $this->get($key);

        $rmSrcsetAttrs = function ($attrs) {
            unset($attrs["srcset"], $attrs["sizes"]);
            return $attrs;
        };
        add_filter("wp_get_attachment_image_attributes", $rmSrcsetAttrs);

        $img = wp_get_attachment_image($id, $default_size, false, $imgAttrs);

        remove_filter("wp_get_attachment_image_attributes", $rmSrcsetAttrs);

        if (!$img) {
            return "";
        }
        if (empty($source_sizes) && empty($args)) {
            return $img;
        }

        $source_html = function ($id, array $source_sizes) {
            return implode(
                "\n",
                array_map(
                    function ($media, $size) use ($id) {
                        return '<source media="' .
                            $media .
                            '" srcset="' .
                            wp_get_attachment_image_src($id, $size)[0] .
                            '">';
                    },
                    array_keys($source_sizes),
                    $source_sizes
                )
            );
        };

        $sources = $source_html($id, $source_sizes);

        if (!empty($mobile_source_sizes) && !empty($mobile_key)) {
            $mobile_id = is_numeric($mobile_key)
                ? $mobile_key
                : carlo_get($mobile_key);
            $sources .=
                "\n" . $source_html($mobile_id, (array) $mobile_source_sizes);
        }

        return <<<HTML
<picture>
    {$sources}
    {$img}
</picture>
HTML;
    }
}

add_action("acf/init", "carlo_acf_init");

carlo_driver(new CarloWpDriver());
$child_theme_structure = get_stylesheet_directory() . "/structure.yml";
carlo_register($child_theme_structure);

function carlo_acf_init()
{
    add_filter(
        "theme_page_templates",
        "carlo_register_templates",
        PHP_INT_MAX,
        3
    );
    $templates = carlo_structure("templates");
    if (is_array($templates)) {
        foreach ($templates as $template => $definition) {
            carlo_acf_template_blocs($template, $definition);
        }
    }
    $types = carlo_structure("types");
    if (is_array($types)) {
        foreach ($types as $type => $definition) {
            carlo_acf_template_blocs("type_{$type}", $definition["template"]);
        }
    }
}

function carlo_register_templates($page_templates, $wp_theme, $post)
{
    $templates = carlo_structure("templates");
    foreach ($templates as $template => $definition) {
        if (!isset($page_templates[$template]) && $template !== "archive") {
            $page_templates[$template] = $definition["_label"];
        }
    }
    return $page_templates;
}

/**
 * charge le menu qui va bien
 */
function carlo_menu($menu)
{
    add_filter("nav_menu_link_attributes", "_carlo_filter_nav_add_id", 10, 3);
    add_filter("wp_nav_menu", "_carlo_filter_nav", 10, 2);
    wp_nav_menu([
        "theme_location" => $menu,
    ]);
    remove_filter("wp_nav_menu", "_carlo_filter_nav", 10);
    remove_filter("nav_menu_link_attributes", "_carlo_filter_nav_add_id", 10);
}

function _carlo_filter_nav_add_id($atts, $item, $args)
{
    $atts["data-object"] = $item->object_id;
    return $atts;
}

function _carlo_filter_nav($nav_menu, $args)
{
    $doc = new DOMDocument();
    $charset = get_bloginfo("charset");
    $doc->loadHTML(
        <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="{$charset}" /></head>
  <body>{$nav_menu}</body>
</html>
HTML
    );
    $items = _carlo_nav_extract_elements($doc);

    carlo_render("menus/{$args->theme_location}", [
        "items" => $items,
    ]);
    return false;
}

function _carlo_nav_extract_elements(DomNode $node)
{
    $uls = $node->getElementsByTagName("ul");
    if (count($uls) === 0) {
        return [];
    }
    $items = array_map(function (DomNode $node) {
        if ($node->nodeName !== "li") {
            return;
        }
        $classes = $node->attributes->getNamedItem("class")->value;
        return [
            "current" => str_contains($classes, "current-menu-item"),
            "name" => $node->firstChild->textContent,
            "href" => $node->firstChild->attributes->getNamedItem("href")
                ->nodeValue,
            "object_id" => $node->firstChild->attributes->getNamedItem(
                "data-object"
            )->nodeValue,
            "children" => _carlo_nav_extract_elements($node),
        ];
    }, iterator_to_array($uls[0]->childNodes));
    $items = array_filter($items);
    return $items;
}

function carlo_render_region($template, $region)
{
    $templates = carlo_structure("template");
    if (
        !isset($templates[$template]) ||
        !isset($templates[$template][$region])
    ) {
        return;
    }
    $region_sections = $templates[$template][$region];

    foreach ((array) $region_sections as $i => $sections) {
        $content_blocks = get_field($region, get_queried_object_id())[$i] ?? [];
        if (is_string($block)) {
            carlo_render("sections/{$block}", $content_blocks ?: []);
        } elseif ($content_blocks) {
            foreach ($content_blocks as $block) {
                carlo_render("sections/{$block["acf_fc_layout"]}", $block);
            }
        }
    }
}
