<?php
use Idleberg\WordpressViteAssets\WordpressViteAssets;

if (!is_admin()) {
    // $assetPath = ltrim(
    //     str_replace(WP_HOME, "", plugin_dir_url( __FILE__ )),  //corrigé
    //     "/"
    // );
    $viteAssets = new WordpressViteAssets(
        get_template_directory() . "/dist/.vite/manifest.json",
        get_template_directory_uri() . "/dist/"
    );

    $viteAssets->inject("app/themes/cgf/js/main.js", [
        "integrity" => false,
    ]);
}

// add_action("wp_enqueue_scripts", function () {
//     $v = 1;
//     $tpl = plugin_dir_url( __FILE__ );  // corrigé

//     if (WP_DEBUG) {
//         $tpl = str_replace(WP_HOME, $_ENV["ASSET_BASE_URL"], $tpl);
//         wp_enqueue_script("carlo_script", "{$tpl}/js/main.js", [], $v);
//     }
// });

// add_filter("script_loader_tag", "carlo_add_script_module", 10, 3);
// function carlo_add_script_module($tag, $handle, $src) {
//     if ("carlo_script" === $handle) {
//         $tag = '<script type="module" src="' . $_ENV["ASSET_BASE_URL"] . '/@vite/client"></script>';
//         $tag .= '<script type="module" src="' . esc_url($src) . '"></script>';
//     }
//     return $tag;
// }

add_action('wp_print_styles', function(){
    wp_style_add_data('admin-bar', 'after', []);
});

remove_action("wp_head", "print_emoji_detection_script", 7);
remove_action("wp_print_styles", "print_emoji_styles");
