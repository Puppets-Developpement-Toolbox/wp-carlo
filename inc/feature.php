<?php

/**
 * Disable Gutemberg
 */
add_filter('use_block_editor_for_post_type', 'carlo_disable_gutenberg', 10, 2);
function carlo_disable_gutenberg($current_status, $post_type) {
    if ($post_type === 'page') return false;
    return $current_status;
}

/**
 * Disable comment
 */
add_action('admin_init', function () {
    global $pagenow;
    if ($pagenow === 'edit-comments.php') {
        wp_safe_redirect(admin_url());
        exit;
    }
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
    foreach (get_post_types() as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
});

add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);
add_filter('comments_array', '__return_empty_array', 10, 2);

add_action('admin_menu', function () {
    remove_menu_page('edit-comments.php');
});

add_action('init', function () {
    if (is_admin_bar_showing()) {
        remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
    }
});

// Disable acf admin
if (!WP_DEBUG) {
    add_filter('acf/settings/show_admin', '__return_false');
}

add_filter('acf/settings/save_json', function ($path) {
  $path = get_stylesheet_directory() . '/acf';
  return $path;
});

add_filter('acf/settings/load_json', function( $paths ) {
  unset($paths[0]);
  $paths[] = get_stylesheet_directory() . '/acf';
  return $paths;
});

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
});

/**
 * Add style select buttons to TinyMCE editor
 */
function add_style_select_buttons($buttons) {
    array_unshift($buttons, 'styleselect');
    return $buttons;
}
add_filter('mce_buttons_2', 'add_style_select_buttons');

/**
 * Add classes to TinyMCE editor
 */
function carlo_tiny_mce_add_clases($init_array) {
    $style_formats = array(
        array(
            'title' => 'Large paragraphe',
            'help'  => 'Ajoute une classe "large" au paragraphe',
            'selector' => 'p,h1,h2,h3,h4,h5,h6,div,ul,ol,li,a,span',
            'classes' => 'large'
        ),
    );
    $init_array['style_formats'] = json_encode($style_formats);
    return $init_array;
}
add_filter('tiny_mce_before_init', 'carlo_tiny_mce_add_clases');
