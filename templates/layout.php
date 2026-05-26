<?php

ob_start();
$template = match (true) {
  is_home() => "archive",
  is_404() => "error",
  is_page() => get_page_template_slug() ?: "default",
  is_single() => "type_" . get_post_type(),
  default => "archive", // ← cas de secours
};

do_action("carlo_prerender", $template);

if (is_singular() && post_password_required()) {
    carlo_render('global/html_start');
    echo '<main id="main" class="flex-1 flex flex-col"><div class="[ section ] padding-15">';
    echo get_the_password_form();
    echo '</div></main>';
    carlo_render('global/html_end');
    ob_flush();
    return;
}

carlo_render("global/html_start");

if(is_page()) {
  $regions = carlo_structure("templates")[$template] ?? null;
} elseif(is_single() && !is_404()) {
  $regions = (carlo_structure("types") ?? [])[get_post_type()]['template'] ?? null;
}

$template_file = get_theme_file_path("/templates/{$template}.php");

if(file_exists($template_file)) :
  include $template_file;
else : ?>
  <main id="main"
        class="flex-1 flex flex-col">
    <?php
      if(!empty($regions)){
        foreach ($regions as $region => $sections) {
          if(!str_starts_with($region, '_')) {
            carlo_render_region($template, $region);
          }
        }
      }
    ?>
  </main>
<?php
endif;

carlo_render('global/html_end');
ob_flush();
