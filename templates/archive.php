<?php
carlo_render_region('header');
carlo_render('global/breadcrumb', ['items' => [
  ['Accueil', get_home_url()],
  [get_the_title(get_queried_object_id())]
]]);

carlo_render('blocs/actus_filters');
/** @var WP_Query */
global $wp_query;
carlo_render('blocs/actus_list', [
  'cards' => $wp_query->get_posts(),
  'has_more' => $wp_query->max_num_pages > 0,
  'page' => 0
]);

