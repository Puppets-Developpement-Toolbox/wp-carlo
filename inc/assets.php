<?php
use Idleberg\WordpressViteAssets\WordpressViteAssets;

$is_dev = WP_ENV === 'development';
$manifest = get_stylesheet_directory() . "/dist/.vite/manifest.json";
// Chemin du thème tel que Vite le connaît (clés du manifest) : app/themes/<thème>.
$assetPath = ltrim(str_replace(WP_HOME, "", get_stylesheet_directory_uri()), "/");

if (!$is_dev && !is_admin()) {
    if(file_exists($manifest)) {
        $viteAssets = new WordpressViteAssets($manifest, get_stylesheet_directory_uri() . "/dist/");
        $viteAssets->inject("{$assetPath}/js/main.js", [
            "integrity" => false,
        ]);
    }
}

add_action("wp_enqueue_scripts", function () use($is_dev) {
    $v = 1;
    $tpl = get_stylesheet_directory_uri();


    if ($is_dev) {
        $tpl = str_replace(WP_HOME, $_ENV["ASSET_BASE_URL"], $tpl);
        wp_enqueue_script("carlo_script", "{$tpl}/js/main.js", [], $v);
        wp_enqueue_style("carlo_style", "{$tpl}/css/global.css", [], $v);
    }
});

// Éditeur de blocs : le CSS du thème est chargé dans l'iframe, pour que l'aperçu des sections
// ressemble au site. Ce hook sert aussi au front et à la page d'admin elle-même : on ne vise que
// l'iframe, pour laquelle WordPress désactive les styles d'éditeur.
add_action("enqueue_block_assets", function () use ($is_dev, $manifest, $assetPath) {
    if (!is_admin() || wp_should_load_block_editor_scripts_and_styles()) return;

    // Comme WordPress le fait pour les thèmes à theme.json : pas de reset de l'éditeur dans
    // l'iframe. Écrit hors @layer, il l'emporterait sur les styles du thème (Tailwind 4, en @layer).
    wp_styles()->done[] = "wp-reset-editor-styles";

    $tpl = get_stylesheet_directory_uri();

    if ($is_dev) {
        wp_enqueue_style("carlo_editor_style", str_replace(WP_HOME, $_ENV["ASSET_BASE_URL"], $tpl) . "/css/global.css");
    } elseif (file_exists($manifest)) {
        $entry = json_decode(file_get_contents($manifest), true)["{$assetPath}/js/main.js"] ?? [];
        foreach ($entry["css"] ?? [] as $i => $css) {
            wp_enqueue_style("carlo_editor_style_{$i}", "{$tpl}/dist/{$css}");
        }
    }
});

if ($is_dev) add_filter("script_loader_tag", "carlo_add_script_module", 10, 3);
function carlo_add_script_module($tag, $handle, $src)
{
    if ("carlo_script" === $handle) {
        $tag = '<script type="module" src="' .
            $_ENV["ASSET_BASE_URL"] .
            '/@vite/client"></script>';
        $tag .= '<script type="module" src="' . esc_url($src) . '"></script>';
    }

    return $tag;
}

add_action( 'wp_print_styles', function(){
    wp_style_add_data('admin-bar', 'after', []);
});

// rm useless scripts
remove_action("wp_head", "print_emoji_detection_script", 7);
remove_action("wp_print_styles", "print_emoji_styles");
