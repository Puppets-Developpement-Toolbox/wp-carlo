<?php

class CarloWpDriver extends carlo\BaseDriver implements carlo\DriverInterface
{
    public function getFile(string $type, string $element, string $variant = 'base', string $namespace = 'default')
    {
        $variant = $variant ?: "base";

        // Vérifier que le namespace est enregistré
        if (!isset($this->namespaces[$namespace])) {
            throw new Exception("Le namespace '{$namespace}' n'est pas enregistré.");
        }

        $ext = $type === "structure" ? "yml" : "php";

        //  Utiliser la fonction get paths
        $paths = $this->getPathsToTest($ext, $element, $variant, $namespace);

        // Utiliser locate_template de WordPress (cherche child-theme puis parent-theme)
        $wp_path = locate_template(array_map(fn($p) => "templates/{$p}", $paths));
        if ($wp_path) {
            return $wp_path;
        }

        // Fallback sur le package (namespace enregistré via BaseDriver)
        return parent::getFile($type, $element, $variant, $namespace);
    }


    public function img(
        string $key,
        string $default_size,
        array $source_sizes,
        $mobile_key,
        array $mobile_source_sizes,
        array $imgAttrs
    ) {
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
        if (empty($source_sizes) && empty($mobile_source_sizes)) {
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

//////  ----
add_action("acf/init", "carlo_acf_init");

carlo_driver(new CarloWpDriver());
$child_theme_structure = get_stylesheet_directory() . "/structure.yml";
carlo_register($child_theme_structure);

function carlo_acf_init()
{
    $templates = carlo_structure("templates");
    if (is_array($templates)) {
        $to_register = [];
        foreach ($templates as $template => $definition) {
            carlo_acf_template_blocs($template, $definition, true);
            $to_register[$template] = $definition['_label'];
        }
        carlo_register_templates('page', $to_register);
    }

    $types = carlo_structure("types");
    if (is_array($types)) {
        foreach ($types as $type => $definition) {
            if(isset($definition['wp_args'])){
                $default_wp_args = ['public' => true];
                $definition['wp_args'] = array_merge($default_wp_args, $definition['wp_args']);
                register_post_type($type, $definition['wp_args']);
            }

            $to_register = [];
            if(!empty($definition["templates"])){
                foreach ($definition["templates"] as $template => $template_definition) {
                    carlo_acf_template_blocs("type_{$type}__{$template}", $template_definition, true);
                    $to_register[$template] = $template_definition["_label"];
                }
                carlo_register_templates($type, $to_register);
            }

            $structure = null;
            if(isset($definition["template"])) $structure = $definition["template"];
            elseif(isset($definition["templates"]["default"])) {
              $structure = $definition["templates"]["default"];
              unset($definition["templates"]["default"]);
            }
            if($structure){
              carlo_acf_template_blocs("type_{$type}", $structure, !empty($to_register));
            }
        }
    }
}

function carlo_register_templates($post_type, $templates)
{
  add_filter(
    "theme_{$post_type}_templates",
    function($registred_templates, $wp_theme, $post) use($templates) {
      foreach ($templates as $template => $label) {
        if (!isset($registred_templates[$template]) && $template !== "archive") {
          $registred_templates[$template] = $label;
        }
      }
      return $registred_templates;
    },
    PHP_INT_MAX,
    3
  );
}

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
            "classes" => $classes,
        ];
    }, iterator_to_array($uls[0]->childNodes));
    $items = array_filter($items);
    return $items;
}

function carlo_render_region($template, $region)
{
    $templates = carlo_structure("templates");
    if(str_starts_with($template, 'type_')){
        $template = $post_type_name = get_post_type();
        $templates_type = carlo_structure("types")[$post_type_name]['template'];
        $templates[$post_type_name] = [
            $region => $templates_type[$region]
        ];
    }

    if (
        !isset($templates[$template]) ||
        !isset($templates[$template][$region])
    ) {
        return;
    }
    $region_sections = $templates[$template][$region];

    foreach ((array) $region_sections as $i => $sections) {
        $content_blocks = get_field($region, get_queried_object_id())[$i] ?? [];
        if (isset($sections['_id'])) {
            carlo_render($sections['_id'], $content_blocks ?: []);
        } elseif ($content_blocks) {
            foreach ($content_blocks as $block) {
                $block['_id'] = $block["acf_fc_layout"];
                unset($block["acf_fc_layout"]);
                carlo_render($block['_id'], $block);
            }
        }
    }
}

function carlo_bootstrap() {
    include __DIR__ . './../templates/layout.php';
}
