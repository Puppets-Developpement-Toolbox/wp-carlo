<?php 
$template = is_home() ? 'archive' : 
  (is_404() ? 'error' : (get_page_template_slug() ?: 'default'));

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