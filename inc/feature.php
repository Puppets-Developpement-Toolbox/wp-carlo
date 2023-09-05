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
  // Redirect any user trying to access comments page
  global $pagenow;
  if ($pagenow === 'edit-comments.php') {
    wp_safe_redirect(admin_url());
    exit;
  }

  // Remove comments metabox from dashboard
  remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
  // Disable support for comments and trackbacks in post types
  foreach (get_post_types() as $post_type) {
    if (post_type_supports($post_type, 'comments')) {
      remove_post_type_support($post_type, 'comments');
      remove_post_type_support($post_type, 'trackbacks');
    }
  }
});

// Close comments on the front-end
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);

// Hide existing comments
add_filter('comments_array', '__return_empty_array', 10, 2);

// Remove comments page in menu
add_action('admin_menu', function () {
  remove_menu_page('edit-comments.php');
});

// Remove comments links from admin bar
add_action('init', function () {
  if (is_admin_bar_showing()) {
    remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
  }
});


// disable acf admin
if(!WP_DEBUG) {
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

add_theme_support( 'title-tag' );