<?php

add_action("after_setup_theme", function () {
    $menus = carlo_structure("menus");
    foreach ($menus as $menu => $name) {
        register_nav_menu($menu, $name);
    }
});
