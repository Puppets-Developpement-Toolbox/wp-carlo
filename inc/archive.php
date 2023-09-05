<?php

add_action('wp_ajax_archive_content', 'carlo_archive_content_ajax');
add_action('wp_ajax_nopriv_archive_content', 'carlo_archive_content_ajax');
function carlo_archive_content_ajax(){
  $page = ($_REQUEST['page'] ?? 1) - 1;
  $post_type = 'post';

  $wp_query = new WP_Query([
    'post_per_page' => -1,
    'post_type' => $post_type,
    'paged' => $page,
    'post_status' => 'publish',
    'cat' => $_REQUEST['cat'] ?? null,
    's' => $_REQUEST['search'] ?? ''
  ]);

  foreach($wp_query->get_posts() as $post) {
    carlo_render('components/actu-list-element', [
      'post' => $post
    ]);
  }

  if($wp_query->max_num_pages <= $page) {
    echo '<div id="end"></div>';
  }

  wp_die();
}