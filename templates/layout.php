<?php

ob_start();
$template = match (true) {
  is_home() => "archive",
  is_404() => "error",
  is_page() => get_page_template_slug() ?: "default",
  is_single() => "type_" . get_post_type(),
};

do_action("carlo_prerender", $template);

carlo_render("global/html_start");

if(is_page()) {
  $regions = carlo_structure("templates")[$template];
} elseif(!is_404()) {
  $regions = carlo_structure("types")[get_post_type()]['template'];
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
