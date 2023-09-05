<?php
carlo_render_region('header');
carlo_render('global/breadcrumb', ['items' => [
  ['Accueil', get_home_url()],
  [get_the_title()]
]]);
carlo_render_region('content');
