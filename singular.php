<?php
ob_start();
$template = match (true) {
    is_home() => "archive",
    is_404() => "error",
    is_page() => get_page_template_slug() ?: "default",
    is_single() => "type_" . get_post_type(),
};

do_action("carlo_prerender", $template);

// carlo_render("global/html_start");
// carlo_render("global/header");
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo("charset"); ?>" />
		<?php wp_head(); ?>
	</head>
  <body <?php body_class(); ?>>

    <?php
    carlo_render("global/svg");

    $regions = carlo_structure("templates")[$template];
    ?>
<main id="main" class="page-content">
  <?php foreach ($regions as $region => $sections) {
      if(!str_starts_with($region, '_')) {
        carlo_render_region($template, $region);
      }
  } ?>
</main>
<?php
// carlo_render("global/footer");
carlo_render("global/html_end");
ob_flush();
