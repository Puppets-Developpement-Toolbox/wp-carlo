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
// carlo_render("global/header");

$regions = carlo_structure("templates")[$template];
?>
<main id="main" class="page-content">
  <?php foreach ($regions as $region => $sections) {
      carlo_render_region($template, $region);
  } ?>
</main>
<?php
// carlo_render("global/footer");
carlo_render("global/html_end");
ob_flush();

