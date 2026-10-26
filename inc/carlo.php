<?php

class CarloWpDriver extends carlo\BaseDriver implements carlo\DriverInterface
{
    public function render(string $tpl, array $args = [])
    {
        // Emplacement des blocs enfants d'un conteneur dans l'aperçu de l'éditeur (cf. inc/blocks.php).
        if ($tpl === 'carlo:inner') {
            echo '<div data-carlo-inner></div>';
            return;
        }
        return parent::render($tpl, $args);
    }

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

        // Fallback sur les templates du plugin wp-carlo
        $plugin_templates = __DIR__ . '/../templates/';
        foreach ($paths as $path) {
            if (file_exists($plugin_templates . $path)) {
                return $plugin_templates . $path;
            }
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
add_action("init", "carlo_init");

carlo_driver(new CarloWpDriver());
$child_theme_structure = get_stylesheet_directory() . "/structure.yml";
carlo_register($child_theme_structure);

function carlo_init()
{
    $types = carlo_structure("types");
    if (is_array($types)) {
        foreach ($types as $type => $definition) {
            if(isset($definition['wp_args'])){
                // show_in_rest et editor : nécessaires à l'éditeur de blocs.
                $wp_args = array_merge(['public' => true, 'show_in_rest' => true], $definition['wp_args']);
                if (isset($wp_args['supports'])) {
                    $wp_args['supports'][] = 'editor';
                }
                register_post_type($type, $wp_args);
            }
        }
    }

    carlo_register_section_blocks();
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

/**
 * Rend les sections d'une région, lues dans le contenu (blocs) du contenu affiché.
 * $template n'est plus utilisé, il est conservé pour les thèmes qui l'appellent.
 */
function carlo_render_region($template, $region)
{
    foreach (parse_blocks(get_post_field('post_content', get_queried_object_id())) as $block) {
        if ($block['blockName'] === 'carlo/region' && ($block['attrs']['region'] ?? null) === $region) {
            foreach ($block['innerBlocks'] as $section) {
                echo render_block($section);
            }
        }
    }
}

function carlo_bootstrap() {
    include __DIR__ . './../templates/layout.php';
}
