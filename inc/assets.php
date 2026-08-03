<?php
use Idleberg\WordpressViteAssets\WordpressViteAssets;

$is_dev = WP_ENV === 'development';

if (!$is_dev && !is_admin()) {
    if(file_exists(get_stylesheet_directory() . "/dist/.vite/manifest.json")) {
        $assetPath = ltrim(
            str_replace(WP_HOME, "", get_stylesheet_directory_uri()),
            "/"
        );
        $viteAssets = new WordpressViteAssets(
            get_stylesheet_directory() . "/dist/.vite/manifest.json",

            get_stylesheet_directory_uri() . "/dist/"
        );
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
