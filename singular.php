<?php 
ob_start();
$template = match (true) {
  is_home() => 'archive',
  is_404() => 'error',
  is_page() => get_page_template_slug(),
  is_single() => 'type_'.get_post_type(),
};

do_action('carlo_prerender', $template);

carlo_render('global/html_start');
carlo_render('global/header');
?>

<main id="main" class="page-content">
  <?php carlo_render($template) ?>
</main>
<?php
carlo_render('global/footer');
carlo_render('global/html_end');
ob_flush();
