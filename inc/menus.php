<?php

add_action('after_setup_theme', function() {
  $structure = carlo_structure();
  foreach($structure['menus'] as $menu => $name) {
    register_nav_menu($menu, $name);
  }
});
